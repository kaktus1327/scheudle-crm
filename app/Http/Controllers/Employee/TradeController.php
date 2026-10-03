<?php

namespace App\Http\Controllers\Employee;

use App\Enums\TradeStatus;
use App\Enums\TradeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreShiftTradeRequest;
use App\Http\Resources\PersonResource;
use App\Http\Resources\ShiftResource;
use App\Http\Resources\ShiftTradeResource;
use App\Models\Shift;
use App\Models\ShiftTrade;
use App\Models\User;
use App\Services\ShiftTradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TradeController extends Controller
{
    private const RELATIONS = [
        'requester', 'targetUser', 'claimer',
        'offeredShift.position', 'offeredShift.user',
        'requestedShift.position', 'requestedShift.user',
    ];

    public function __construct(private readonly ShiftTradeService $trades) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $upcoming = fn () => Shift::query()
            ->inPublishedSchedules()
            ->whereDate('date', '>=', today())
            ->with(['position', 'user']);

        $pendingClaims = ShiftTrade::where('type', TradeType::OpenShiftClaim)
            ->pending()
            ->pluck('requester_id', 'offered_shift_id');

        $openShifts = $upcoming()->whereNull('user_id')->orderBy('date')->get()->map(fn (Shift $shift) => [
            ...(new ShiftResource($shift))->resolve($request),
            'claim' => match ($pendingClaims[$shift->id] ?? null) {
                null => null,
                $user->id => 'mine',
                default => 'taken',
            },
        ]);

        return Inertia::render('employee/trades/index', [
            'market' => ShiftTradeResource::collection(
                ShiftTrade::where(fn ($q) => $q
                    ->where('status', TradeStatus::Open)
                    ->orWhere(fn ($q) => $q->where('type', TradeType::OpenOffer)->where('status', TradeStatus::PendingManager)))
                    ->whereHas('offeredShift', fn ($q) => $q->whereDate('date', '>=', today()))
                    ->with(self::RELATIONS)
                    ->latest()
                    ->get()
            ),
            'openShifts' => $openShifts,
            'incoming' => ShiftTradeResource::collection(
                ShiftTrade::where('target_user_id', $user->id)->pending()->with(self::RELATIONS)->latest()->get()
            ),
            'outgoing' => ShiftTradeResource::collection(
                ShiftTrade::where(fn ($q) => $q->where('requester_id', $user->id)->orWhere('claimer_id', $user->id))
                    ->with(self::RELATIONS)->latest()->limit(20)->get()
            ),
            'myShifts' => ShiftResource::collection($upcoming()->where('user_id', $user->id)->orderBy('date')->get()),
            'colleagues' => PersonResource::collection(
                User::employees()->whereKeyNot($user->id)->orderBy('name')->get()
            ),
            'colleagueShifts' => ShiftResource::collection(
                $upcoming()->whereNotNull('user_id')->where('user_id', '!=', $user->id)->orderBy('date')->get()
            ),
        ]);
    }

    public function store(StoreShiftTradeRequest $request): RedirectResponse
    {
        $offered = Shift::findOrFail($request->validated('offered_shift_id'));
        $this->ensureTradable($offered);

        $this->trades->propose(
            requester: $request->user(),
            offered: $offered,
            type: TradeType::from($request->validated('type')),
            target: $request->validated('target_user_id') ? User::find($request->validated('target_user_id')) : null,
            requested: $request->validated('requested_shift_id') ? Shift::find($request->validated('requested_shift_id')) : null,
            note: $request->validated('note'),
        );

        return back()->with('success', 'Propozycja wysłana.');
    }

    public function respond(Request $request, ShiftTrade $trade): RedirectResponse
    {
        $this->authorize('respond', $trade);

        $accept = $request->validate(['accept' => ['required', 'boolean']])['accept'];
        $this->trades->respondAsPeer($trade, $accept);

        return back()->with('success', $accept ? 'Zaakceptowano - teraz decyzja kierownika.' : 'Odrzucono propozycję.');
    }

    public function claim(Request $request, ShiftTrade $trade): RedirectResponse
    {
        $this->authorize('claim', $trade);

        $this->trades->claim($trade, $request->user());

        return back()->with('success', 'Zgłoszono - czeka na akceptację kierownika.');
    }

    public function claimOpenShift(Request $request, Shift $shift): RedirectResponse
    {
        abort_unless($shift->isOpen() && $shift->schedule->isPublished(), 404);
        $this->ensureTradable($shift);

        $alreadyClaimed = ShiftTrade::where('offered_shift_id', $shift->id)
            ->where('type', TradeType::OpenShiftClaim)
            ->pending()
            ->exists();

        if ($alreadyClaimed) {
            throw ValidationException::withMessages(['shift' => 'Ktoś już zgłosił się na tę zmianę.']);
        }

        $this->trades->propose($request->user(), $shift, TradeType::OpenShiftClaim);

        return back()->with('success', 'Zgłoszono - czeka na akceptację kierownika.');
    }

    public function cancel(ShiftTrade $trade): RedirectResponse
    {
        $this->authorize('cancel', $trade);

        $trade->forceFill(['status' => TradeStatus::Cancelled])->save();

        return back()->with('success', 'Propozycja anulowana.');
    }

    private function ensureTradable(Shift $shift): void
    {
        if ($shift->date->isPast() && ! $shift->date->isToday()) {
            throw ValidationException::withMessages(['offered_shift_id' => 'Nie można wymieniać zmian z przeszłości.']);
        }
    }
}
