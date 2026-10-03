<?php

namespace App\Http\Controllers;

use App\Enums\TradeStatus;
use App\Http\Resources\ShiftResource;
use App\Models\Availability;
use App\Models\Message;
use App\Models\ShiftTrade;
use App\Models\User;
use App\Models\UserDocument;
use App\Services\HoursCalculator;
use App\Services\PayrollCalculator;
use App\Support\CinemaWeek;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly HoursCalculator $hours,
        private readonly PayrollCalculator $payroll,
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        return $user->isManager()
            ? $this->manager()
            : $this->employee($user);
    }

    private function employee(User $user): Response
    {
        $month = now();

        $upcoming = $user->shifts()
            ->inPublishedSchedules()
            ->whereDate('date', '>=', today())
            ->with('position')
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit(3)
            ->get();

        return Inertia::render('employee/dashboard', [
            'stats' => [
                'hoursThisMonth' => $this->hours->hoursInMonth($user, $month),
                'expectedPay' => $this->payroll->grossForMonth($user, $month),
                'shiftsSplit' => $this->hours->weekdayWeekendSplit($user, $month),
            ],
            'history' => $this->hours->monthlyHistory($user),
            'upcomingShifts' => ShiftResource::collection($upcoming),
            'pendingAcknowledgements' => $user->receivedMessages()
                ->where('requires_acknowledgement', true)
                ->wherePivotNull('acknowledged_at')
                ->count(),
            'availabilitySubmitted' => Availability::where('user_id', $user->id)
                ->whereDate('week_start', CinemaWeek::next()->toDateString())
                ->whereNotNull('submitted_at')
                ->exists(),
            'nextWeek' => CinemaWeek::next()->toArray(),
        ]);
    }

    private function manager(): Response
    {
        $month = now();
        $employees = User::employees()->orderBy('name')->get();

        $team = $employees->map(fn (User $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'hours' => $this->hours->hoursInMonth($u, $month),
            'pay' => $this->payroll->grossForMonth($u, $month),
        ]);

        $lastMessage = Message::latest()->withCount([
            'recipients',
            'recipients as acknowledged_count' => fn ($q) => $q->whereNotNull('message_recipients.acknowledged_at'),
        ])->first();

        return Inertia::render('manager/dashboard', [
            'team' => $team->values(),
            'totals' => [
                'hours' => round($team->sum('hours'), 2),
                'pay' => round($team->sum('pay'), 2),
                'employees' => $employees->count(),
            ],
            'lastMessage' => $lastMessage ? [
                'id' => $lastMessage->id,
                'title' => $lastMessage->title,
                'requires_acknowledgement' => $lastMessage->requires_acknowledgement,
                'recipients' => $lastMessage->recipients_count,
                'acknowledged' => $lastMessage->acknowledged_count,
            ] : null,
            'pendingTrades' => ShiftTrade::where('status', TradeStatus::PendingManager)->count(),
            'expiringDocuments' => UserDocument::whereHas('user')->expiringWithin(30)->count(),
            'availability' => [
                'week' => CinemaWeek::next()->toArray(),
                'submitted' => Availability::whereDate('week_start', CinemaWeek::next()->toDateString())
                    ->whereNotNull('submitted_at')
                    ->count(),
            ],
        ]);
    }
}
