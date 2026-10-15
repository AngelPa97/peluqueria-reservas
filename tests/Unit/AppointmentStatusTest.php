<?php

use App\Enums\AppointmentStatus;

it('las canceladas no ocupan hueco en la agenda', function () {
    expect(AppointmentStatus::blocking())
        ->toContain('pending', 'confirmed', 'completed')
        ->not->toContain('cancelled');
});

it('cada estado tiene etiqueta en español', function () {
    expect(AppointmentStatus::Pending->label())->toBe('Pendiente')
        ->and(AppointmentStatus::Cancelled->label())->toBe('Cancelada');
});
