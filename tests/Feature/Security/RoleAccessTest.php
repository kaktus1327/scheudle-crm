<?php

use App\Models\Message;
use App\Models\ScheduleTemplate;

dataset('manager pages', [
    'grafiki' => '/manager/schedules',
    'wymiany' => '/manager/trades',
    'wiadomości' => '/manager/messages',
    'nowa wiadomość' => '/manager/messages/create',
    'pracownicy' => '/manager/users',
    'nowy pracownik' => '/manager/users/create',
    'dokumenty' => '/manager/documents',
    'szablony' => '/manager/templates',
    'nowy szablon' => '/manager/templates/create',
]);

dataset('employee pages', [
    'grafik' => '/schedule',
    'moje zmiany' => '/schedule/mine',
    'dyspozycyjność' => '/availability',
    'giełda' => '/trades',
    'wiadomości' => '/messages',
    'dokumenty' => '/documents',
]);

test('guests are redirected to login', function (string $url) {
    $this->get($url)->assertRedirect('/login');
})->with('manager pages')->with('employee pages');

test('managers can open every manager page', function (string $url) {
    $this->actingAs(manager())->get($url)->assertOk();
})->with('manager pages');

test('employees get 403 on every manager page', function (string $url) {
    $this->actingAs(employee())->get($url)->assertForbidden();
})->with('manager pages');

test('employees can open every employee page', function (string $url) {
    $this->actingAs(employee())->get($url)->assertOk();
})->with('employee pages');

test('both roles see their own dashboard', function () {
    $this->actingAs(manager())->get('/dashboard')->assertOk()->assertInertia(fn ($page) => $page->component('manager/dashboard'));
    $this->actingAs(employee())->get('/dashboard')->assertOk()->assertInertia(fn ($page) => $page->component('employee/dashboard'));
});

test('employees cannot perform manager actions', function () {
    $employee = employee();
    $schedule = publishedSchedule();
    $shift = shiftFor($employee, $schedule);
    $template = ScheduleTemplate::create(['name' => 'Test']);

    $this->actingAs($employee);

    $this->post("/manager/schedules/{$schedule->id}/publish")->assertForbidden();
    $this->post("/manager/schedules/{$schedule->id}/auto")->assertForbidden();
    $this->put("/manager/shifts/{$shift->id}/assign", ['user_id' => $employee->id])->assertForbidden();
    $this->delete("/manager/shifts/{$shift->id}")->assertForbidden();
    $this->delete("/manager/templates/{$template->id}")->assertForbidden();
    $this->post('/manager/users', ['name' => 'Hacker', 'email' => 'h@x.pl', 'role' => 'manager', 'hourly_rate' => 999])->assertForbidden();
    $this->post('/manager/messages', ['title' => 'x', 'body' => 'y'])->assertForbidden();

    expect($shift->fresh()->user_id)->toBe($employee->id);
    expect(Message::count())->toBe(0);
});

test('pages with parameters render for the right role only', function () {
    $employee = employee();
    $schedule = publishedSchedule();
    shiftFor($employee, $schedule);
    $manager = manager();

    $this->actingAs($manager)->get("/manager/schedules/{$schedule->id}")->assertOk()
        ->assertInertia(fn ($page) => $page->component('manager/schedules/builder'));
    $this->actingAs($manager)->get("/manager/users/{$employee->id}/edit")->assertOk();
    $this->actingAs($manager)->get("/archive/{$schedule->id}")->assertOk();

    $this->actingAs($employee)->get("/manager/schedules/{$schedule->id}")->assertForbidden();
    $this->actingAs($employee)->get("/manager/users/{$manager->id}/edit")->assertForbidden();
    $this->actingAs($employee)->get('/archive')->assertOk();
    $this->actingAs($employee)->get("/archive/{$schedule->id}")->assertOk();
});

test('resource collections reach the frontend as plain lists', function () {
    $employee = employee();
    shiftFor($employee, date: today()->toDateString());

    $this->actingAs($employee)->get('/dashboard')->assertInertia(
        fn ($page) => expect($page->toArray()['props']['upcomingShifts'])->toBeList()->toHaveCount(1)
    );
});

test('registration is disabled', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', ['name' => 'x', 'email' => 'x@x.pl', 'password' => 'secret123', 'password_confirmation' => 'secret123'])->assertNotFound();
});

test('security headers are set', function () {
    $this->get('/login')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});
