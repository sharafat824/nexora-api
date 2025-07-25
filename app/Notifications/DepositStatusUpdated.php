<?php

namespace App\Notifications;

use App\Models\Deposit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DepositStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected Deposit $deposit;

    public function __construct(Deposit $deposit)
    {
        $this->deposit = $deposit;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = ucfirst($this->deposit->status); // Capitalize
        $amount = number_format($this->deposit->amount, 2);
        $date = $this->deposit->created_at->format('F j, Y h:i A');

        $mail = (new MailMessage)
            ->subject("Deposit {$status} - \${$amount}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your deposit of **\${$amount}** made on **{$date}** has been **{$status}**.");

        if ($this->deposit->status === 'rejected') {
            if ($this->deposit->rejection_reason) {
                $mail->line("**Reason:** {$this->deposit->rejection_reason}");
            }
        }

        if ($this->deposit->status === 'completed') {
            $mail->line("The funds have been successfully added to your wallet.");
        }

        if ($this->deposit->admin_notes) {
            $mail->line("**Admin Notes:** {$this->deposit->admin_notes}");
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Deposit {$this->deposit->status}",
            'message' => $this->getNotificationMessage(),
            'amount' => $this->deposit->amount,
            'status' => $this->deposit->status,
            'deposit_id' => $this->deposit->id,
            'date' => $this->deposit->created_at->toDateTimeString(),
            'type' => 'deposit_status_update',
        ];
    }

    protected function getNotificationMessage(): string
    {
        $amount = number_format($this->deposit->amount, 2);
        $message = "Your deposit of \${$amount} has been {$this->deposit->status}.";

        if ($this->deposit->status === 'rejected' && $this->deposit->rejection_reason) {
            $message .= " Reason: {$this->deposit->rejection_reason}.";
        }

        return $message;
    }
}
