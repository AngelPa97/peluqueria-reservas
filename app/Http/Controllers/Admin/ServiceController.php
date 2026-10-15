<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::withCount('stylists')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.form', ['service' => new Service(['is_active' => true])]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        Service::create($request->validated());

        return redirect()->route('admin.services.index')->with('status', 'Servicio creado.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', ['service' => $service]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('admin.services.index')->with('status', 'Servicio actualizado.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if ($service->appointments()->exists()) {
            $service->update(['is_active' => false]);

            return back()->with('status', 'El servicio tiene citas asociadas, así que se ha desactivado en lugar de borrarse.');
        }

        $service->delete();

        return back()->with('status', 'Servicio eliminado.');
    }
}
