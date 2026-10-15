<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Stylist;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('los clientes no pueden entrar en la administración', function (string $route) {
    $this->actingAs(User::factory()->create())
        ->get(route($route))
        ->assertForbidden();
})->with([
    'admin.dashboard',
    'admin.agenda',
    'admin.appointments.index',
    'admin.services.index',
    'admin.stylists.index',
]);

it('cada rol aterriza en su página tras iniciar sesión', function () {
    $this->actingAs($this->admin)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertRedirect(route('appointments.index'));
});

it('las páginas de administración cargan', function (string $route) {
    $service = Service::factory()->create();
    $stylist = stylistOffering($service);
    Appointment::factory()->for($stylist)->for($service)->create(['starts_at' => today()->setTime(10, 0), 'ends_at' => today()->setTime(10, 30)]);

    $this->actingAs($this->admin)->get(route($route))->assertOk();
})->with([
    'admin.dashboard',
    'admin.agenda',
    'admin.appointments.index',
    'admin.services.index',
    'admin.services.create',
    'admin.stylists.index',
    'admin.stylists.create',
]);

it('el admin puede crear un servicio', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.services.store'), [
            'name' => 'Corte infantil',
            'duration_minutes' => 30,
            'price' => 12.5,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.services.index'));

    expect(Service::sole())
        ->name->toBe('Corte infantil')
        ->duration_minutes->toBe(30)
        ->is_active->toBeTrue();
});

it('un servicio con citas se desactiva en lugar de borrarse', function () {
    $appointment = Appointment::factory()->create();

    $this->actingAs($this->admin)->delete(route('admin.services.destroy', $appointment->service));

    expect($appointment->service->fresh())->not->toBeNull()
        ->is_active->toBeFalse();
});

it('el admin puede crear un estilista con servicios y horario', function () {
    $service = Service::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.stylists.store'), [
            'name' => 'Ana Torres',
            'specialty' => 'Color',
            'is_active' => '1',
            'services' => [$service->id],
            'hours' => [
                ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '14:00'],
                ['day_of_week' => 1, 'start_time' => '16:00', 'end_time' => '20:00'],
                ['day_of_week' => 2, 'start_time' => '', 'end_time' => ''],
            ],
        ])
        ->assertRedirect(route('admin.stylists.index'));

    $stylist = Stylist::sole();

    expect($stylist->services->pluck('id')->all())->toBe([$service->id])
        ->and($stylist->workingHours)->toHaveCount(2);
});

it('rechaza franjas horarias solapadas o al revés', function (array $hours) {
    $this->actingAs($this->admin)
        ->post(route('admin.stylists.store'), ['name' => 'Ana', 'hours' => $hours])
        ->assertSessionHasErrors();

    expect(Stylist::count())->toBe(0);
})->with([
    'solapadas' => [[
        ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '14:00'],
        ['day_of_week' => 1, 'start_time' => '13:00', 'end_time' => '18:00'],
    ]],
    'fin antes que inicio' => [[
        ['day_of_week' => 3, 'start_time' => '14:00', 'end_time' => '09:00'],
    ]],
]);

it('el admin puede cambiar el estado de una cita', function () {
    $appointment = Appointment::factory()->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.appointments.status', $appointment), ['status' => 'confirmed'])
        ->assertRedirect();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Confirmed);
});

it('filtra las citas por estado', function () {
    $pending = Appointment::factory()->create();
    $cancelled = Appointment::factory()->status(AppointmentStatus::Cancelled)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.appointments.index', ['status' => 'cancelled']))
        ->assertOk()
        ->assertSee($cancelled->user->email)
        ->assertDontSee($pending->user->email);
});
