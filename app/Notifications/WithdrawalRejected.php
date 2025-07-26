<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WithdrawalRejected extends Notification implements ShouldQueue
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
        $reason = $this->withdrawal->rejection_reason ?? 'Not specified';

        return (new MailMessage)
            ->subject("Withdrawal Rejected - \${$amount}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your withdrawal request of **\${$amount}** made on **{$date}** has been rejected.")
            ->line("Reason: {$reason}");
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Withdrawal Rejected',
            'message' => "Your withdrawal of \${$this->withdrawal->amount} has been rejected.",
            'reason' => $this->withdrawal->rejection_reason,
            'amount' => $this->withdrawal->amount,
            'withdrawal_id' => $this->withdrawal->id,
            'status' => 'rejected',
            'type' => 'withdrawal',
            'date' => $this->withdrawal->created_at->toDateTimeString(),
        ];
    }
}
