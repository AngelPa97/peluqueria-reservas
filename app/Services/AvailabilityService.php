<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Stylist;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Calcula los huecos libres de un estilista para un servicio en un día concreto.
 *
 * Un hueco es válido si:
 *  - cae dentro de alguna franja del horario laboral del estilista ese día,
 *  - el servicio completo cabe antes de que termine la franja,
 *  - no se solapa con otra cita activa (pendiente, confirmada o completada),
 *  - no está en el pasado.
 */
class AvailabilityService
{
    /** Separación en minutos entre posibles horas de inicio. */
    public const SLOT_INTERVAL = 15;

    /** Días hacia delante en los que se permite reservar. */
    public const BOOKING_WINDOW_DAYS = 60;

    /**
     * @return Collection<int, CarbonImmutable>
     */
    public function slotsFor(Stylist $stylist, Service $service, CarbonImmutable $date): Collection
    {
        $day = $date->startOfDay();

        if (! $this->isBookableDate($day) || ! $this->stylistOffers($stylist, $service)) {
            return collect();
        }

        $blocks = $stylist->workingHours()->where('day_of_week', $day->dayOfWeekIso)->get();

        $busy = Appointment::query()
            ->blocking()
            ->where('stylist_id', $stylist->id)
            ->where('starts_at', '<', $day->endOfDay())
            ->where('ends_at', '>', $day)
            ->get(['starts_at', 'ends_at']);

        $now = CarbonImmutable::now();
        $slots = collect();

        foreach ($blocks as $block) {
            $cursor = $day->setTimeFromTimeString($block->start_time);
            $blockEnd = $day->setTimeFromTimeString($block->end_time);

            while ($cursor->addMinutes($service->duration_minutes)->lte($blockEnd)) {
                $end = $cursor->addMinutes($service->duration_minutes);

                $overlaps = $busy->contains(
                    fn (Appointment $a) => $a->starts_at->lt($end) && $a->ends_at->gt($cursor)
                );

                if (! $overlaps && $cursor->gt($now)) {
                    $slots->push($cursor);
                }

                $cursor = $cursor->addMinutes(self::SLOT_INTERVAL);
            }
        }

        return $slots->sort()->values();
    }

    public function isAvailable(Stylist $stylist, Service $service, CarbonImmutable $start): bool
    {
        return $this->slotsFor($stylist, $service, $start)
            ->contains(fn (CarbonImmutable $slot) => $slot->equalTo($start));
    }

    public function isBookableDate(CarbonImmutable $date): bool
    {
        $today = CarbonImmutable::today();

        return $date->betweenIncluded($today, $today->addDays(self::BOOKING_WINDOW_DAYS));
    }

    private function stylistOffers(Stylist $stylist, Service $service): bool
    {
        return $stylist->is_active
            && $service->is_active
            && $stylist->services()->whereKey($service->id)->exists();
    }
}
