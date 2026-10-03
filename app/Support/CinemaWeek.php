<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class CinemaWeek
{
    private function __construct(public readonly CarbonImmutable $start) {}

    public static function fromDate(CarbonInterface|string $date): self
    {
        $date = CarbonImmutable::parse($date)->startOfDay();

        return new self($date->isFriday() ? $date : $date->previous(CarbonInterface::FRIDAY));
    }

    public static function current(): self
    {
        return self::fromDate(now());
    }

    public static function next(): self
    {
        return self::current()->addWeeks(1);
    }

    public function addWeeks(int $weeks): self
    {
        return new self($this->start->addWeeks($weeks));
    }

    public function end(): CarbonImmutable
    {
        return $this->start->addDays(6);
    }

    public function days(): Collection
    {
        return collect(range(0, 6))->map(fn (int $offset) => $this->start->addDays($offset));
    }

    public function dayForOffset(int $offset): CarbonImmutable
    {
        return $this->start->addDays($offset);
    }

    public function contains(CarbonInterface $date): bool
    {
        return $date->between($this->start, $this->end()->endOfDay());
    }

    public function label(): string
    {
        return $this->start->format('d.m').' - '.$this->end()->format('d.m.Y');
    }

    public function toDateString(): string
    {
        return $this->start->toDateString();
    }

    public function toArray(): array
    {
        return [
            'start' => $this->start->toDateString(),
            'end' => $this->end()->toDateString(),
            'label' => $this->label(),
            'days' => $this->days()->map(fn (CarbonImmutable $day) => [
                'date' => $day->toDateString(),
                'weekday' => $day->locale('pl')->isoFormat('dddd'),
                'isWeekend' => $day->isWeekend(),
            ])->all(),
        ];
    }
}
