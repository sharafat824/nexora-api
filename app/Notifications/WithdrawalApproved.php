<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WithdrawalApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected Withdrawal $withdrawal;

    public function __construct(Withdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $amount = number_format($this->withdrawal->amount, 2);
        $date = $this->withdrawal->created_at->format('F j, Y h:i A');

        $mail = (new MailMessage)
            ->subject("Withdrawal Approved - \${$amount}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your withdrawal request of **\${$amount}** made on **{$date}** has been approved.")
            ->line("The funds will be processed shortly via your selected withdrawal method.");

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Withdrawal Approved',
            'message' => "Your withdrawal of \${$this->withdrawal->amount} has been approved.",
            'amount' => $this->withdrawal->amount,
            'withdrawal_id' => $this->withdrawal->id,
            'transaction_id' => $this->withdrawal->transaction_id ?? null,
            'status' => 'approved',
            'type' => 'withdrawal',
            'date' => $this->withdrawal->created_at->toDateTimeString(),
        ];
    }
}
