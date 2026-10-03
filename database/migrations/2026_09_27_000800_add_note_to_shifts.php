<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['shifts', 'template_shifts'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('note', 50)->nullable()->after('end_time');
            });
        }
    }

    public function down(): void
    {
        foreach (['shifts', 'template_shifts'] as $table) {
            Schema::table($table, fn (Blueprint $table) => $table->dropColumn('note'));
        }
    }
};
