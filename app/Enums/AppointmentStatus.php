<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Confirmed => 'Confirmada',
            self::Completed => 'Completada',
            self::Cancelled => 'Cancelada',
        };
    }

    /**
     * Clases de Tailwind para la etiqueta de estado.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Confirmed => 'bg-emerald-100 text-emerald-800',
            self::Completed => 'bg-sky-100 text-sky-800',
            self::Cancelled => 'bg-gray-200 text-gray-600',
        };
    }

    /**
     * Estados que ocupan hueco en la agenda.
     *
     * @return array<int, string>
     */
    public static function blocking(): array
    {
        return [self::Pending->value, self::Confirmed->value, self::Completed->value];
    }
}
