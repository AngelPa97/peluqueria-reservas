<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Stylist;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaController extends Controller
{
    /** Rango horario que se dibuja en la agenda diaria. */
    public const OPENING_HOUR = 9;

    public const CLOSING_HOUR = 21;

    public function __invoke(Request $request): View
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);

        $date = $request->filled('date')
            ? CarbonImmutable::createFromFormat('Y-m-d', $request->input('date'))->startOfDay()
            : CarbonImmutable::today();

        $stylists = Stylist::active()
            ->with(['workingHours' => fn ($q) => $q->where('day_of_week', $date->dayOfWeekIso)])
            ->orderBy('name')
            ->get();

        $appointments = Appointment::blocking()
            ->whereDate('starts_at', $date)
            ->with(['user', 'service'])
            ->orderBy('starts_at')
            ->get()
            ->groupBy('stylist_id');

        return view('admin.agenda', [
            'date' => $date,
            'stylists' => $stylists,
            'appointments' => $appointments,
            'openingHour' => self::OPENING_HOUR,
            'closingHour' => self::CLOSING_HOUR,
        ]);
    }
}
