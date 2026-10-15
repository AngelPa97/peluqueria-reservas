<x-app-layout>
    <x-slot name="title">Panel</x-slot>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-stone-900">Panel de administración</h1>
        <p class="mt-1 text-sm text-stone-500">{{ ucfirst(now()->translatedFormat('l, j \d\e F \d\e Y')) }}</p>
    </x-slot>

    <!-- KPIs -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Citas hoy', $todayAppointments->count(), route('admin.agenda')],
            ['Pendientes de confirmar', $pendingCount, route('admin.appointments.index', ['status' => 'pending'])],
            ['Próximas citas', $upcomingCount, route('admin.appointments.index', ['from' => today()->toDateString()])],
            ['Facturado este mes', number_format((float) $monthRevenue, 2, ',', '.').' €', null],
        ] as [$label, $value, $link])
            <x-card>
                <p class="text-sm text-stone-500">{{ $label }}</p>
                <p class="mt-2 font-display text-3xl font-bold text-stone-900">{{ $value }}</p>
                @if ($link)
                    <a href="{{ $link }}" class="mt-2 inline-block text-xs font-semibold text-rose-700 hover:text-rose-900">Ver &rarr;</a>
                @endif
            </x-card>
        @endforeach
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-3">
        <!-- Hoy -->
        <x-card class="lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-stone-900">Citas de hoy</h2>
                <a href="{{ route('admin.agenda') }}" class="text-sm font-semibold text-rose-700 hover:text-rose-900">Abrir agenda</a>
            </div>
            <ul class="mt-4 divide-y divide-stone-100">
                @forelse ($todayAppointments as $appointment)
                    <li class="flex items-center gap-4 py-3">
                        <span class="w-24 shrink-0 font-mono text-sm text-stone-500">{{ $appointment->starts_at->format('H:i') }}–{{ $appointment->ends_at->format('H:i') }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium text-stone-900">{{ $appointment->user->name }}</p>
                            <p class="truncate text-sm text-stone-500">{{ $appointment->service->name }} · {{ $appointment->stylist->name }}</p>
                        </div>
                        <x-status-badge :status="$appointment->status" />
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-stone-500">No hay citas para hoy.</li>
                @endforelse
            </ul>
        </x-card>

        <div class="space-y-8">
            <!-- Próximos 7 días -->
            <x-card>
                <h2 class="font-semibold text-stone-900">Próximos 7 días</h2>
                <div class="mt-6 flex h-40 items-end gap-2">
                    @foreach ($week as $day)
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <span class="text-xs font-medium text-stone-700">{{ $day['count'] }}</span>
                            <div class="w-full rounded-t bg-rose-600" style="height: {{ max(4, round($day['count'] / $weekMax * 110)) }}px"></div>
                            <span class="text-[11px] text-stone-500">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <!-- Servicios más pedidos -->
            <x-card>
                <h2 class="font-semibold text-stone-900">Servicios más pedidos</h2>
                <p class="text-xs text-stone-500">Últimos meses</p>
                <ol class="mt-4 space-y-2 text-sm">
                    @foreach ($topServices as $service)
                        <li class="flex justify-between">
                            <span>{{ $service->name }}</span>
                            <span class="font-semibold text-stone-900">{{ $service->appointments_count }}</span>
                        </li>
                    @endforeach
                </ol>
            </x-card>
        </div>
    </div>
</x-app-layout>
