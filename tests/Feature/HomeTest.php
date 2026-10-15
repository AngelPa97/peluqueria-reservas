<?php

use App\Models\Service;

it('la portada muestra los servicios activos', function () {
    $active = Service::factory()->create(['name' => 'Corte de mujer']);
    $hidden = Service::factory()->inactive()->create(['name' => 'Servicio retirado']);

    $this->get('/')
        ->assertOk()
        ->assertSee($active->name)
        ->assertDontSee($hidden->name);
});
