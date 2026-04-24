<?php
// php artisan tinker --execute="App\Models\User::factory(X)->create()"
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserFactory extends Factory
{

    protected static ?string $password;


    public function definition(): array
    {
        // Latvian first names
        $latvianFirstNames = [
            'Jānis', 'Andris', 'Mārtiņš', 'Guntis', 'Aldis', 'Juris', 'Edgars', 'Kārlis',
            'Roberts', 'Miks', 'Oskars', 'Emīls', 'Ričards', 'Kristaps', 'Markuss',
            'Anna', 'Līga', 'Maija', 'Inese', 'Laura', 'Kristīne', 'Ieva', 'Sanita',
            'Linda', 'Dace', 'Zane', 'Elīna', 'Monika', 'Sabīne', 'Rebecca', 'Katrina'
        ];
        
        // Latvian last names
        $latvianLastNames = [
            'Kalniņš', 'Bērziņš', 'Ozoliņš', 'Jānis', 'Liepiņš', 'Krūmiņš', 'Vītoliņš',
            'Lejiņš', 'Mālnieks', 'Zariņš', 'Birkavs', 'Puriņš', 'Šmits', 'Kauliņš',
            'Eglītis', 'Siliņš', 'Liepins', 'Krastiņš', 'Vēveris', 'Cālītis', 'Bērzkalns',
            'Ozols', 'Liepa', 'Krūms', 'Vītols', 'Leja', 'Mālnieks', 'Zarins', 'Bērziņš'
        ];
        
        $firstName = fake()->randomElement($latvianFirstNames);
        $lastName = fake()->randomElement($latvianLastNames);
        
        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower(str_replace(['ā', 'č', 'ē', 'ī', 'ķ', 'ļ', 'ņ', 'š', 'ū', 'ž'], ['a', 'c', 'e', 'i', 'k', 'l', 'n', 's', 'u', 'z'], $firstName . '.' . $lastName)) . '@' . fake()->randomElement(['gmail.com', 'inbox.lv', 'mail.lv', 'yahoo.com', 'outlook.com']),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'time_credits' => 10,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
