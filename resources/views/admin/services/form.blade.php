<x-app-layout>
    <x-slot name="title">{{ $service->exists ? 'Editar servicio' : 'Nuevo servicio' }}</x-slot>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-stone-900">{{ $service->exists ? 'Editar servicio' : 'Nuevo servicio' }}</h1>
    </x-slot>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" class="space-y-6">
            @csrf
            @if ($service->exists)
                @method('PUT')
            @endif

            <div>
                <x-input-label for="name" value="Nombre" />
                <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $service->name)" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Descripción" />
                <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-stone-300 shadow-sm focus:border-rose-500 focus:ring-rose-500">{{ old('description', $service->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="duration_minutes" value="Duración (minutos)" />
                    <x-text-input id="duration_minutes" name="duration_minutes" type="number" min="5" max="480" step="5" class="mt-1 block w-full" :value="old('duration_minutes', $service->duration_minutes)" required />
                    <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="price" value="Precio (€)" />
                    <x-text-input id="price" name="price" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('price', $service->price)" required />
                    <x-input-error :messages="$errors->get('price')" class="mt-2" />
                </div>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-rose-700 focus:ring-rose-500" @checked(old('is_active', $service->is_active))>
                <span class="text-sm text-stone-700">Visible para reservar</span>
            </label>

            <div class="flex items-center gap-4">
                <x-primary-button>Guardar</x-primary-button>
                <a href="{{ route('admin.services.index') }}" class="text-sm text-stone-600 hover:text-stone-900">Cancelar</a>
            </div>
        </form>
    </x-card>
</x-app-layout>
