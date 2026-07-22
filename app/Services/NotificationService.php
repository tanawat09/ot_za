<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    /**
     * Send in-app notification to a user.
     */
    public static function send(int $userId, string $title, string $message, ?string $link = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false,
        ]);
    }
}
