<?php

namespace App\Http\Controllers;

use App\Actions\CreateBookingAction;
use App\Actions\TransitionBookingStatusAction;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Setting;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected CreateBookingAction $createBookingAction,
        protected TransitionBookingStatusAction $transitionBookingStatusAction
    ) {}

    public function create(Request $request): View
    {
        $selectedItem = null;
        if ($request->filled('item_id')) {
            $selectedItem = Item::active()->with(['category', 'images'])->find($request->integer('item_id'));
        }

        $items = Item::active()->with(['category', 'images'])->orderBy('name')->get();

        $startAt = $request->filled('start_at')
            ? Carbon::parse($request->input('start_at'))->format('Y-m-d\TH:i')
            : now()->addDay()->setHour(9)->setMinute(0)->format('Y-m-d\TH:i');

        $endAt = $request->filled('end_at')
            ? Carbon::parse($request->input('end_at'))->format('Y-m-d\TH:i')
            : now()->addDays(2)->setHour(9)->setMinute(0)->format('Y-m-d\TH:i');

        $qty = max(1, $request->integer('qty', 1));

        return view('bookings.create', compact('selectedItem', 'items', 'startAt', 'endAt', 'qty'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'item_id.required' => 'Unit rental wajib dipilih.',
            'qty.required' => 'Jumlah unit sewa wajib ditentukan.',
            'start_at.required' => 'Waktu mulai sewa wajib ditentukan.',
            'end_at.required' => 'Waktu selesai sewa wajib ditentukan.',
            'end_at.after' => 'Waktu selesai harus setelah waktu mulai sewa.',
        ]);

        $startAt = Carbon::parse($validated['start_at']);
        $endAt = Carbon::parse($validated['end_at']);

        // Cannot book in the past
        if ($startAt->lt(now()->subMinutes(15))) {
            return back()->withInput()->with('error', 'Waktu mulai sewa tidak boleh di masa lampau.');
        }

        $itemsWithQty = [
            [
                'item_id' => (int) $validated['item_id'],
                'qty' => (int) $validated['qty'],
            ],
        ];

        try {
            $booking = $this->createBookingAction->execute(
                customer: $request->user(),
                itemsWithQty: $itemsWithQty,
                startAt: $startAt,
                endAt: $endAt,
                actor: $request->user(),
                notes: $validated['notes'] ?? null
            );

            return redirect()->route('my-bookings.show', $booking->code)
                ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran sebelum batas waktu berakhir.');
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function myBookings(Request $request): View
    {
        $query = Booking::where('user_id', $request->user()->id)
            ->with(['items.item.category', 'payments'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $bookings = $query->paginate(10)->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    public function show(Request $request, string $code): View
    {
        $booking = Booking::where('code', $code)
            ->with(['items.item.category', 'payments.verifier', 'statusLogs.actor', 'user'])
            ->firstOrFail();

        Gate::authorize('view', $booking);

        return view('bookings.show', compact('booking'));
    }

    public function cancel(Request $request, string $code): RedirectResponse
    {
        $booking = Booking::where('code', $code)->firstOrFail();

        Gate::authorize('cancel', $booking);

        $reason = $request->input('reason', 'Dibatalkan oleh pelanggan');

        try {
            $this->transitionBookingStatusAction->execute(
                booking: $booking,
                newStatus: BookingStatus::CANCELLED,
                actor: $request->user(),
                reason: $reason
            );

            return redirect()->route('my-bookings.show', $booking->code)
                ->with('success', 'Pesanan rental Anda berhasil dibatalkan.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function invoice(string $code): View
    {
        $booking = Booking::where('code', $code)
            ->with(['items.item.category', 'payments', 'user'])
            ->firstOrFail();

        Gate::authorize('view', $booking);

        return view('bookings.invoice', compact('booking'));
    }
}
