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
        Schema::create('workout_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('template_id')
                ->constrained('workout_templates')
                ->cascadeOnDelete();

            $table->integer('station_number');

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            $table->integer('work_duration');

            $table->integer('rest_duration');

            $table->integer('switch_duration');

            $table->integer('total_sets');

            $table->integer('sort_order')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_templates');
    }
};
