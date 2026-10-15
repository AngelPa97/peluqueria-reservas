<?php

namespace Database\Factories;

use App\Models\Stylist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stylist>
 */
class StylistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->firstName().' '.fake()->lastName(),
            'specialty' => fake()->randomElement(['Color', 'Cortes', 'Barbería', 'Recogidos']),
            'bio' => fake()->sentence(12),
            'is_active' => true,
        ];
    }

    /**
     * Horario de lunes a viernes de 9:00 a 14:00 y de 16:00 a 20:00.
     */
    public function withDefaultSchedule(): static
    {
        return $this->afterCreating(function (Stylist $stylist) {
            foreach (range(1, 5) as $day) {
                $stylist->workingHours()->createMany([
                    ['day_of_week' => $day, 'start_time' => '09:00', 'end_time' => '14:00'],
                    ['day_of_week' => $day, 'start_time' => '16:00', 'end_time' => '20:00'],
                ]);
            }
        });
    }
}
