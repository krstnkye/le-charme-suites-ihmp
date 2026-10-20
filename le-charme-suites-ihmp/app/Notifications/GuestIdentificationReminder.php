<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuestIdentificationReminder extends Notification
{
    public function __construct(public Reservation $reservation) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reservation = $this->reservation;

        return (new MailMessage)
            ->subject('Please prepare a valid ID for your Le Charmé Suites stay')
            ->greeting('Hello '.$reservation->guest->first_name.',')
            ->line('Thank you for booking with Le Charmé Suites Subic. We have not yet received a valid government-issued ID for your reservation.')
            ->line('Booking reference: '.($reservation->booking_reference ?: '#LC-'.str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT)))
            ->line('Arrival: '.$reservation->check_in_date?->format('F j, Y'))
            ->line('Please present the ID at the front desk on arrival, or reply to this email with the ID type and number.')
            ->salutation('Front Office, Le Charmé Suites Subic');
    }
}
