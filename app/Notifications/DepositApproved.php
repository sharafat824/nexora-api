<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Deposit;

class DepositApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $deposit;

    public function __construct(Deposit $deposit)
    {
        $this->deposit = $deposit;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Deposit Approved')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your deposit of ' . number_format($this->deposit->amount, 2) . ' has been approved.')
        //    ->line('Transaction ID: ' . optional($this->deposit->transaction)->id)
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Your deposit of ' . number_format($this->deposit->amount, 2) . ' has been approved.',
        //    'deposit_id' => $this->deposit->id,
        ];
    }
}
