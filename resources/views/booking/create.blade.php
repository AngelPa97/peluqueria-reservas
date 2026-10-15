@php
    $catalog = $services->map(fn ($s) => [
        'id' => $s->id,
        'name' => $s->name,
        'duration' => $s->duration_minutes,
        'price' => $s->formattedPrice(),
        'stylists' => $s->stylists->map(fn ($st) => ['id' => $st->id, 'name' => $st->name, 'specialty' => $st->specialty])->values(),
    ])->values();
@endphp

<x-app-layout>
    <x-slot name="title">Reservar cita</x-slot>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-stone-900">Reservar cita</h1>
        <p class="mt-1 text-sm text-stone-500">Completa los pasos y confirma tu reserva.</p>
    </x-slot>

    <form method="POST" action="{{ route('booking.store') }}"
          x-data="booking({
              catalog: @js($catalog),
              serviceId: @js((int) old('service_id', $selectedService)),
              stylistId: @js((int) old('stylist_id')),
              date: @js(old('date', $minDate)),
              time: @js(old('time')),
              url: @js(route('booking.availability')),
          })"
          class="grid gap-8 lg:grid-cols-3">
        @csrf

        <div class="space-y-8 lg:col-span-2">
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Paso 1 -->
            <x-card>
                <h2 class="font-semibold text-stone-900"><span class="text-rose-700">1.</span> Elige un servicio</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <template x-for="service in catalog" :key="service.id">
                        <label class="flex cursor-pointer items-center justify-between rounded-lg border p-4 transition"
                               :class="serviceId === service.id ? 'border-rose-600 bg-rose-50 ring-1 ring-rose-600' : 'border-stone-200 hover:border-stone-300'">
                            <input type="radio" name="service_id" class="sr-only" :value="service.id"
                                   :checked="serviceId === service.id" @change="selectService(service.id)">
                            <span>
                                <span class="block font-medium text-stone-900" x-text="service.name"></span>
                                <span class="block text-sm text-stone-500" x-text="service.duration + ' min'"></span>
                            </span>
                            <span class="font-semibold text-rose-700" x-text="service.price"></span>
                        </label>
                    </template>
                </div>
            </x-card>

            <!-- Paso 2 -->
            <x-card x-show="service" x-cloak>
                <h2 class="font-semibold text-stone-900"><span class="text-rose-700">2.</span> ¿Con quién?</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <template x-for="stylist in service?.stylists ?? []" :key="stylist.id">
                        <label class="cursor-pointer rounded-lg border p-4 text-center transition"
                               :class="stylistId === stylist.id ? 'border-rose-600 bg-rose-50 ring-1 ring-rose-600' : 'border-stone-200 hover:border-stone-300'">
                            <input type="radio" name="stylist_id" class="sr-only" :value="stylist.id"
                                   :checked="stylistId === stylist.id" @change="stylistId = stylist.id; loadSlots()">
                            <span class="block font-medium text-stone-900" x-text="stylist.name"></span>
                            <span class="block text-xs text-stone-500" x-text="stylist.specialty"></span>
                        </label>
                    </template>
                </div>
            </x-card>

            <!-- Paso 3 -->
            <x-card x-show="stylistId" x-cloak>
                <h2 class="font-semibold text-stone-900"><span class="text-rose-700">3.</span> Día y hora</h2>
                <div class="mt-4 max-w-xs">
                    <x-input-label for="date" value="Fecha" />
                    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full"
                                  min="{{ $minDate }}" max="{{ $maxDate }}"
                                  x-model="date" @change="loadSlots()" />
                </div>

                <div class="mt-6">
                    <p x-show="loading" class="text-sm text-stone-500">Buscando huecos libres…</p>
                    <p x-show="!loading && slots.length === 0" class="text-sm text-stone-500">
                        No quedan huecos libres este día. Prueba con otra fecha u otro estilista.
                    </p>
                    <div x-show="!loading && slots.length" class="grid grid-cols-4 gap-2 sm:grid-cols-6">
                        <template x-for="slot in slots" :key="slot">
                            <button type="button" @click="time = slot"
                                    class="rounded-md py-2 text-sm font-medium transition"
                                    :class="time === slot ? 'bg-rose-700 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200'"
                                    x-text="slot"></button>
                        </template>
                    </div>
                    <input type="hidden" name="time" :value="time">
                </div>
            </x-card>
        </div>

        <!-- Resumen -->
        <aside class="lg:sticky lg:top-8 lg:self-start">
            <x-card>
                <h2 class="font-semibold text-stone-900">Resumen</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-stone-500">Servicio</dt><dd class="text-right font-medium" x-text="service?.name ?? '—'"></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-stone-500">Estilista</dt><dd class="text-right font-medium" x-text="stylist?.name ?? '—'"></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-stone-500">Fecha</dt><dd class="text-right font-medium" x-text="prettyDate"></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-stone-500">Hora</dt><dd class="text-right font-medium" x-text="time ?? '—'"></dd></div>
                    <div class="flex justify-between gap-4 border-t border-stone-200 pt-3"><dt class="text-stone-500">Precio</dt><dd class="font-display text-lg font-bold text-rose-700" x-text="service?.price ?? '—'"></dd></div>
                </dl>

                <div class="mt-6">
                    <x-input-label for="notes" value="Notas para el estilista (opcional)" />
                    <textarea id="notes" name="notes" rows="3" maxlength="500"
                              class="mt-1 block w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                              placeholder="Ej.: quiero mantener el largo">{{ old('notes') }}</textarea>
                </div>

                <x-primary-button class="mt-6 w-full" x-bind:disabled="!time">Confirmar reserva</x-primary-button>
                <p class="mt-3 text-xs text-stone-500">Puedes cancelar sin coste hasta {{ \App\Models\Appointment::CANCELLATION_NOTICE_HOURS }} horas antes.</p>
            </x-card>
        </aside>
    </form>
</x-app-layout>
