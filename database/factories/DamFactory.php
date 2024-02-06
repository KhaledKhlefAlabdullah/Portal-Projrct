<?php

namespace Database\Factories;

use App\Models\Dam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DamFactory extends Factory
{
    protected $model = Dam::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'location' => fake()->word(),
            'water_level' => fake()->randomFloat(),
            'discharge' => fake()->word(),
            'source' => fake()->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }
}
