@php
    $pxPerMinute = 1.2;
    $toPx = fn ($time) => (($time->hour - $openingHour) * 60 + $time->minute) * $pxPerMinute;
    $height = ($closingHour - $openingHour) * 60 * $pxPerMinute;
@endphp

<x-app-layout>
    <x-slot name="title">Agenda</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-stone-900">Agenda</h1>
                <p class="mt-1 text-sm text-stone-500">{{ ucfirst($date->translatedFormat('l, j \d\e F \d\e Y')) }}</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <a href="{{ route('admin.agenda', ['date' => $date->subDay()->toDateString()]) }}" class="rounded-md px-3 py-2 text-stone-600 ring-1 ring-stone-300 hover:bg-stone-100" aria-label="Día anterior">&larr;</a>
                <x-text-input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()" />
                <a href="{{ route('admin.agenda', ['date' => $date->addDay()->toDateString()]) }}" class="rounded-md px-3 py-2 text-stone-600 ring-1 ring-stone-300 hover:bg-stone-100" aria-label="Día siguiente">&rarr;</a>
                <a href="{{ route('admin.agenda') }}" class="rounded-md px-3 py-2 text-sm font-medium text-stone-600 ring-1 ring-stone-300 hover:bg-stone-100">Hoy</a>
            </form>
        </div>
    </x-slot>

    <div class="overflow-x-auto rounded-xl bg-white ring-1 ring-stone-200">
        <div class="flex min-w-[720px]">
            <!-- Columna de horas -->
            <div class="w-16 shrink-0 border-r border-stone-200">
                <div class="h-12 border-b border-stone-200"></div>
                <div class="relative" style="height: {{ $height }}px">
                    @for ($h = $openingHour; $h < $closingHour; $h++)
                        <span class="absolute right-2 -translate-y-1/2 text-xs text-stone-400" style="top: {{ ($h - $openingHour) * 60 * $pxPerMinute }}px">{{ sprintf('%02d:00', $h) }}</span>
                    @endfor
                </div>
            </div>

            @forelse ($stylists as $stylist)
                <div class="min-w-[200px] flex-1 border-r border-stone-200 last:border-r-0">
                    <div class="flex h-12 items-center gap-2 border-b border-stone-200 px-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-rose-100 text-xs font-bold text-rose-800">{{ $stylist->initials() }}</span>
                        <span class="truncate text-sm font-semibold text-stone-900">{{ $stylist->name }}</span>
                    </div>
                    <div class="relative bg-[repeating-linear-gradient(135deg,#f5f5f4_0,#f5f5f4_6px,#fafaf9_6px,#fafaf9_12px)]" style="height: {{ $height }}px">
                        {{-- Franjas de trabajo en blanco; el rayado indica que no trabaja --}}
                        @foreach ($stylist->workingHours as $block)
                            @php
                                $start = $date->setTimeFromTimeString($block->start_time);
                                $end = $date->setTimeFromTimeString($block->end_time);
                            @endphp
                            <div class="absolute inset-x-0 bg-white" style="top: {{ $toPx($start) }}px; height: {{ $toPx($end) - $toPx($start) }}px"></div>
                        @endforeach

                        @for ($h = $openingHour + 1; $h < $closingHour; $h++)
                            <div class="absolute inset-x-0 border-t border-dashed border-stone-200" style="top: {{ ($h - $openingHour) * 60 * $pxPerMinute }}px"></div>
                        @endfor

                        @foreach ($appointments->get($stylist->id, collect()) as $appointment)
                            <div class="absolute inset-x-1 overflow-hidden rounded-md border-l-4 px-2 py-1 text-xs shadow-sm
                                        {{ $appointment->status === \App\Enums\AppointmentStatus::Pending ? 'border-amber-500 bg-amber-50' : 'border-rose-600 bg-rose-50' }}"
                                 style="top: {{ $toPx($appointment->starts_at) }}px; height: {{ max(18, $toPx($appointment->ends_at) - $toPx($appointment->starts_at) - 2) }}px"
                                 title="{{ $appointment->user->name }} · {{ $appointment->service->name }} · {{ $appointment->status->label() }}">
                                <p class="font-semibold text-stone-900">{{ $appointment->starts_at->format('H:i') }} {{ $appointment->user->name }}</p>
                                <p class="truncate text-stone-600">{{ $appointment->service->name }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="p-6 text-sm text-stone-500">No hay estilistas activos.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-4 flex gap-6 text-xs text-stone-500">
        <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-sm border-l-4 border-amber-500 bg-amber-50"></span> Pendiente</span>
        <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-sm border-l-4 border-rose-600 bg-rose-50"></span> Confirmada / completada</span>
        <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-sm bg-stone-200"></span> Fuera de horario</span>
    </div>
</x-app-layout>
