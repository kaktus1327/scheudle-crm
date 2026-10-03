<?php

namespace App\Http\Controllers\Manager;

use App\Enums\TradeStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShiftTradeResource;
use App\Models\ShiftTrade;
use App\Services\ShiftTradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TradeController extends Controller
{
    private const RELATIONS = [
        'requester', 'targetUser', 'claimer', 'decider',
        'offeredShift.position', 'offeredShift.user',
        'requestedShift.position', 'requestedShift.user',
    ];

    public function index(): Response
    {
        return Inertia::render('manager/trades/index', [
            'pending' => ShiftTradeResource::collection(
                ShiftTrade::where('status', TradeStatus::PendingManager)
                    ->with(self::RELATIONS)
                    ->orderByDesc('availability_conflict')
                    ->oldest()
                    ->get()
            ),
            'history' => ShiftTradeResource::collection(
                ShiftTrade::whereIn('status', [TradeStatus::Approved, TradeStatus::Rejected])
                    ->whereNotNull('decided_by')
                    ->with(self::RELATIONS)
                    ->latest('decided_at')
                    ->limit(20)
                    ->get()
            ),
        ]);
    }

    public function decide(Request $request, ShiftTrade $trade, ShiftTradeService $service): RedirectResponse
    {
        $this->authorize('decide', $trade);

        $approve = $request->validate(['approve' => ['required', 'boolean']])['approve'];
        $service->decide($trade, $request->user(), $approve);

        return back()->with('success', $approve ? 'Zaakceptowano - grafik zaktualizowany.' : 'Odrzucono wymianę.');
    }
}
