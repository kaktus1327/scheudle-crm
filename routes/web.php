<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\FirstPasswordChangeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee;
use App\Http\Controllers\Manager;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('password/first-change', [FirstPasswordChangeController::class, 'edit'])->name('password.first-change');
    Route::put('password/first-change', [FirstPasswordChangeController::class, 'update'])->name('password.first-change.update');
});

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('archive', [ArchiveController::class, 'index'])->name('archive.index');
    Route::get('archive/{schedule}', [ArchiveController::class, 'show'])->name('archive.show');

    Route::get('attachments/{attachment}', AttachmentController::class)->name('attachments.download');

    Route::middleware('role:employee')->group(function () {
        Route::get('schedule', [Employee\ScheduleController::class, 'index'])->name('schedule.index');
        Route::get('schedule/mine', [Employee\ScheduleController::class, 'mine'])->name('schedule.mine');

        Route::get('availability', [Employee\AvailabilityController::class, 'edit'])->name('availability.edit');
        Route::put('availability', [Employee\AvailabilityController::class, 'update'])->name('availability.update');

        Route::get('trades', [Employee\TradeController::class, 'index'])->name('trades.index');
        Route::post('trades', [Employee\TradeController::class, 'store'])->name('trades.store');
        Route::post('trades/{trade}/respond', [Employee\TradeController::class, 'respond'])->name('trades.respond');
        Route::post('trades/{trade}/claim', [Employee\TradeController::class, 'claim'])->name('trades.claim');
        Route::post('trades/{trade}/cancel', [Employee\TradeController::class, 'cancel'])->name('trades.cancel');
        Route::post('shifts/{shift}/claim', [Employee\TradeController::class, 'claimOpenShift'])->name('shifts.claim');

        Route::get('messages', [Employee\MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [Employee\MessageController::class, 'show'])->name('messages.show');
        Route::post('messages/{message}/acknowledge', [Employee\MessageController::class, 'acknowledge'])->name('messages.acknowledge');

        Route::get('documents', [Employee\DocumentController::class, 'index'])->name('documents.index');
    });

    Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('schedules', [Manager\ScheduleController::class, 'index'])->name('schedules.index');
        Route::post('schedules', [Manager\ScheduleController::class, 'store'])->name('schedules.store');
        Route::get('schedules/{schedule}', [Manager\ScheduleController::class, 'edit'])->name('schedules.edit');
        Route::post('schedules/{schedule}/template', [Manager\ScheduleController::class, 'applyTemplate'])->name('schedules.template');
        Route::post('schedules/{schedule}/save-template', [Manager\ScheduleController::class, 'saveAsTemplate'])->name('schedules.save-template');
        Route::post('schedules/{schedule}/auto', [Manager\ScheduleController::class, 'autoFill'])->name('schedules.auto');
        Route::post('schedules/{schedule}/publish', [Manager\ScheduleController::class, 'publish'])->name('schedules.publish');

        Route::post('schedules/{schedule}/shifts', [Manager\ShiftController::class, 'store'])->name('shifts.store');
        Route::put('shifts/{shift}', [Manager\ShiftController::class, 'update'])->name('shifts.update');
        Route::put('shifts/{shift}/assign', [Manager\ShiftController::class, 'assign'])->name('shifts.assign');
        Route::delete('shifts/{shift}', [Manager\ShiftController::class, 'destroy'])->name('shifts.destroy');

        Route::resource('templates', Manager\TemplateController::class)->except(['show']);

        Route::get('trades', [Manager\TradeController::class, 'index'])->name('trades.index');
        Route::post('trades/{trade}/decide', [Manager\TradeController::class, 'decide'])->name('trades.decide');

        Route::get('messages', [Manager\MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/create', [Manager\MessageController::class, 'create'])->name('messages.create');
        Route::post('messages', [Manager\MessageController::class, 'store'])->name('messages.store');
        Route::get('messages/{message}', [Manager\MessageController::class, 'show'])->name('messages.show');

        Route::resource('users', Manager\UserController::class)->except(['show']);
        Route::post('users/{user}/restore', [Manager\UserController::class, 'restore'])->withTrashed()->name('users.restore');
        Route::post('users/{user}/reset-password', [Manager\UserController::class, 'resetPassword'])->name('users.reset-password');

        Route::get('documents', [Manager\DocumentController::class, 'index'])->name('documents.index');
        Route::post('users/{user}/documents', [Manager\DocumentController::class, 'store'])->name('documents.store');
        Route::delete('documents/{document}', [Manager\DocumentController::class, 'destroy'])->name('documents.destroy');
        Route::post('document-types', [Manager\DocumentController::class, 'storeType'])->name('document-types.store');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
