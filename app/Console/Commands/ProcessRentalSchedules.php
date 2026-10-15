<?php

namespace App\Console\Commands;

use App\Actions\TransitionBookingStatusAction;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Notifications\RentalReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRentalSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rental:process-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proses otomasi pesanan rental: kedaluwarsa booking, peringatan overdue, dan pengingat H-1';

    /**
     * Execute the console command.
     */
    public function handle(TransitionBookingStatusAction $transitionAction): int
    {
        $this->info('Memulai pemrosesan jadwal rental...');

        $now = now();

        // 1. Auto-expire pending bookings without payment proof that passed expires_at
        $expiredBookings = Booking::where('status', BookingStatus::PENDING)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->doesntHave('payments')
            ->get();

        $expiredCount = 0;
        foreach ($expiredBookings as $booking) {
            try {
                $transitionAction->execute(
                    booking: $booking,
                    newStatus: BookingStatus::EXPIRED,
                    actor: null,
                    reason: 'Batas waktu pembayaran habis dan tidak ada bukti pembayaran yang diunggah (otomatis sistem).'
                );
                $expiredCount++;
                $this->line("Pesanan {$booking->code} telah dialihkan ke EXPIRED.");
            } catch (\Throwable $e) {
                $this->error("Gagal meng-expire booking {$booking->code}: " . $e->getMessage());
                Log::error("Failed to expire booking {$booking->code}: " . $e->getMessage());
            }
        }
        $this->info("Total pesanan expired: {$expiredCount}");

        // 2. Overdue detection & warning (1x per day per booking)
        $overdueBookings = Booking::overdue()->with('user')->get();
        $overdueNotifiedCount = 0;

        foreach ($overdueBookings as $booking) {
            $user = $booking->user;
            if (! $user) {
                continue;
            }

            // Check if already notified today for this overdue booking
            $alreadyNotifiedToday = $user->notifications()
                ->where('type', RentalReminderNotification::class)
                ->where('data->type', 'overdue')
                ->where('data->booking_id', $booking->id)
                ->whereDate('created_at', $now->toDateString())
                ->exists();

            if (! $alreadyNotifiedToday) {
                $user->notify(new RentalReminderNotification(
                    booking: $booking,
                    type: 'overdue',
                    title: 'Peringatan: Masa Sewa Anda Telah Berakhir',
                    message: "Pesanan {$booking->code} telah melewati batas pengembalian (" . format_date_id($booking->end_at) . "). Denda keterlambatan berjalan per hari. Harap segera kembalikan unit ke toko kami."
                ));
                $overdueNotifiedCount++;
                $this->line("Notifikasi peringatan overdue dikirim untuk {$booking->code}.");
            }
        }
        $this->info("Total notifikasi overdue dikirim: {$overdueNotifiedCount}");

        // 3. Reminder H-1 Pickup (start_at within 23 to 25 hours from now)
        $h1PickupStart = $now->copy()->addHours(23);
        $h1PickupEnd = $now->copy()->addHours(25);

        $pickupReminders = Booking::where('status', BookingStatus::CONFIRMED)
            ->whereBetween('start_at', [$h1PickupStart, $h1PickupEnd])
            ->with('user')
            ->get();

        $pickupReminderCount = 0;
        foreach ($pickupReminders as $booking) {
            $user = $booking->user;
            if (! $user) {
                continue;
            }

            $alreadyReminded = $user->notifications()
                ->where('type', RentalReminderNotification::class)
                ->where('data->type', 'pickup_reminder')
                ->where('data->booking_id', $booking->id)
                ->exists();

            if (! $alreadyReminded) {
                $user->notify(new RentalReminderNotification(
                    booking: $booking,
                    type: 'pickup_reminder',
                    title: 'Pengingat: Jadwal Pengambilan Rental Besok',
                    message: "Halo {$user->name}, pengingat bahwa jadwal sewa {$booking->code} dimulai besok (" . format_date_id($booking->start_at) . "). Silakan ambil unit tepat waktu."
                ));
                $pickupReminderCount++;
                $this->line("Pengingat pickup dikirim untuk {$booking->code}.");
            }
        }
        $this->info("Total pengingat H-1 pickup dikirim: {$pickupReminderCount}");

        // 4. Reminder H-1 Return (end_at within 23 to 25 hours from now)
        $h1ReturnStart = $now->copy()->addHours(23);
        $h1ReturnEnd = $now->copy()->addHours(25);

        $returnReminders = Booking::where('status', BookingStatus::PICKED_UP)
            ->whereBetween('end_at', [$h1ReturnStart, $h1ReturnEnd])
            ->with('user')
            ->get();

        $returnReminderCount = 0;
        foreach ($returnReminders as $booking) {
            $user = $booking->user;
            if (! $user) {
                continue;
            }

            $alreadyReminded = $user->notifications()
                ->where('type', RentalReminderNotification::class)
                ->where('data->type', 'return_reminder')
                ->where('data->booking_id', $booking->id)
                ->exists();

            if (! $alreadyReminded) {
                $user->notify(new RentalReminderNotification(
                    booking: $booking,
                    type: 'return_reminder',
                    title: 'Pengingat: Jadwal Pengembalian Unit Besok',
                    message: "Halo {$user->name}, masa sewa {$booking->code} akan berakhir besok (" . format_date_id($booking->end_at) . "). Pastikan unit lengkap saat dikembalikan."
                ));
                $returnReminderCount++;
                $this->line("Pengingat return dikirim untuk {$booking->code}.");
            }
        }
        $this->info("Total pengingat H-1 return dikirim: {$returnReminderCount}");

        $this->info('Selesai memproses jadwal rental.');

        return self::SUCCESS;
    }
}
