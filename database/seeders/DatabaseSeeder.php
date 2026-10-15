<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\Role;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Stylist;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administración',
            'email' => 'admin@peluqueria.test',
            'role' => Role::Admin,
        ]);

        $client = User::factory()->create([
            'name' => 'Lucía Fernández',
            'email' => 'cliente@peluqueria.test',
            'phone' => '600 123 456',
        ]);

        $otherClients = User::factory(8)
            ->state(fn () => ['name' => fake()->firstName().' '.fake()->lastName()])
            ->create();

        $services = collect([
            ['Corte de mujer', 'Lavado, corte y secado con cepillo.', 45, 22],
            ['Corte de hombre', 'Corte a tijera o máquina con acabado.', 30, 14],
            ['Arreglo de barba', 'Perfilado con navaja y toalla caliente.', 15, 8],
            ['Tinte completo', 'Coloración de raíz a puntas con productos sin amoniaco.', 90, 45],
            ['Mechas balayage', 'Iluminación degradada a mano alzada.', 120, 75],
            ['Peinado para eventos', 'Recogido o semirecogido para bodas y celebraciones.', 60, 35],
            ['Tratamiento de keratina', 'Alisado y nutrición profunda.', 120, 90],
        ])->map(fn (array $s) => Service::create([
            'name' => $s[0],
            'description' => $s[1],
            'duration_minutes' => $s[2],
            'price' => $s[3],
        ]));

        $stylists = collect([
            ['Marta Ruiz', 'Color y mechas', 'Más de 10 años especializada en coloración y técnicas de iluminación.', [0, 3, 4, 5, 6]],
            ['Javier López', 'Cortes y barbería', 'Barbero clásico con gusto por los degradados modernos.', [1, 2, 0]],
            ['Sara Gómez', 'Recogidos y eventos', 'Peinados para novias e invitadas, siempre con un toque natural.', [0, 5, 6, 3]],
        ])->map(function (array $s) use ($services) {
            $stylist = Stylist::create(['name' => $s[0], 'specialty' => $s[1], 'bio' => $s[2]]);
            $stylist->services()->attach($services->only($s[3])->pluck('id'));

            foreach (range(1, 5) as $day) {
                $stylist->workingHours()->createMany([
                    ['day_of_week' => $day, 'start_time' => '09:30', 'end_time' => '14:00'],
                    ['day_of_week' => $day, 'start_time' => '16:00', 'end_time' => '20:00'],
                ]);
            }
            $stylist->workingHours()->create(['day_of_week' => 6, 'start_time' => '09:00', 'end_time' => '14:00']);

            return $stylist;
        });

        $this->seedAppointments($stylists, collect([$client])->merge($otherClients));
    }

    /**
     * Genera citas de ejemplo en las dos últimas semanas y las dos próximas
     * respetando la disponibilidad real de cada estilista.
     */
    private function seedAppointments($stylists, $clients): void
    {
        $availability = app(AvailabilityService::class);

        foreach (range(-14, 14) as $offset) {
            $date = CarbonImmutable::today()->addDays($offset);

            foreach ($stylists as $stylist) {
                $service = $stylist->services->random();
                $slots = $offset < 0
                    ? $this->pastSlots($stylist, $date)
                    : $availability->slotsFor($stylist, $service, $date);

                foreach ($slots->shuffle()->take(rand(1, 3)) as $start) {
                    $end = $start->addMinutes($service->duration_minutes);

                    $clash = Appointment::where('stylist_id', $stylist->id)
                        ->where('starts_at', '<', $end)->where('ends_at', '>', $start)->exists();

                    if ($clash) {
                        continue;
                    }

                    Appointment::create([
                        'user_id' => $clients->random()->id,
                        'stylist_id' => $stylist->id,
                        'service_id' => $service->id,
                        'starts_at' => $start,
                        'ends_at' => $end,
                        'price' => $service->price,
                        'status' => $offset < 0
                            ? fake()->randomElement([AppointmentStatus::Completed, AppointmentStatus::Completed, AppointmentStatus::Cancelled])
                            : fake()->randomElement([AppointmentStatus::Pending, AppointmentStatus::Confirmed]),
                    ]);
                }
            }
        }
    }

    private function pastSlots(Stylist $stylist, CarbonImmutable $date)
    {
        return $stylist->workingHours
            ->where('day_of_week', $date->dayOfWeekIso)
            ->map(fn ($block) => $date->setTimeFromTimeString($block->start_time)->addMinutes(30 * rand(0, 4)))
            ->values();
    }
}
