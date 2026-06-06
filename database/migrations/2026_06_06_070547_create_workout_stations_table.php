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
       Schema::create('workout_stations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('template_id')
                ->constrained('workout_templates')
                ->cascadeOnDelete();

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            $table->unsignedInteger('station_number');

            $table->unsignedInteger('sort_order')->default(1);

            $table->unsignedInteger('work_duration_override')->nullable();

            $table->unsignedInteger('rest_duration_override')->nullable();

            $table->unsignedInteger('total_sets_override')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_stations');
    }
};
