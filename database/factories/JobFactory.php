<?php
//$jobs = Job::factory(5)->create();
namespace Database\Factories;


use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraph(),
            'time_credits' => fake()->numberBetween(10, 100),
            'category' => fake()->randomElement(['development', 'design', 'marketing', 'other']),
            'user_id' => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
