<?php

namespace App\Http\Controllers\Manager;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\PositionResource;
use App\Models\DocumentType;
use App\Models\Position;
use App\Models\User;
use App\Notifications\TemporaryPasswordNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $showArchived = $request->boolean('archived');

        $users = User::query()
            ->when($showArchived, fn ($q) => $q->onlyTrashed())
            ->with(['positions', 'documents.type'])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return Inertia::render('manager/users/index', [
            'users' => EmployeeResource::collection($users),
            'archived' => $showArchived,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return $this->form(null);
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $password = Str::password(12, symbols: false);

        $user = DB::transaction(function () use ($request, $password) {
            $user = new User($request->safe()->only(['name', 'email', 'phone']));
            $user->forceFill([
                'password' => $password,
                'role' => Role::from($request->validated('role')),
                'hourly_rate' => $request->validated('hourly_rate'),
                'must_change_password' => true,
                'email_verified_at' => now(),
            ])->save();

            $user->positions()->sync($request->validated('position_ids', []));

            return $user;
        });

        $user->notify(new TemporaryPasswordNotification($password));

        return to_route('manager.users.index')->with('success', "Konto utworzone. Hasło jednorazowe wysłano na {$user->email}.");
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return $this->form($user->load(['positions', 'documents.type']));
    }

    public function update(EmployeeRequest $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user) {
            $user->fill($request->safe()->only(['name', 'email', 'phone']));
            $user->forceFill([
                'hourly_rate' => $request->validated('hourly_rate'),
                'role' => $request->user()->is($user) ? $user->role : Role::from($request->validated('role')),
            ])->save();

            $user->positions()->sync($request->validated('position_ids', []));
        });

        return back()->with('success', 'Zapisano zmiany.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        DB::transaction(function () use ($user) {
            $user->shifts()->whereDate('date', '>=', today())->update(['user_id' => null]);
            $user->delete();
        });

        return to_route('manager.users.index')->with('success', "{$user->name} - konto zarchiwizowane, przyszłe zmiany zwolnione.");
    }

    public function restore(User $user): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user->restore();

        return back()->with('success', "{$user->name} - konto przywrócone.");
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $password = Str::password(12, symbols: false);

        $user->forceFill(['password' => $password, 'must_change_password' => true, 'remember_token' => null])->save();

        DB::table('sessions')->where('user_id', $user->id)->delete();

        $user->notify(new TemporaryPasswordNotification($password, isReset: true));

        return back()->with('success', "Nowe hasło jednorazowe wysłano na {$user->email}.");
    }

    private function form(?User $user): Response
    {
        return Inertia::render('manager/users/form', [
            'user' => $user ? new EmployeeResource($user) : null,
            'positions' => PositionResource::collection(Position::orderBy('sort_order')->get()),
            'documentTypes' => DocumentType::orderBy('name')->get(['id', 'name']),
            'roles' => collect(Role::cases())->map(fn (Role $r) => ['value' => $r->value, 'label' => $r->label()]),
        ]);
    }
}
