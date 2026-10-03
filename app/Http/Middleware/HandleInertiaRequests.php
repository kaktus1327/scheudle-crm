<?php

namespace App\Http\Middleware;

use App\Models\ShiftTrade;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'email_verified_at' => $user->email_verified_at,
                    'must_change_password' => $user->must_change_password,
                ] : null,
            ],
            'badges' => fn () => $user ? $this->badges($user) : [],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    private function badges($user): array
    {
        if ($user->isManager()) {
            return [
                'trades' => ShiftTrade::where('status', 'pending_manager')->count(),
            ];
        }

        return [
            'messages' => $user->receivedMessages()->wherePivotNull('read_at')->count(),
            'trades' => ShiftTrade::where('status', 'pending_peer')->where('target_user_id', $user->id)->count(),
        ];
    }
}
