<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = CarbonImmutable::today();
        $monthStart = $today->startOfMonth();

        $todayAppointments = Appointment::blocking()
            ->whereDate('starts_at', $today)
            ->with(['user', 'service', 'stylist'])
            ->orderBy('starts_at')
            ->get();

        $monthRevenue = Appointment::where('status', AppointmentStatus::Completed)
            ->whereBetween('starts_at', [$monthStart, $today->endOfMonth()])
            ->sum('price');

        $topServices = Service::withCount(['appointments' => fn ($q) => $q
            ->blocking()
            ->where('starts_at', '>=', $monthStart->subMonths(2))])
            ->orderByDesc('appointments_count')
            ->take(5)
            ->get();

        // Citas por día durante los próximos 7 días para el gráfico de barras.
        $week = collect(range(0, 6))->map(function (int $offset) use ($today) {
            $day = $today->addDays($offset);

            return [
                'label' => $day->translatedFormat('D j'),
                'count' => Appointment::blocking()->whereDate('starts_at', $day)->count(),
            ];
        });

        return view('admin.dashboard', [
            'todayAppointments' => $todayAppointments,
            'pendingCount' => Appointment::where('status', AppointmentStatus::Pending)->upcoming()->count(),
            'upcomingCount' => Appointment::blocking()->upcoming()->count(),
            'monthRevenue' => $monthRevenue,
            'topServices' => $topServices,
            'week' => $week,
            'weekMax' => max(1, $week->max('count')),
        ]);
    }
}
