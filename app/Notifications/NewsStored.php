<?php

namespace App\Notifications;

use App\Models\News;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewsStored extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public News $news,
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
            'type' => 'news_stored',
            'title' => 'নতুন নিউজ জমা পড়েছে',
            'message' => $this->message,
            'news_id' => $this->news->id,
            'news_title' => $this->news->title,
            'news_slug' => $this->news->slug,
            'news_image' => $this->news->image
                ? asset('storage/' . $this->news->image)
                : null,
            'reporter_name' => $this->news->user?->name,
            'url' => route('admin.news.edit', $this->news->id),
            'icon' => 'fa-newspaper',
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