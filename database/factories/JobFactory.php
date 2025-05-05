<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{

    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'user_id' => User::factory(),
            'salary' => '$50,000 USD'
        ];
    }
}
