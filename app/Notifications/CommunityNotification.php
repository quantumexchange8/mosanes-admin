<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommunityNotification extends Notification
{
    use Queueable;

    protected $type;
    protected $data;
    /**
     * Create a new notification instance.
     */
    public function __construct($type, $data = [])
    {
        $this->type = $type; // e.g. 'like', 'comment', 'hashtag'
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);

    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => $this->type,
            'post_id' => $this->data['post']->id ?? null,
            'user_name' => $this->data['user']->name ?? null,
            'comment' => $this->data['comment']->content ?? null,
            'hashtag_id' => $this->data['hashtag']->id ?? null,
            'route' => $this->data['route'] ?? null,
        ];
    }
}
