<x-app-layout>
    <x-slot name="title">Mis citas</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-stone-900">Mis citas</h1>
                <p class="mt-1 text-sm text-stone-500">Hola, {{ Auth::user()->name }}. Aquí tienes tus reservas.</p>
            </div>
            <a href="{{ route('booking.create') }}" class="rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">Nueva reserva</a>
        </div>
    </x-slot>

    <section>
        <h2 class="text-lg font-semibold text-stone-900">Próximas</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @forelse ($upcoming as $appointment)
                <x-card class="flex gap-5">
                    <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-lg bg-rose-50 text-rose-800">
                        <span class="text-xs uppercase">{{ $appointment->starts_at->translatedFormat('M') }}</span>
                        <span class="font-display text-2xl font-bold leading-none">{{ $appointment->starts_at->format('j') }}</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-semibold text-stone-900">{{ $appointment->service->name }}</h3>
                            <x-status-badge :status="$appointment->status" />
                        </div>
                        <p class="mt-1 text-sm text-stone-600">
                            {{ ucfirst($appointment->starts_at->translatedFormat('l')) }}, {{ $appointment->starts_at->format('H:i') }}–{{ $appointment->ends_at->format('H:i') }}
                            · con {{ $appointment->stylist->name }}
                        </p>
                        <p class="mt-1 text-sm font-medium text-stone-900">{{ number_format((float) $appointment->price, 2, ',', '.') }} €</p>

                        @can('cancel', $appointment)
                            <form method="POST" action="{{ route('appointments.cancel', $appointment) }}" class="mt-3"
                                  onsubmit="return confirm('¿Seguro que quieres cancelar esta cita?')">
                                @csrf
                                @method('PATCH')
                                <button class="text-sm font-medium text-red-600 hover:text-red-800">Cancelar cita</button>
                            </form>
                        @else
                            <p class="mt-3 text-xs text-stone-500">Para cancelar con menos de {{ \App\Models\Appointment::CANCELLATION_NOTICE_HOURS }} horas, llámanos.</p>
                        @endcan
                    </div>
                </x-card>
            @empty
                <x-card class="md:col-span-2 text-center">
                    <p class="text-stone-600">No tienes citas próximas.</p>
                    <a href="{{ route('booking.create') }}" class="mt-2 inline-block font-semibold text-rose-700 hover:text-rose-900">Reservar ahora &rarr;</a>
                </x-card>
            @endforelse
        </div>
    </section>

    @if ($past->isNotEmpty())
        <section class="mt-12">
            <h2 class="text-lg font-semibold text-stone-900">Historial</h2>
            <div class="mt-4 overflow-hidden rounded-xl bg-white ring-1 ring-stone-200">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs uppercase tracking-wider text-stone-500">
                        <tr>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Servicio</th>
                            <th class="hidden px-4 py-3 sm:table-cell">Estilista</th>
                            <th class="px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($past as $appointment)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3">{{ $appointment->starts_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $appointment->service->name }}</td>
                                <td class="hidden px-4 py-3 sm:table-cell">{{ $appointment->stylist->name }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$appointment->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-app-layout>
