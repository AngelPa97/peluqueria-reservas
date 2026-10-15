<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-19 08:00'));
    $this->client = User::factory()->create();
});

it('el cliente ve sus citas', function () {
    $appointment = Appointment::factory()->for($this->client)->create();
    $foreign = Appointment::factory()->create(['starts_at' => now()->addDays(5)]);

    $this->actingAs($this->client)
        ->get(route('appointments.index'))
        ->assertOk()
        ->assertSee($appointment->service->name)
        ->assertDontSee($foreign->service->name);
});

it('el cliente puede cancelar su cita con antelación', function () {
    $appointment = Appointment::factory()->for($this->client)->create([
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
    ]);

    $this->actingAs($this->client)
        ->patch(route('appointments.cancel', $appointment))
        ->assertRedirect();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Cancelled);
});

it('no se puede cancelar con menos de 2 horas de antelación', function () {
    $appointment = Appointment::factory()->for($this->client)->create([
        'starts_at' => now()->addHour(),
        'ends_at' => now()->addHours(2),
    ]);

    $this->actingAs($this->client)
        ->patch(route('appointments.cancel', $appointment))
        ->assertForbidden();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Pending);
});

it('no se puede cancelar la cita de otra persona', function () {
    $appointment = Appointment::factory()->create();

    $this->actingAs($this->client)
        ->patch(route('appointments.cancel', $appointment))
        ->assertForbidden();
});
