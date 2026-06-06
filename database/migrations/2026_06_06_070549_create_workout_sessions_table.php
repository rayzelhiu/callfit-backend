<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workout_sessions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('template_id')
                ->constrained('workout_templates')
                ->cascadeOnDelete();

            $table->enum('status', [
                'waiting',
                'running',
                'paused',
                'finished'
            ])->default('waiting');

            $table->unsignedInteger('current_station')->default(1);

            $table->unsignedInteger('current_set')->default(1);

            $table->timestamp('started_at')->nullable();

            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_sessions');
    }
};
