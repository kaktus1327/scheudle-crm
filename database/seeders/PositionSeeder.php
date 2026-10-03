<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['name' => 'Obsługa', 'slug' => 'obsluga'],
            ['name' => 'Kasa', 'slug' => 'kasa'],
            ['name' => 'Bar', 'slug' => 'bar'],
            ['name' => 'Kawiarnia', 'slug' => 'kawiarnia'],
        ])->each(fn (array $p, int $i) => Position::updateOrCreate(['slug' => $p['slug']], [...$p, 'sort_order' => $i]));
    }
}
