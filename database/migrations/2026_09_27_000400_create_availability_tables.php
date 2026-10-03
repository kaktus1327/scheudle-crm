<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');
            $table->string('note', 500)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'week_start']);
        });

        Schema::create('availability_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('unavailable')->default(false);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            $table->unique(['availability_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_days');
        Schema::dropIfExists('availabilities');
    }
};
