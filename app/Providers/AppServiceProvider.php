<?php

namespace App\Providers;

use App\Models\MessageAttachment;
use App\Models\ShiftTrade;
use App\Models\UserDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        JsonResource::withoutWrapping();

        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        Route::model('trade', ShiftTrade::class);
        Route::model('document', UserDocument::class);
        Route::model('attachment', MessageAttachment::class);
    }
}
