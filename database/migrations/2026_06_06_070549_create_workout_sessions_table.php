<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sessions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('template_id')
                ->constrained('workout_templates')
                ->cascadeOnDelete();

            $table->foreignId('started_by')
                ->constrained('users');

            $table->enum('status', [
                'waiting',
                'running',
                'paused',
                'finished'
            ])->default('waiting');

            // phase saat ini
            $table->enum('current_phase', [
                'demo',
                'warmup',
                'work',
                'rest',
                'switch',
                'cooldown',
                'finished'
            ])->default('demo');

            // posisi workout
            $table->unsignedInteger('current_station')->default(1);
            $table->unsignedInteger('current_set')->default(1);
            $table->unsignedInteger('current_round')->default(1);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sessions');
    }
};