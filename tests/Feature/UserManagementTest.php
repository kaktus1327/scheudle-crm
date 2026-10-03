<?php

use App\Models\User;
use App\Notifications\TemporaryPasswordNotification;
use Illuminate\Support\Facades\Notification;

test('manager creates an employee who must change the one time password', function () {
    Notification::fake();

    $this->actingAs(manager())->post('/manager/users', [
        'name' => 'Nowy Pracownik',
        'email' => 'nowy@kino.test',
        'role' => 'employee',
        'hourly_rate' => 30.5,
        'position_ids' => [position('bar')->id],
    ])->assertRedirect('/manager/users');

    $user = User::where('email', 'nowy@kino.test')->sole();
    expect($user->must_change_password)->toBeTrue()
        ->and($user->canWorkAt(position('bar')))->toBeTrue();

    Notification::assertSentTo($user, TemporaryPasswordNotification::class);
});

test('user with a one time password is forced to change it', function () {
    $user = employee(['must_change_password' => true]);

    $this->actingAs($user)->get('/dashboard')->assertRedirect('/password/first-change');
    $this->actingAs($user)->get('/schedule')->assertRedirect('/password/first-change');

    $this->put('/password/first-change', [
        'current_password' => 'password',
        'password' => 'NoweHaslo123',
        'password_confirmation' => 'NoweHaslo123',
    ])->assertRedirect('/dashboard');

    expect($user->fresh()->must_change_password)->toBeFalse();
    $this->get('/dashboard')->assertOk();
});

test('manager resets a forgotten password', function () {
    Notification::fake();
    $employee = employee();

    $this->actingAs(manager())->post("/manager/users/{$employee->id}/reset-password")->assertSessionHasNoErrors();

    expect($employee->fresh()->must_change_password)->toBeTrue();
    Notification::assertSentTo($employee, TemporaryPasswordNotification::class);
});

test('firing an employee is a soft delete that frees future shifts', function () {
    $employee = employee();
    $shift = shiftFor($employee);

    $this->actingAs(manager())->delete("/manager/users/{$employee->id}");

    expect(User::find($employee->id))->toBeNull()
        ->and(User::withTrashed()->find($employee->id))->not->toBeNull()
        ->and($shift->fresh()->user_id)->toBeNull();

    $this->post('/logout');
    $this->post('/login', ['email' => $employee->email, 'password' => 'password']);
    $this->assertGuest();
});

test('manager cannot fire themselves', function () {
    $manager = manager();

    $this->actingAs($manager)->delete("/manager/users/{$manager->id}")->assertForbidden();
});
