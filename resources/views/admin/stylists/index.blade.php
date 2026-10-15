<x-app-layout>
    <x-slot name="title">Estilistas</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-bold text-stone-900">Estilistas</h1>
            <a href="{{ route('admin.stylists.create') }}" class="rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">Nuevo estilista</a>
        </div>
    </x-slot>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($stylists as $stylist)
            <x-card class="flex flex-col">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 font-display font-bold text-rose-800">{{ $stylist->initials() }}</span>
                    <div class="flex-1">
                        <h2 class="font-semibold text-stone-900">{{ $stylist->name }}</h2>
                        <p class="text-sm text-rose-700">{{ $stylist->specialty }}</p>
                    </div>
                    @unless ($stylist->is_active)
                        <span class="rounded-full bg-stone-200 px-2.5 py-0.5 text-xs font-medium text-stone-600">Inactivo</span>
                    @endunless
                </div>

                <div class="mt-4 flex flex-wrap gap-1">
                    @foreach ($stylist->services as $service)
                        <span class="rounded bg-stone-100 px-2 py-0.5 text-xs text-stone-700">{{ $service->name }}</span>
                    @endforeach
                </div>

                <dl class="mt-4 flex-1 space-y-1 text-sm">
                    @forelse ($stylist->workingHours->groupBy('day_of_week') as $day => $blocks)
                        <div class="flex gap-2">
                            <dt class="w-24 text-stone-500">{{ \App\Models\WorkingHour::DAYS[$day] }}</dt>
                            <dd>{{ $blocks->map(fn ($b) => substr($b->start_time, 0, 5).'–'.substr($b->end_time, 0, 5))->implode(', ') }}</dd>
                        </div>
                    @empty
                        <p class="text-stone-500">Sin horario asignado.</p>
                    @endforelse
                </dl>

                <div class="mt-4 flex gap-4 border-t border-stone-100 pt-4 text-sm">
                    <a href="{{ route('admin.stylists.edit', $stylist) }}" class="font-medium text-rose-700 hover:text-rose-900">Editar</a>
                    <form method="POST" action="{{ route('admin.stylists.destroy', $stylist) }}" onsubmit="return confirm('¿Eliminar este estilista?')">
                        @csrf
                        @method('DELETE')
                        <button class="font-medium text-stone-500 hover:text-red-700">Eliminar</button>
                    </form>
                </div>
            </x-card>
        @empty
            <p class="text-stone-500">Aún no hay estilistas.</p>
        @endforelse
    </div>
</x-app-layout>
