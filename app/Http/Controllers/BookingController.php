<?php

namespace App\Http\Controllers;

use App\Actions\BookAppointment;
use App\Models\Service;
use App\Models\Stylist;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request): View
    {
        $services = Service::active()
            ->whereHas('stylists', fn ($q) => $q->where('is_active', true))
            ->with(['stylists' => fn ($q) => $q->where('is_active', true)->select('stylists.id', 'name', 'specialty')])
            ->orderBy('name')
            ->get();

        return view('booking.create', [
            'services' => $services,
            'selectedService' => $request->integer('service') ?: null,
            'minDate' => CarbonImmutable::today()->toDateString(),
            'maxDate' => CarbonImmutable::today()->addDays(AvailabilityService::BOOKING_WINDOW_DAYS)->toDateString(),
        ]);
    }

    /**
     * Devuelve en JSON las horas libres para el formulario de reserva.
     */
    public function availability(Request $request, AvailabilityService $availability): JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
            'stylist_id' => ['required', Rule::exists('stylists', 'id')->where('is_active', true)],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $slots = $availability->slotsFor(
            Stylist::findOrFail($data['stylist_id']),
            Service::findOrFail($data['service_id']),
            CarbonImmutable::parse($data['date']),
        );

        return response()->json([
            'slots' => $slots->map(fn (CarbonImmutable $slot) => $slot->format('H:i'))->values(),
        ]);
    }

    public function store(Request $request, BookAppointment $book): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
            'stylist_id' => ['required', Rule::exists('stylists', 'id')->where('is_active', true)],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'service_id' => 'servicio',
            'stylist_id' => 'estilista',
            'date' => 'fecha',
            'time' => 'hora',
            'notes' => 'notas',
        ]);

        $appointment = $book->handle(
            $request->user(),
            Stylist::findOrFail($data['stylist_id']),
            Service::findOrFail($data['service_id']),
            CarbonImmutable::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time'])->startOfMinute(),
            $data['notes'] ?? null,
        );

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Cita reservada para el '.$appointment->starts_at->translatedFormat('l j \d\e F \a \l\a\s H:i').'.');
    }
}
