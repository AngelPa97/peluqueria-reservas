<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-19 08:00'));

    $this->service = Service::factory()->create(['duration_minutes' => 45, 'price' => 22]);
    $this->stylist = stylistOffering($this->service);
    $this->client = User::factory()->create();
});

it('pide iniciar sesión para reservar', function () {
    $this->get(route('booking.create'))->assertRedirect(route('login'));
});

it('muestra el formulario de reserva', function () {
    $this->actingAs($this->client)
        ->get(route('booking.create'))
        ->assertOk()
        ->assertSee($this->service->name);
});

it('devuelve los huecos libres en JSON', function () {
    $this->actingAs($this->client)
        ->getJson(route('booking.availability', [
            'service_id' => $this->service->id,
            'stylist_id' => $this->stylist->id,
            'date' => '2026-10-19',
        ]))
        ->assertOk()
        ->assertJsonPath('slots.0', '09:00');
});

it('un cliente puede reservar una cita', function () {
    $this->actingAs($this->client)
        ->post(route('booking.store'), [
            'service_id' => $this->service->id,
            'stylist_id' => $this->stylist->id,
            'date' => '2026-10-20',
            'time' => '10:30',
            'notes' => 'Solo las puntas',
        ])
        ->assertRedirect(route('appointments.index'))
        ->assertSessionHas('status');

    $appointment = Appointment::sole();

    expect($appointment->user_id)->toBe($this->client->id)
        ->and($appointment->starts_at->format('Y-m-d H:i'))->toBe('2026-10-20 10:30')
        ->and($appointment->ends_at->format('H:i'))->toBe('11:15')
        ->and($appointment->status)->toBe(AppointmentStatus::Pending)
        ->and((float) $appointment->price)->toBe(22.0);
});

it('guarda el precio del momento aunque el servicio cambie después', function () {
    $this->actingAs($this->client)->post(route('booking.store'), [
        'service_id' => $this->service->id,
        'stylist_id' => $this->stylist->id,
        'date' => '2026-10-20',
        'time' => '10:30',
    ]);

    $this->service->update(['price' => 30]);

    expect((float) Appointment::sole()->price)->toBe(22.0);
});

it('no permite reservar un hueco ocupado', function () {
    Appointment::factory()->for($this->stylist)->for($this->service)->create([
        'starts_at' => '2026-10-20 10:00',
        'ends_at' => '2026-10-20 10:45',
    ]);

    $this->actingAs($this->client)
        ->post(route('booking.store'), [
            'service_id' => $this->service->id,
            'stylist_id' => $this->stylist->id,
            'date' => '2026-10-20',
            'time' => '10:30',
        ])
        ->assertSessionHasErrors('starts_at');

    expect(Appointment::count())->toBe(1);
});

it('no permite reservar fuera del horario', function (string $time) {
    $this->actingAs($this->client)
        ->post(route('booking.store'), [
            'service_id' => $this->service->id,
            'stylist_id' => $this->stylist->id,
            'date' => '2026-10-20',
            'time' => $time,
        ])
        ->assertSessionHasErrors('starts_at');
})->with(['08:00', '13:30', '15:00', '10:10']);

it('no permite reservar un servicio desactivado', function () {
    $this->service->update(['is_active' => false]);

    $this->actingAs($this->client)
        ->post(route('booking.store'), [
            'service_id' => $this->service->id,
            'stylist_id' => $this->stylist->id,
            'date' => '2026-10-20',
            'time' => '10:30',
        ])
        ->assertSessionHasErrors('service_id');
});
