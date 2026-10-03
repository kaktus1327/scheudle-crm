<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function view(User $user, Message $message): bool
    {
        return $user->isManager() || $this->isRecipient($user, $message);
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function acknowledge(User $user, Message $message): bool
    {
        return $message->requires_acknowledgement && $this->isRecipient($user, $message);
    }

    private function isRecipient(User $user, Message $message): bool
    {
        return $message->recipients()->whereKey($user->id)->exists();
    }
}
