<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'employee_code' => strtoupper($this->faker->bothify('EMP###')),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'employment_status' => 'active',
            'department' => $this->faker->randomElement(['Quality', 'Operations', 'Finance']),
            'designation' => $this->faker->jobTitle(),
        ];
    }
}
