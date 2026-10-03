<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_trades', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('status', 30)->index();
            $table->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('claimer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('offered_shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->foreignId('requested_shift_id')->nullable()->constrained('shifts')->cascadeOnDelete();
            $table->boolean('availability_conflict')->default(false);
            $table->string('note', 500)->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('peer_responded_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_trades');
    }
};
