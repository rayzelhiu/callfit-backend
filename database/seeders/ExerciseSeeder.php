<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exercise;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [

            ['name'=>'Burpee'],
            ['name'=>'Push Up'],
            ['name'=>'Squat'],
            ['name'=>'Jump Squat'],
            ['name'=>'Mountain Climber'],
            ['name'=>'Jumping Jack'],
            ['name'=>'High Knees'],
            ['name'=>'Plank'],
            ['name'=>'Lunge'],
            ['name'=>'Sit Up'],
            ['name'=>'Russian Twist'],
            ['name'=>'Bicycle Crunch'],
            ['name'=>'Wall Sit'],
            ['name'=>'Bear Crawl'],
            ['name'=>'Side Plank'],

        ];

        foreach ($exercises as $exercise) {

            Exercise::create([
                'name'=>$exercise['name']
            ]);

        }
    }
}