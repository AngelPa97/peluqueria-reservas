<x-app-layout>
    <x-slot name="title">Citas</x-slot>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-stone-900">Citas</h1>
    </x-slot>

    <form method="GET" class="mb-6 grid gap-3 rounded-xl bg-white p-4 ring-1 ring-stone-200 sm:grid-cols-2 lg:grid-cols-[2fr_1.4fr_1.4fr_1fr_1fr_auto]">
        <x-text-input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar cliente…" />
        <select name="status" class="rounded-md border-stone-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500">
            <option value="">Todos los estados</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select name="stylist_id" class="rounded-md border-stone-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500">
            <option value="">Todos los estilistas</option>
            @foreach ($stylists as $stylist)
                <option value="{{ $stylist->id }}" @selected((int) ($filters['stylist_id'] ?? 0) === $stylist->id)>{{ $stylist->name }}</option>
            @endforeach
        </select>
        <x-text-input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="Desde" />
        <x-text-input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="Hasta" />
        <x-primary-button>Filtrar</x-primary-button>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white ring-1 ring-stone-200">
        <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50 text-left text-xs uppercase tracking-wider text-stone-500">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Servicio</th>
                    <th class="px-4 py-3">Estilista</th>
                    <th class="px-4 py-3 text-right">Precio</th>
                    <th class="px-4 py-3">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($appointments as $appointment)
                    <tr class="align-top">
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="font-medium text-stone-900">{{ $appointment->starts_at->format('d/m/Y') }}</span>
                            <span class="block text-stone-500">{{ $appointment->starts_at->format('H:i') }}–{{ $appointment->ends_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-stone-900">{{ $appointment->user->name }}</span>
                            <span class="block text-stone-500">{{ $appointment->user->email }}</span>
                            @if ($appointment->notes)
                                <span class="mt-1 block text-xs italic text-stone-500">“{{ $appointment->notes }}”</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $appointment->service->name }}</td>
                        <td class="px-4 py-3">{{ $appointment->stylist->name }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">{{ number_format((float) $appointment->price, 2, ',', '.') }} €</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" aria-label="Estado"
                                        class="rounded-md border-0 py-1 pl-2 pr-8 text-xs font-medium {{ $appointment->status->badgeClasses() }} focus:ring-2 focus:ring-rose-500">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected($appointment->status === $status)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-stone-500">No hay citas con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $appointments->links() }}</div>
</x-app-layout>
