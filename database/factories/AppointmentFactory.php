<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Stylist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addDays(3)->setTime(10, 0);

        return [
            'user_id' => User::factory(),
            'stylist_id' => Stylist::factory(),
            'service_id' => Service::factory(),
            'starts_at' => $start,
            'ends_at' => $start->copy()->addMinutes(30),
            'status' => AppointmentStatus::Pending,
            'price' => 15,
        ];
    }

    public function status(AppointmentStatus $status): static
    {
        return $this->state(['status' => $status]);
    }
}
