<?php

namespace Database\Factories;


use App\Enums\AccountRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name"=>$this->faker->name,
            "email"=>$this->faker->unique()->safeEmail,
            "password"=>Hash::make("password123"),
            "role"=>$this->faker->randomElement(AccountRole::cases()), 
            //'role' => AccountRole::Agent,
        ];
    }
}
