<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Deposit;

class DepositStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $deposit;

    /**
     * Create a new notification instance.
     */
    public function __construct(Deposit $deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->deposit->status;
        $amount = $this->deposit->formatted_amount;

        $mailMessage = (new MailMessage)
            ->subject("Deposit {$status} - {$amount}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your deposit of {$amount} has been {$status}.");

        if ($status === 'rejected' && $this->deposit->rejection_reason) {
            $mailMessage->line("Reason: {$this->deposit->rejection_reason}");
        }

        $mailMessage->line('Thank you for using our service!');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Deposit {$this->deposit->status}",
            'message' => $this->getNotificationMessage(),
            'amount' => $this->deposit->amount,
            'status' => $this->deposit->status,
            'deposit_id' => $this->deposit->id,
            'date' => now()->toDateTimeString(),
            'type' => 'deposit_status_update',
        ];
    }

    protected function getNotificationMessage(): string
    {
        $message = "Your deposit of {$this->deposit->formatted_amount} has been {$this->deposit->status}";

        if ($this->deposit->status === 'rejected' && $this->deposit->rejection_reason) {
            $message .= ". Reason: {$this->deposit->rejection_reason}";
        }

        return $message;
    }
}
