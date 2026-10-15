<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function cancel(User $user, Appointment $appointment): bool
    {
        return $appointment->user_id === $user->id && $appointment->canBeCancelledByClient();
    }
}
