<?php

use App\Enums\TradeStatus;
use App\Models\ShiftTrade;
use App\Support\CinemaWeek;

test('giveaway goes through peer and manager before the schedule changes', function () {
    $alice = employee();
    $bob = employee();
    $shift = shiftFor($alice);

    $this->actingAs($alice)->post('/trades', [
        'type' => 'giveaway',
        'offered_shift_id' => $shift->id,
        'target_user_id' => $bob->id,
    ])->assertSessionHasNoErrors();

    $trade = ShiftTrade::sole();
    expect($trade->status)->toBe(TradeStatus::PendingPeer);

    $this->actingAs(employee())->post("/trades/{$trade->id}/respond", ['accept' => true])->assertForbidden();

    $this->actingAs($bob)->post("/trades/{$trade->id}/respond", ['accept' => true]);
    expect($trade->fresh()->status)->toBe(TradeStatus::PendingManager)
        ->and($shift->fresh()->user_id)->toBe($alice->id);

    $this->actingAs(manager())->post("/manager/trades/{$trade->id}/decide", ['approve' => true]);

    $shift->refresh();
    expect($trade->fresh()->status)->toBe(TradeStatus::Approved)
        ->and($shift->user_id)->toBe($bob->id)
        ->and($shift->original_user_id)->toBe($alice->id);
});

test('swap exchanges both shifts after approval', function () {
    $alice = employee();
    $bob = employee();
    $schedule = publishedSchedule();
    $aliceShift = shiftFor($alice, $schedule);
    $bobShift = shiftFor($bob, $schedule, start: '10:00', end: '16:00');

    $this->actingAs($alice)->post('/trades', [
        'type' => 'swap',
        'offered_shift_id' => $aliceShift->id,
        'target_user_id' => $bob->id,
        'requested_shift_id' => $bobShift->id,
    ])->assertSessionHasNoErrors();

    $trade = ShiftTrade::sole();
    $this->actingAs($bob)->post("/trades/{$trade->id}/respond", ['accept' => true]);
    $this->actingAs(manager())->post("/manager/trades/{$trade->id}/decide", ['approve' => true]);

    expect($aliceShift->fresh()->user_id)->toBe($bob->id)
        ->and($bobShift->fresh()->user_id)->toBe($alice->id);
});

test('employees cannot offer someone elses shift', function () {
    $shift = shiftFor(employee());

    $this->actingAs(employee())->post('/trades', [
        'type' => 'open_offer',
        'offered_shift_id' => $shift->id,
    ])->assertSessionHasErrors('offered_shift_id');

    expect(ShiftTrade::count())->toBe(0);
});

test('open offer can be claimed only once', function () {
    $owner = employee();
    $shift = shiftFor($owner);

    $this->actingAs($owner)->post('/trades', ['type' => 'open_offer', 'offered_shift_id' => $shift->id]);
    $trade = ShiftTrade::sole();

    $this->actingAs($owner)->post("/trades/{$trade->id}/claim")->assertForbidden();

    $first = employee();
    $this->actingAs($first)->post("/trades/{$trade->id}/claim")->assertSessionHasNoErrors();
    $this->actingAs(employee())->post("/trades/{$trade->id}/claim")->assertForbidden();

    expect($trade->fresh()->claimer_id)->toBe($first->id)
        ->and($trade->fresh()->status)->toBe(TradeStatus::PendingManager);
});

test('claim is flagged when the employee did not submit availability', function () {
    $claimer = employee();
    $open = shiftFor(null);

    $this->actingAs($claimer)->post("/shifts/{$open->id}/claim")->assertSessionHasNoErrors();
    expect(ShiftTrade::sole()->availability_conflict)->toBeTrue();
});

test('claimed open shift shows as pending instead of claimable', function () {
    $claimer = employee();
    $open = shiftFor(null);

    $this->actingAs($claimer)->post("/shifts/{$open->id}/claim");

    $this->actingAs($claimer)->get('/trades')->assertInertia(fn ($page) => $page->where('openShifts.0.claim', 'mine'));
    $this->actingAs(employee())->get('/trades')->assertInertia(fn ($page) => $page->where('openShifts.0.claim', 'taken'));
    $this->actingAs(employee())->post("/shifts/{$open->id}/claim")->assertSessionHasErrors('shift');

    expect(ShiftTrade::count())->toBe(1);
});

test('claimed open offer stays on the market as pending', function () {
    $owner = employee();
    $shift = shiftFor($owner);
    $this->actingAs($owner)->post('/trades', ['type' => 'open_offer', 'offered_shift_id' => $shift->id]);
    $this->actingAs(employee())->post('/trades/'.ShiftTrade::sole()->id.'/claim');

    $this->actingAs(employee())->get('/trades')->assertInertia(fn ($page) => $page
        ->where('market.0.status', 'pending_manager')
        ->where('market.0.can.claim', false));
});

test('claim is not flagged when availability matches', function () {
    $claimer = employee();
    availability($claimer, CinemaWeek::current());
    $open = shiftFor(null);

    $this->actingAs($claimer)->post("/shifts/{$open->id}/claim");
    expect(ShiftTrade::sole()->availability_conflict)->toBeFalse();
});

test('manager can reject a trade and the schedule stays the same', function () {
    $alice = employee();
    $shift = shiftFor($alice);
    $this->actingAs($alice)->post('/trades', ['type' => 'open_offer', 'offered_shift_id' => $shift->id]);
    $trade = ShiftTrade::sole();
    $this->actingAs(employee())->post("/trades/{$trade->id}/claim");

    $this->actingAs(manager())->post("/manager/trades/{$trade->id}/decide", ['approve' => false]);

    expect($trade->fresh()->status)->toBe(TradeStatus::Rejected)
        ->and($shift->fresh()->user_id)->toBe($alice->id);
});
