<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $appointments = $request->user()->appointments()
            ->with(['service', 'stylist'])
            ->latest('starts_at')
            ->get();

        [$upcoming, $past] = $appointments->partition(
            fn (Appointment $a) => $a->starts_at->isFuture() && $a->status !== AppointmentStatus::Cancelled
        );

        return view('appointments.index', [
            'upcoming' => $upcoming->sortBy('starts_at'),
            'past' => $past,
        ]);
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        Gate::authorize('cancel', $appointment);

        $appointment->update(['status' => AppointmentStatus::Cancelled]);

        return back()->with('status', 'Tu cita se ha cancelado.');
    }
}
