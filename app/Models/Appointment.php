<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'stylist_id', 'service_id', 'starts_at', 'ends_at', 'status', 'price', 'notes'])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    /**
     * Horas mínimas de antelación para que un cliente pueda cancelar.
     */
    public const CANCELLATION_NOTICE_HOURS = 2;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => AppointmentStatus::class,
            'price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stylist(): BelongsTo
    {
        return $this->belongsTo(Stylist::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Citas que ocupan hueco en la agenda (todas menos las canceladas).
     */
    public function scopeBlocking(Builder $query): void
    {
        $query->whereIn('status', AppointmentStatus::blocking());
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('starts_at', '>=', now());
    }

    public function canBeCancelledByClient(): bool
    {
        return in_array($this->status, [AppointmentStatus::Pending, AppointmentStatus::Confirmed], true)
            && $this->starts_at->gt(now()->addHours(self::CANCELLATION_NOTICE_HOURS));
    }
}
