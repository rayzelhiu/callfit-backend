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

            $table->string('name');

            $table->text('description')->nullable();

            $table->unsignedInteger('work_duration')->default(40);

            $table->unsignedInteger('rest_duration')->default(15);

            $table->unsignedInteger('switch_duration')->default(20);

            $table->unsignedInteger('total_sets')->default(2);

            $table->unsignedInteger('total_rounds')->default(1);

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

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
