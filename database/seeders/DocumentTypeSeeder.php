<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        DocumentType::updateOrCreate(['name' => 'Książeczka sanepidowska'], ['remind_days_before' => 30]);
        DocumentType::updateOrCreate(['name' => 'Legitymacja uczniowska / studencka'], ['remind_days_before' => 14]);
    }
}
