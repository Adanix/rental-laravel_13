<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateBookingAction;
use App\Actions\TransitionBookingStatusAction;
use App\Contracts\PaymentGatewayInterface;
use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected CreateBookingAction $createBookingAction,
        protected TransitionBookingStatusAction $transitionBookingStatusAction,
        protected PaymentGatewayInterface $paymentGateway
    ) {}

    public function index(Request $request): View
    {
        $query = Booking::with(['user', 'items.item', 'payments'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date')) {
            $date = Carbon::parse($request->string('date'))->toDateString();
            $query->where(function ($q) use ($date) {
                $q->whereDate('start_at', $date)
                    ->orWhereDate('end_at', $date);
            });
        }

        $bookings = $query->paginate(15)->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['user', 'items.item.category', 'payments.verifier', 'statusLogs.actor']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function verifyPayment(Request $request, Booking $booking, Payment $payment): RedirectResponse
    {
        $this->paymentGateway->verifyPayment(
            payment: $payment,
            actor: $request->user(),
            notes: $request->input('notes')
        );

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function rejectPayment(Request $request, Booking $booking, Payment $payment): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->paymentGateway->rejectPayment(
            payment: $payment,
            actor: $request->user(),
            reason: $request->string('reason')
        );

        return back()->with('success', 'Pembayaran telah ditolak dan status pesanan diperbarui.');
    }

    public function pickup(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->status !== BookingStatus::CONFIRMED) {
            return back()->with('error', 'Hanya pesanan berstatus Terkonfirmasi yang dapat diserahkan ke pelanggan.');
        }

        $request->validate([
            'condition_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $checklist = $booking->handover_checklist ?? [];
        $checklist['pickup'] = [
            'condition' => $request->input('condition_notes', 'Semua unit dalam kondisi baik dan lengkap.'),
            'staff_id' => $request->user()->id,
            'staff_name' => $request->user()->name,
            'time' => now()->toIso8601String(),
        ];

        $booking->update([
            'picked_up_at' => now(),
            'handover_checklist' => $checklist,
        ]);

        try {
            $this->transitionBookingStatusAction->execute(
                booking: $booking,
                newStatus: BookingStatus::PICKED_UP,
                actor: $request->user(),
                reason: 'Unit telah diambil/diserahkan kepada pelanggan.'
            );

            return back()->with('success', 'Unit berhasil diserahkan! Status pesanan kini Sedang Disewa.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function return(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->status !== BookingStatus::PICKED_UP) {
            return back()->with('error', 'Hanya pesanan yang sedang disewa yang dapat diproses pengembaliannya.');
        }

        $request->validate([
            'condition_notes' => ['nullable', 'string', 'max:1000'],
            'damage_fee' => ['nullable', 'integer', 'min:0'],
        ]);

        $returnedAt = now();
        $damageFee = max(0, $request->integer('damage_fee', 0));

        // Calculate late fee
        $lateDays = 0;
        if ($returnedAt->gt($booking->end_at)) {
            $diffHours = round(abs($booking->end_at->diffInHours($returnedAt)), 2);
            $lateDays = (int) ceil($diffHours / 24);
        }

        $dailySum = $booking->items->sum(fn ($i) => $i->unit_price * $i->qty);
        $lateFeePercent = (int) Setting::get('late_fee_per_day_percent', 100);
        $lateFee = (int) round($lateDays * $dailySum * ($lateFeePercent / 100));

        $totalDeductions = $lateFee + $damageFee;
        $depositTotal = $booking->deposit_total;

        $checklist = $booking->handover_checklist ?? [];
        $checklist['return'] = [
            'condition' => $request->input('condition_notes', 'Unit kembali dalam kondisi normal.'),
            'late_days' => $lateDays,
            'late_fee' => $lateFee,
            'damage_fee' => $damageFee,
            'staff_id' => $request->user()->id,
            'staff_name' => $request->user()->name,
            'time' => $returnedAt->toIso8601String(),
        ];

        $booking->update([
            'returned_at' => $returnedAt,
            'late_fee' => $lateFee,
            'damage_fee' => $damageFee,
            'handover_checklist' => $checklist,
        ]);

        // Settlement of Deposit
        if ($depositTotal > $totalDeductions) {
            $refundAmount = $depositTotal - $totalDeductions;
            if ($refundAmount > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'type' => PaymentType::REFUND,
                    'method' => PaymentMethod::CASH,
                    'amount' => $refundAmount,
                    'status' => PaymentStatus::PENDING,
                    'notes' => 'Pengembalian sisa deposit jaminan setelah dikurangi biaya denda/kerusakan',
                ]);
            }
        } elseif ($totalDeductions > $depositTotal) {
            $additionalAmount = $totalDeductions - $depositTotal;
            Payment::create([
                'booking_id' => $booking->id,
                'type' => PaymentType::ADDITIONAL_FEE,
                'method' => PaymentMethod::CASH,
                'amount' => $additionalAmount,
                'status' => PaymentStatus::PENDING,
                'notes' => 'Tagihan kekurangan biaya denda/kerusakan yang melebihi deposit jaminan',
            ]);
        }

        try {
            $this->transitionBookingStatusAction->execute(
                booking: $booking,
                newStatus: BookingStatus::RETURNED,
                actor: $request->user(),
                reason: 'Unit telah dikembalikan oleh pelanggan dan proses serah-terima selesai.'
            );

            return back()->with('success', 'Pengembalian unit berhasil diproses! Periksa penyelesaian deposit.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function settlePayment(Request $request, Booking $booking, Payment $payment): RedirectResponse
    {
        $payment->update([
            'status' => PaymentStatus::VERIFIED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'notes' => $payment->notes . ' (Diselesaikan oleh ' . $request->user()->name . ')',
        ]);

        return back()->with('success', 'Status transaksi penyelesaian berhasil diperbarui.');
    }

    public function walkInCreate(): View
    {
        $items = Item::active()->with(['category', 'images'])->orderBy('name')->get();
        $startAt = now()->format('Y-m-d\TH:i');
        $endAt = now()->addDay()->format('Y-m-d\TH:i');

        return view('admin.bookings.walk-in', compact('items', 'startAt', 'endAt'));
    }

    public function walkInStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'item_id' => ['required', 'exists:items,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'payment_method' => ['required', new \Illuminate\Validation\Rules\Enum(PaymentMethod::class)],
            'direct_pickup' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Find or create customer
        $phone = preg_replace('/[^0-9]/', '', $validated['customer_phone']);
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => $validated['customer_name'],
                'role' => UserRole::CUSTOMER,
                'password' => null,
            ]
        );

        $startAt = Carbon::parse($validated['start_at']);
        $endAt = Carbon::parse($validated['end_at']);

        try {
            $booking = $this->createBookingAction->execute(
                customer: $user,
                itemsWithQty: [
                    [
                        'item_id' => (int) $validated['item_id'],
                        'qty' => (int) $validated['qty'],
                    ],
                ],
                startAt: $startAt,
                endAt: $endAt,
                actor: $request->user(),
                notes: 'Walk-in / Pesanan langsung: ' . ($validated['notes'] ?? '')
            );

            // Record verified payment
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'type' => PaymentType::RENTAL_AND_DEPOSIT,
                'method' => PaymentMethod::from($validated['payment_method']),
                'amount' => $booking->grand_total,
                'status' => PaymentStatus::VERIFIED,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'notes' => 'Pembayaran lunas langsung di kasir/counter (' . $validated['payment_method'] . ')',
            ]);

            // Transition to confirmed
            $this->transitionBookingStatusAction->execute(
                booking: $booking,
                newStatus: BookingStatus::CONFIRMED,
                actor: $request->user(),
                reason: 'Pembayaran walk-in telah diterima tunai/lunas oleh staff.'
            );

            // Direct pickup option
            if ($request->boolean('direct_pickup')) {
                $checklist = [
                    'pickup' => [
                        'condition' => 'Unit langsung diambil saat pemesanan walk-in di counter.',
                        'staff_id' => $request->user()->id,
                        'staff_name' => $request->user()->name,
                        'time' => now()->toIso8601String(),
                    ],
                ];

                $booking->update([
                    'picked_up_at' => now(),
                    'handover_checklist' => $checklist,
                ]);

                $this->transitionBookingStatusAction->execute(
                    booking: $booking,
                    newStatus: BookingStatus::PICKED_UP,
                    actor: $request->user(),
                    reason: 'Unit langsung diserahkan di counter walk-in.'
                );
            }

            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', "Pesanan walk-in {$booking->code} berhasil dibuat dan diproses!");
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
