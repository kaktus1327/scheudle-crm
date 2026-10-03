<?php

use App\Enums\ScheduleStatus;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Schedule;
use App\Support\CinemaWeek;
use Illuminate\Support\Facades\Storage;

test('schedule page does not leak colleagues private data', function () {
    $me = employee();
    $colleague = employee(['name' => 'Jan Testowy', 'email' => 'tajny@kino.test', 'phone' => '600700800', 'hourly_rate' => 45.67]);
    shiftFor($colleague);

    $response = $this->actingAs($me)->get('/schedule')->assertOk();

    $html = $response->getContent();
    expect($html)
        ->toContain($colleague->name)
        ->not->toContain('tajny@kino.test')
        ->not->toContain('600700800')
        ->not->toContain('45.67')
        ->not->toContain('hourly_rate');
});

test('shared auth props never include the hourly rate', function () {
    $this->actingAs(employee(['hourly_rate' => 77.77]))
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page->missing('auth.user.hourly_rate')->missing('auth.user.password'));
});

test('employees cannot see draft schedules', function () {
    $employee = employee();
    $draft = Schedule::create(['week_start' => CinemaWeek::current()->toDateString(), 'status' => ScheduleStatus::Draft]);
    shiftFor($employee, $draft);

    $this->actingAs($employee)->get("/archive/{$draft->id}")->assertForbidden();
    $this->actingAs($employee)->get('/schedule')->assertInertia(fn ($page) => $page->where('schedule', null));
});

test('employees can only read messages addressed to them', function () {
    $author = manager();
    $reader = employee();
    $outsider = employee();

    $message = Message::create(['author_id' => $author->id, 'title' => 'Tylko dla jednej osoby', 'body' => 'Poufne']);
    $message->recipients()->attach($reader);

    $this->actingAs($reader)->get("/messages/{$message->id}")->assertOk();
    $this->actingAs($outsider)->get("/messages/{$message->id}")->assertForbidden();
});

test('attachments are private and require authorization', function () {
    Storage::fake('local');
    Storage::disk('local')->put('attachments/1/regulamin.pdf', 'PDF');

    $reader = employee();
    $outsider = employee();
    $message = Message::create(['author_id' => manager()->id, 'title' => 'Regulamin', 'body' => '...']);
    $message->recipients()->attach($reader);
    $attachment = MessageAttachment::create([
        'message_id' => $message->id,
        'path' => 'attachments/1/regulamin.pdf',
        'original_name' => 'regulamin.pdf',
        'mime_type' => 'application/pdf',
        'size' => 3,
    ]);

    $this->get("/attachments/{$attachment->id}")->assertRedirect('/login');
    $this->actingAs($outsider)->get("/attachments/{$attachment->id}")->assertForbidden();
    $this->actingAs($reader)->get("/attachments/{$attachment->id}")->assertOk()->assertDownload('regulamin.pdf');
});

test('employees cannot escalate their role through the profile form', function () {
    $employee = employee();

    $this->actingAs($employee)->patch('/settings/profile', [
        'name' => 'Nowe Imię',
        'email' => $employee->email,
        'role' => 'manager',
        'hourly_rate' => 999,
    ]);

    $employee->refresh();
    expect($employee->isManager())->toBeFalse()
        ->and((float) $employee->hourly_rate)->not->toBe(999.0);
});
