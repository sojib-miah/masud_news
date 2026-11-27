<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ReporterRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $reporter,
        public string $message
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reporter_registered',
            'title' => 'নতুন রিপোর্টার নিবন্ধিত',
            'message' => $this->message,
            'reporter_id' => $this->reporter->id,
            'reporter_name' => $this->reporter->name,
            'reporter_phone' => $this->reporter->phone,
            'url' => route('reporter.pending'),
            'icon' => 'fa-user-plus',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastType(): string
    {
        return 'NewNotification';
    }
}