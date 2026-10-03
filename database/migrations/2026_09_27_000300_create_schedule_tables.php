<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('template_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_offset');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->date('week_start')->unique();
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('schedule_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->restrictOnDelete();
            $table->date('date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('original_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('template_shifts');
        Schema::dropIfExists('schedule_templates');
    }
};
