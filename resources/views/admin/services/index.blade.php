<x-app-layout>
    <x-slot name="title">Servicios</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-bold text-stone-900">Servicios</h1>
            <a href="{{ route('admin.services.create') }}" class="rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">Nuevo servicio</a>
        </div>
    </x-slot>

    <div class="overflow-x-auto rounded-xl bg-white ring-1 ring-stone-200">
        <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50 text-left text-xs uppercase tracking-wider text-stone-500">
                <tr>
                    <th class="px-4 py-3">Servicio</th>
                    <th class="px-4 py-3">Duración</th>
                    <th class="px-4 py-3 text-right">Precio</th>
                    <th class="px-4 py-3">Estilistas</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($services as $service)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="font-medium text-stone-900">{{ $service->name }}</span>
                            <span class="block max-w-md truncate text-stone-500">{{ $service->description }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $service->duration_minutes }} min</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">{{ $service->formattedPrice() }}</td>
                        <td class="px-4 py-3">{{ $service->stylists_count }}</td>
                        <td class="px-4 py-3">
                            <span @class(['rounded-full px-2.5 py-0.5 text-xs font-medium', 'bg-emerald-100 text-emerald-800' => $service->is_active, 'bg-stone-200 text-stone-600' => ! $service->is_active])>
                                {{ $service->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.services.edit', $service) }}" class="font-medium text-rose-700 hover:text-rose-900">Editar</a>
                            <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="ms-3 inline" onsubmit="return confirm('¿Eliminar este servicio?')">
                                @csrf
                                @method('DELETE')
                                <button class="font-medium text-stone-500 hover:text-red-700">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-stone-500">Aún no hay servicios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
