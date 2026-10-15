@php
    $hours = old('hours', $stylist->workingHours->map(fn ($h) => [
        'day_of_week' => $h->day_of_week,
        'start_time' => substr($h->start_time, 0, 5),
        'end_time' => substr($h->end_time, 0, 5),
    ])->all());
    $selectedServices = array_map('intval', old('services', $stylist->services->pluck('id')->all()));
@endphp

<x-app-layout>
    <x-slot name="title">{{ $stylist->exists ? 'Editar estilista' : 'Nuevo estilista' }}</x-slot>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-stone-900">{{ $stylist->exists ? 'Editar estilista' : 'Nuevo estilista' }}</h1>
    </x-slot>

    <form method="POST" action="{{ $stylist->exists ? route('admin.stylists.update', $stylist) : route('admin.stylists.store') }}"
          class="grid gap-8 lg:grid-cols-2">
        @csrf
        @if ($stylist->exists)
            @method('PUT')
        @endif

        <x-card class="space-y-6">
            <h2 class="font-semibold text-stone-900">Datos</h2>

            <div>
                <x-input-label for="name" value="Nombre" />
                <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $stylist->name)" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="specialty" value="Especialidad" />
                <x-text-input id="specialty" name="specialty" class="mt-1 block w-full" :value="old('specialty', $stylist->specialty)" />
                <x-input-error :messages="$errors->get('specialty')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="bio" value="Biografía" />
                <textarea id="bio" name="bio" rows="3" class="mt-1 block w-full rounded-md border-stone-300 shadow-sm focus:border-rose-500 focus:ring-rose-500">{{ old('bio', $stylist->bio) }}</textarea>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-rose-700 focus:ring-rose-500" @checked(old('is_active', $stylist->is_active))>
                <span class="text-sm text-stone-700">Acepta reservas</span>
            </label>

            <fieldset>
                <legend class="text-sm font-medium text-stone-700">Servicios que realiza</legend>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    @foreach ($services as $service)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="services[]" value="{{ $service->id }}" class="rounded border-stone-300 text-rose-700 focus:ring-rose-500" @checked(in_array($service->id, $selectedServices, true))>
                            {{ $service->name }}
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('services.*')" class="mt-2" />
            </fieldset>
        </x-card>

        <div x-data="{ rows: @js(array_values($hours)) }" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-stone-900">Horario semanal</h2>
                <button type="button" @click="rows.push({ day_of_week: 1, start_time: '09:00', end_time: '14:00' })"
                        class="text-sm font-semibold text-rose-700 hover:text-rose-900">+ Añadir franja</button>
            </div>
            <p class="mt-1 text-sm text-stone-500">Puedes añadir varias franjas por día (por ejemplo, mañana y tarde).</p>
            <x-input-error :messages="array_merge($errors->get('hours'), $errors->get('hours.*'))" class="mt-2" />

            <div class="mt-4 space-y-2">
                <template x-for="(row, i) in rows" :key="i">
                    <div class="flex items-center gap-2">
                        <select :name="`hours[${i}][day_of_week]`" x-model.number="row.day_of_week"
                                class="flex-1 rounded-md border-stone-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500">
                            @foreach (\App\Models\WorkingHour::DAYS as $num => $day)
                                <option value="{{ $num }}">{{ $day }}</option>
                            @endforeach
                        </select>
                        <input type="time" :name="`hours[${i}][start_time]`" x-model="row.start_time" step="900" aria-label="Inicio"
                               class="rounded-md border-stone-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500">
                        <span class="text-stone-400">–</span>
                        <input type="time" :name="`hours[${i}][end_time]`" x-model="row.end_time" step="900" aria-label="Fin"
                               class="rounded-md border-stone-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500">
                        <button type="button" @click="rows.splice(i, 1)" class="px-2 text-stone-400 hover:text-red-600" aria-label="Quitar franja">&times;</button>
                    </div>
                </template>
                <p x-show="rows.length === 0" class="text-sm text-stone-500">Sin franjas: este estilista no aparecerá con huecos libres.</p>
            </div>
        </div>

        <div class="flex items-center gap-4 lg:col-span-2">
            <x-primary-button>Guardar</x-primary-button>
            <a href="{{ route('admin.stylists.index') }}" class="text-sm text-stone-600 hover:text-stone-900">Cancelar</a>
        </div>
    </form>
</x-app-layout>
