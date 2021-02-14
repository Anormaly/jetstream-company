<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user should have a personal company.
     *
     * @return $this
     */
    public function withPersonalCompany()
    {
        return $this->has(
            Company::factory()
                ->state(function (array $attributes, User $user) {
                    return ['name' => $user->name.'\'s Company', 'user_id' => $user->id, 'personal_company' => true];
                }),
            'ownedCompanies'
        );
    }
}
