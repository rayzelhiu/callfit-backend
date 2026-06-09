<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_templates', function (Blueprint $table) {

            $table->unsignedInteger('warmup_duration')
                ->default(10)
                ->after('description');

            $table->unsignedInteger('cooldown_duration')
                ->default(15)
                ->after('warmup_duration');

        });
    }

    public function down(): void
    {
        Schema::table('workout_templates', function (Blueprint $table) {

            $table->dropColumn([
                'warmup_duration',
                'cooldown_duration'
            ]);

        });
}
};
