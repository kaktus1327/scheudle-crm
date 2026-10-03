<?php

namespace App\Console\Commands;

use App\Models\UserDocument;
use App\Notifications\DocumentExpiringNotification;
use Illuminate\Console\Command;

class SendDocumentReminders extends Command
{
    protected $signature = 'documents:remind';

    protected $description = 'Wysyła przypomnienia o kończących się dokumentach (książeczka sanepidowska, status ucznia itp.)';

    public function handle(): int
    {
        $sent = 0;

        UserDocument::query()
            ->whereNull('reminded_at')
            ->whereHas('user')
            ->with(['type', 'user'])
            ->lazyById()
            ->filter(fn (UserDocument $doc) => $doc->isExpiringSoon())
            ->each(function (UserDocument $doc) use (&$sent) {
                $doc->user->notify(new DocumentExpiringNotification($doc));
                $doc->forceFill(['reminded_at' => now()])->save();
                $sent++;
            });

        $this->info("Wysłano przypomnień: {$sent}");

        return self::SUCCESS;
    }
}
