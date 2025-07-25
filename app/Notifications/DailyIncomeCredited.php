<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DailyIncomeCredited extends Notification implements ShouldQueue
{
    use Queueable;

    protected float $amount;

    public function __construct(float $amount)
    {
        $this->amount = $amount;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Daily Profit Credited - Nexora UK')
            ->greeting("Hello {$notifiable->name},")
            ->line("You've just received your daily profit of **$" . number_format($this->amount, 2) . "** in your Nexora UK wallet.")
            ->line('Keep growing with Nexora UK! 🚀')
            ->salutation('— The Nexora UK Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'amount' => $this->amount,
            'currency' => 'USD',
            'type' => 'daily_income',
        ];
    }
}
