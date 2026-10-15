<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Stylist;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookAppointment
{
    public function __construct(private AvailabilityService $availability) {}

    /**
     * Crea la cita comprobando de nuevo la disponibilidad dentro de una
     * transacción, bloqueando la fila del estilista para evitar que dos
     * clientes reserven el mismo hueco a la vez.
     */
    public function handle(User $client, Stylist $stylist, Service $service, CarbonImmutable $start, ?string $notes = null): Appointment
    {
        return DB::transaction(function () use ($client, $stylist, $service, $start, $notes) {
            Stylist::whereKey($stylist->id)->lockForUpdate()->first();

            if (! $this->availability->isAvailable($stylist, $service, $start)) {
                throw ValidationException::withMessages([
                    'starts_at' => 'Ese horario ya no está disponible. Elige otro, por favor.',
                ]);
            }

            return Appointment::create([
                'user_id' => $client->id,
                'stylist_id' => $stylist->id,
                'service_id' => $service->id,
                'starts_at' => $start,
                'ends_at' => $start->addMinutes($service->duration_minutes),
                'status' => AppointmentStatus::Pending,
                'price' => $service->price,
                'notes' => $notes,
            ]);
        });
    }
}
