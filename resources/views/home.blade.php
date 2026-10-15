<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} · Reserva tu cita online</title>
    <meta name="description" content="Reserva tu cita en la peluquería en menos de un minuto: elige servicio, estilista y hora.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-stone-50 text-stone-800">
    <header class="absolute inset-x-0 top-0 z-10">
        <div class="max-w-7xl mx-auto flex items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
            <x-brand />
            <nav class="flex items-center gap-3 text-sm font-medium">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-md bg-stone-900 px-4 py-2 text-white hover:bg-stone-700">Mi cuenta</a>
                @else
                    <a href="{{ route('login') }}" class="whitespace-nowrap px-3 py-2 text-stone-700 hover:text-stone-900">Iniciar sesión</a>
                    <a href="{{ route('register') }}" class="hidden whitespace-nowrap rounded-md bg-stone-900 px-4 py-2 text-white hover:bg-stone-700 sm:inline-block">Crear cuenta</a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-rose-50 via-stone-50 to-amber-50 pt-32 pb-24">
        <div class="max-w-7xl mx-auto grid items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <p class="mb-4 inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-rose-800">Reserva online 24/7</p>
                <h1 class="font-display text-4xl font-bold leading-tight text-stone-900 sm:text-5xl">
                    Tu próximo corte,<br>a un par de clics.
                </h1>
                <p class="mt-6 max-w-lg text-lg text-stone-600">
                    Elige el servicio, tu estilista favorito y la hora que mejor te venga. Sin llamadas ni esperas: ves los huecos libres en tiempo real.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('booking.create') }}" class="rounded-md bg-rose-700 px-6 py-3 font-semibold text-white shadow-sm hover:bg-rose-800">Reservar cita</a>
                    <a href="#servicios" class="rounded-md px-6 py-3 font-semibold text-stone-700 ring-1 ring-stone-300 hover:bg-white">Ver servicios y precios</a>
                </div>
            </div>

            <div class="hidden lg:block">
                <div class="relative mx-auto max-w-md rounded-2xl bg-white p-6 shadow-xl ring-1 ring-stone-200">
                    <p class="text-sm font-medium text-stone-500">Huecos libres hoy</p>
                    <div class="mt-4 grid grid-cols-4 gap-2 text-center text-sm">
                        @foreach (['10:00', '10:15', '11:30', '12:00', '16:00', '16:45', '17:30', '18:15'] as $i => $hour)
                            <span @class([
                                'rounded-md py-2 font-medium',
                                'bg-rose-700 text-white' => $i === 2,
                                'bg-stone-100 text-stone-700' => $i !== 2,
                            ])>{{ $hour }}</span>
                        @endforeach
                    </div>
                    <div class="mt-6 rounded-lg bg-stone-50 p-4 text-sm">
                        <p class="font-semibold text-stone-900">Corte de mujer · 45 min</p>
                        <p class="text-stone-500">con Marta Ruiz · 22,00 €</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Servicios -->
    <section id="servicios" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl font-bold text-stone-900">Servicios</h2>
            <p class="mt-2 text-stone-600">Precios cerrados y duración aproximada de cada servicio.</p>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($services as $service)
                    <x-card class="flex flex-col">
                        <div class="flex items-start justify-between gap-4">
                            <h3 class="font-semibold text-stone-900">{{ $service->name }}</h3>
                            <span class="whitespace-nowrap font-display text-lg font-bold text-rose-700">{{ $service->formattedPrice() }}</span>
                        </div>
                        <p class="mt-2 flex-1 text-sm text-stone-600">{{ $service->description }}</p>
                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span class="text-stone-500">{{ $service->duration_minutes }} min</span>
                            <a href="{{ route('booking.create', ['service' => $service->id]) }}" class="font-semibold text-rose-700 hover:text-rose-900">Reservar &rarr;</a>
                        </div>
                    </x-card>
                @empty
                    <p class="text-stone-500">Todavía no hay servicios publicados.</p>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Equipo -->
    <section class="bg-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl font-bold text-stone-900">Nuestro equipo</h2>
            <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($stylists as $stylist)
                    <div class="flex gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-rose-100 font-display text-lg font-bold text-rose-800">{{ $stylist->initials() }}</span>
                        <div>
                            <h3 class="font-semibold text-stone-900">{{ $stylist->name }}</h3>
                            <p class="text-sm font-medium text-rose-700">{{ $stylist->specialty }}</p>
                            <p class="mt-2 text-sm text-stone-600">{{ $stylist->bio }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Cómo funciona -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl font-bold text-stone-900">Cómo funciona</h2>
            <ol class="mt-10 grid gap-6 sm:grid-cols-3">
                @foreach ([
                    ['Elige servicio y estilista', 'Solo verás a los profesionales que realizan ese servicio.'],
                    ['Escoge día y hora', 'El calendario muestra únicamente los huecos realmente libres.'],
                    ['Confirma tu cita', 'Consulta o cancela tus reservas desde "Mis citas".'],
                ] as $i => [$title, $text])
                    <li class="rounded-xl bg-white p-6 ring-1 ring-stone-200">
                        <span class="font-display text-3xl font-bold text-rose-700">{{ $i + 1 }}</span>
                        <h3 class="mt-2 font-semibold text-stone-900">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-stone-600">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <footer class="border-t border-stone-200 py-8 text-center text-sm text-stone-500">
        <p>Tijera &amp; Peine · Calle Mayor 12, Madrid · Lunes a viernes 9:30–14:00 y 16:00–20:00, sábados 9:00–14:00</p>
        <p class="mt-1">Proyecto de portfolio desarrollado con Laravel.</p>
    </footer>
</body>
</html>
