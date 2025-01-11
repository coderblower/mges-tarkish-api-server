<?php

namespace App\Notifications;


use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserDeletedNotification extends Notification
{

    private $user;
    private $note;
    /**
     * Create a new notification instance.
     */
    public function __construct($user, $note)
    {
        $this->user = $user;
        $this->note = $note;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database'];  // We only want to use the database driver
    }
    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => 'User ' . $this->user->name . ' has been deleted. passport: ' .$this->user->candidate->passport, 
            'user_id' => $this->user->id,
            'data' => $this->user->toArray(),
            'notification_type' => 'user-deleted',
            'note' => $this->note
        ];
    }


}
