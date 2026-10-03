<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\TradeStatus;
use App\Enums\TradeType;
use App\Models\Availability;
use App\Models\DocumentType;
use App\Models\Message;
use App\Models\Position;
use App\Models\Schedule;
use App\Models\ScheduleTemplate;
use App\Models\ShiftTrade;
use App\Models\User;
use App\Models\UserDocument;
use App\Services\AutoScheduler;
use App\Services\ScheduleBuilder;
use App\Support\CinemaWeek;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DemoSeeder extends Seeder
{
    private const WEEKS_BACK = 20;

    public function run(AutoScheduler $scheduler, ScheduleBuilder $builder): void
    {
        mt_srand(2026);

        $positions = Position::pluck('id', 'slug');
        $manager = $this->user('Anna Nowak', 'kierownik@kino.test', Role::Manager, 0);
        $employees = $this->employees($positions);

        $current = CinemaWeek::current();
        $calm = ScheduleTemplate::where('name', 'Spokojny tydzień')->first();
        $busy = ScheduleTemplate::where('name', 'Intensywny tydzień')->first();
        $womensCinema = ScheduleTemplate::where('name', 'Kino Kobiet')->first();

        foreach (range(self::WEEKS_BACK, 0) as $back) {
            $week = $current->addWeeks(-$back);
            $this->availabilityFor($employees, $week);

            $schedule = $builder->draftFor($week, $manager);
            $builder->applyTemplate($schedule, match (true) {
                $back % 4 === 0 => $womensCinema,
                $back % 4 === 2 => $busy,
                default => $calm,
            });
            $scheduler->fill($schedule);
            $builder->publish($schedule);
        }

        $next = CinemaWeek::next();
        $demo = $employees->firstWhere('email', 'pracownik@kino.test');
        $this->availabilityFor($employees->reject(fn (User $u) => $u->is($demo)), $next);
        $builder->applyTemplate($builder->draftFor($next, $manager), $calm);

        $this->openShiftAndTrades($current, $demo, $employees);
        $this->messages($manager, $employees);
        $this->documents($employees);
    }

    private function user(string $name, string $email, Role $role, float $rate): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'password' => 'password',
            'role' => $role,
            'hourly_rate' => $rate,
            'email_verified_at' => now(),
            'must_change_password' => false,
        ])->save();

        return $user;
    }

    private function employees(Collection $positions): Collection
    {
        $people = [
            ['Kacper Wiśniewski', 'pracownik@kino.test', ['obsluga', 'bar']],
            ['Zuzanna Kowalczyk', 'zuzanna@kino.test', ['obsluga', 'bar', 'kawiarnia']],
            ['Jakub Lewandowski', 'jakub@kino.test', ['obsluga']],
            ['Julia Wójcik', 'julia@kino.test', ['kawiarnia', 'bar']],
            ['Szymon Kamiński', 'szymon@kino.test', ['obsluga', 'bar']],
            ['Maja Zielińska', 'maja@kino.test', ['obsluga', 'kawiarnia']],
            ['Filip Szymański', 'filip@kino.test', ['bar']],
            ['Lena Woźniak', 'lena@kino.test', ['obsluga', 'kasa']],
            ['Antoni Dąbrowski', 'antoni@kino.test', ['obsluga', 'bar']],
            ['Hanna Kozłowska', 'hanna@kino.test', ['kawiarnia']],
            ['Michał Jankowski', 'michal@kino.test', ['obsluga']],
            ['Oliwia Mazur', 'oliwia@kino.test', ['obsluga', 'bar', 'kawiarnia']],
            ['Wiktoria Krawczyk', 'wiktoria@kino.test', ['kasa', 'bar']],
            ['Igor Piotrowski', 'igor@kino.test', ['bar']],
            ['Natalia Grabowska', 'natalia@kino.test', ['bar', 'kawiarnia']],
            ['Adam Pawlak', 'adam@kino.test', ['obsluga', 'bar']],
            ['Zofia Michalska', 'zofia@kino.test', ['kasa', 'obsluga']],
            ['Bartosz Nowicki', 'bartosz@kino.test', ['bar']],
            ['Amelia Adamczyk', 'amelia@kino.test', ['bar', 'kawiarnia']],
            ['Tymon Dudek', 'tymon@kino.test', ['obsluga']],
            ['Laura Zając', 'laura@kino.test', ['bar', 'kasa']],
            ['Kamil Wieczorek', 'kamil@kino.test', ['obsluga', 'bar']],
        ];

        return collect($people)->map(function (array $p) use ($positions) {
            $user = $this->user($p[0], $p[1], Role::Employee, [28.10, 30.50, 32.00][mt_rand(0, 2)]);
            $user->positions()->sync(collect($p[2])->map(fn ($slug) => $positions[$slug]));

            return $user->load('positions');
        });
    }

    private function availabilityFor(Collection $employees, CinemaWeek $week): void
    {
        foreach ($employees as $employee) {
            $availability = Availability::updateOrCreate(
                ['user_id' => $employee->id, 'week_start' => $week->toDateString()],
                ['submitted_at' => $week->start->subDays(4)],
            );
            $availability->days()->delete();

            foreach ($week->days() as $day) {
                $unavailable = mt_rand(1, 100) <= 25;

                $availability->days()->create([
                    'date' => $day->toDateString(),
                    'unavailable' => $unavailable,
                    'start_time' => $unavailable ? null : ['09:00', '11:00', '15:00', '16:00'][mt_rand(0, 3)],
                    'end_time' => $unavailable ? null : ['22:00', '23:30', '01:00'][mt_rand(0, 2)],
                ]);
            }
        }
    }

    private function openShiftAndTrades(CinemaWeek $week, User $demo, Collection $employees): void
    {
        $schedule = Schedule::whereDate('week_start', $week->toDateString())->firstOrFail();
        $upcoming = $schedule->shifts()->whereDate('date', '>=', today())->whereNotNull('user_id')->get();

        $upcoming->first()?->update(['user_id' => null]);

        $others = $upcoming->where('user_id', '!=', $demo->id)->whereNotNull('user_id')->values();
        $mine = $upcoming->where('user_id', $demo->id)->values();

        if ($others->count() >= 3) {
            ShiftTrade::create([
                'type' => TradeType::Giveaway,
                'status' => TradeStatus::PendingPeer,
                'requester_id' => $others[0]->user_id,
                'target_user_id' => $demo->id,
                'offered_shift_id' => $others[0]->id,
                'note' => 'Mam kolokwium, weźmiesz?',
            ]);

            ShiftTrade::create([
                'type' => TradeType::OpenOffer,
                'status' => TradeStatus::Open,
                'requester_id' => $others[1]->user_id,
                'offered_shift_id' => $others[1]->id,
            ]);

            $claimer = $employees->first(fn (User $u) => ! in_array($u->id, [$demo->id, $others[2]->user_id], true));
            ShiftTrade::create([
                'type' => TradeType::Giveaway,
                'status' => TradeStatus::PendingManager,
                'requester_id' => $others[2]->user_id,
                'target_user_id' => $claimer->id,
                'offered_shift_id' => $others[2]->id,
                'availability_conflict' => true,
                'peer_responded_at' => now()->subHour(),
            ]);
        }

        if ($mine->isNotEmpty() && $others->count() >= 4) {
            ShiftTrade::create([
                'type' => TradeType::Swap,
                'status' => TradeStatus::PendingPeer,
                'requester_id' => $demo->id,
                'target_user_id' => $others[3]->user_id,
                'offered_shift_id' => $mine[0]->id,
                'requested_shift_id' => $others[3]->id,
            ]);
        }
    }

    private function messages(User $manager, Collection $employees): void
    {
        $ids = $employees->pluck('id');

        $procedures = Message::create([
            'author_id' => $manager->id,
            'title' => 'Nowa procedura zamykania kas',
            'body' => "Cześć,\n\nod poniedziałku obowiązuje nowa procedura zamykania kas barowych. Najważniejsze zmiany:\n\n1. Raport X drukujemy przed przeliczeniem gotówki.\n2. Koperty z utargiem podpisują dwie osoby.\n3. Klucze do sejfu oddajemy tylko kierownikowi zmiany.\n\nProszę o potwierdzenie zapoznania się.\n\nAnna",
            'requires_acknowledgement' => true,
        ]);
        $procedures->recipients()->attach($ids);
        $procedures->recipients()->updateExistingPivot($ids->take(7)->all(), ['read_at' => now()->subDay(), 'acknowledged_at' => now()->subDay()]);
        $procedures->created_at = now()->subDays(2);
        $procedures->save();

        $premiere = Message::create([
            'author_id' => $manager->id,
            'title' => 'Premiera w przyszły piątek',
            'body' => "W przyszły piątek mamy dużą premierę - grafik będzie ułożony na szablonie intensywnym. Jeśli możecie, dajcie szerszą dyspozycyjność na weekend.\n\nDzięki!",
            'requires_acknowledgement' => false,
        ]);
        $premiere->recipients()->attach($ids);
    }

    private function documents(Collection $employees): void
    {
        $sanepid = DocumentType::where('name', 'Książeczka sanepidowska')->first();
        $student = DocumentType::where('name', 'Legitymacja uczniowska / studencka')->first();

        foreach ($employees as $i => $employee) {
            UserDocument::updateOrCreate(
                ['user_id' => $employee->id, 'document_type_id' => $sanepid->id],
                ['valid_until' => today()->addDays([-3, 12, 25, 90, 180, 365][$i % 6])],
            );

            if ($i % 2 === 0) {
                UserDocument::updateOrCreate(
                    ['user_id' => $employee->id, 'document_type_id' => $student->id],
                    ['valid_until' => today()->addDays([10, 40, 200][$i % 3])],
                );
            }
        }
    }
}
