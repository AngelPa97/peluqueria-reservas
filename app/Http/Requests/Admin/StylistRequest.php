<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        // Las filas de horario vacías del formulario se descartan.
        $hours = collect($this->input('hours', []))
            ->filter(fn ($row) => filled($row['start_time'] ?? null) || filled($row['end_time'] ?? null))
            ->values()
            ->all();

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'hours' => $hours,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'specialty' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'services' => ['array'],
            'services.*' => ['integer', 'exists:services,id'],
            'hours' => ['array'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:1,7'],
            'hours.*.start_time' => ['required', 'date_format:H:i'],
            'hours.*.end_time' => ['required', 'date_format:H:i', 'after:hours.*.start_time'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $byDay = collect($this->input('hours', []))->groupBy('day_of_week');

                foreach ($byDay as $rows) {
                    $sorted = $rows->sortBy('start_time')->values();

                    for ($i = 1; $i < $sorted->count(); $i++) {
                        if ($sorted[$i]['start_time'] < $sorted[$i - 1]['end_time']) {
                            $validator->errors()->add('hours', 'Hay franjas horarias que se solapan en el mismo día.');

                            return;
                        }
                    }
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'specialty' => 'especialidad',
            'hours.*.start_time' => 'hora de inicio',
            'hours.*.end_time' => 'hora de fin',
        ];
    }
}
