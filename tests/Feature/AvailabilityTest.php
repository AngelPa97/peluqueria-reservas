<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Stylist;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;

beforeEach(function () {
    // Lunes 19/10/2026 a las 8:00, antes de abrir.
    $this->travelTo(CarbonImmutable::parse('2026-10-19 08:00'));

    $this->service = Service::factory()->create(['duration_minutes' => 60]);
    $this->stylist = stylistOffering($this->service);
    $this->availability = app(AvailabilityService::class);
});

function times($slots): array
{
    return $slots->map(fn ($s) => $s->format('H:i'))->all();
}

it('ofrece huecos cada 15 minutos dentro del horario laboral', function () {
    $slots = times($this->availability->slotsFor($this->stylist, $this->service, today()->toImmutable()));

    expect($slots)
        ->toContain('09:00', '09:15', '13:00', '16:00', '19:00')
        // Un servicio de 60 min no cabe empezando a las 13:15 (la franja acaba a las 14:00).
        ->not->toContain('13:15', '14:00', '15:00', '19:15');
});

it('no ofrece huecos que se solapan con otra cita', function () {
    Appointment::factory()->for($this->stylist)->for($this->service)->create([
        'starts_at' => '2026-10-19 10:00',
        'ends_at' => '2026-10-19 11:00',
    ]);

    $slots = times($this->availability->slotsFor($this->stylist, $this->service, today()->toImmutable()));

    expect($slots)
        ->toContain('09:00', '11:00')
        ->not->toContain('09:15', '09:45', '10:00', '10:45');
});

it('las citas canceladas liberan el hueco', function () {
    Appointment::factory()->for($this->stylist)->for($this->service)
        ->status(AppointmentStatus::Cancelled)
        ->create(['starts_at' => '2026-10-19 10:00', 'ends_at' => '2026-10-19 11:00']);

    expect(times($this->availability->slotsFor($this->stylist, $this->service, today()->toImmutable())))
        ->toContain('10:00');
});

it('no ofrece horas que ya han pasado', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-19 12:05'));

    $slots = times($this->availability->slotsFor($this->stylist, $this->service, today()->toImmutable()));

    expect($slots)->not->toContain('12:00')->toContain('12:15');
});

it('no hay huecos los días que el estilista no trabaja', function () {
    $sunday = CarbonImmutable::parse('2026-10-25');

    expect($this->availability->slotsFor($this->stylist, $this->service, $sunday))->toBeEmpty();
});

it('no hay huecos si el estilista no realiza el servicio', function () {
    $other = Service::factory()->create();

    expect($this->availability->slotsFor($this->stylist, $other, today()->toImmutable()))->toBeEmpty();
});

it('no permite reservar fuera de la ventana de reservas', function () {
    $tooFar = today()->toImmutable()->addDays(AvailabilityService::BOOKING_WINDOW_DAYS + 1);
    $yesterday = today()->toImmutable()->subDay();

    expect($this->availability->slotsFor($this->stylist, $this->service, $tooFar))->toBeEmpty()
        ->and($this->availability->slotsFor($this->stylist, $this->service, $yesterday))->toBeEmpty();
});

it('un estilista recién creado sin indicar is_active está activo', function () {
    $stylist = Stylist::create(['name' => 'Nueva']);
    $stylist->services()->attach($this->service);
    $stylist->workingHours()->create(['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '12:00']);

    expect($this->availability->slotsFor($stylist, $this->service, today()->toImmutable()))->not->toBeEmpty();
});
