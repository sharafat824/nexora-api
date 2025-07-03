<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Withdrawal;

class WithdrawalApproved extends Notification
{
    use Queueable;

    protected $withdrawal;

    public function __construct(Withdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // or just ['mail'] if you don’t use in-app notifications
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Withdrawal Approved')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your withdrawal request of ' . number_format($this->withdrawal->amount, 2) . ' has been approved.')
        //    ->line('Transaction ID: ' . $this->withdrawal->transaction_id)
            ->line('Thank you for using our platform.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Your withdrawal of ' . number_format($this->withdrawal->amount, 2) . ' has been approved.',
           // 'withdrawal_id' => $this->withdrawal->id,
        ];
    }
}
