<?php

namespace App\Modules\Notifications\Services;

use App\Models\User;
use App\Modules\Notifications\Notifications\SystemNotification;

class NotificationService
{
    /**
     * إرسال إشعار إلى مستخدم محدد.
     */
    public function send(
        User $user,
        string $title,
        string $message,
        array $additionalData = []
    ): void {
        $user->notify(new SystemNotification(array_merge([
            'title' => $title,
            'message' => $message,
        ], $additionalData)));
    }
}
