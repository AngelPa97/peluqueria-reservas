<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StylistRequest;
use App\Models\Service;
use App\Models\Stylist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StylistController extends Controller
{
    public function index(): View
    {
        return view('admin.stylists.index', [
            'stylists' => Stylist::with(['services', 'workingHours'])->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Stylist(['is_active' => true]));
    }

    public function store(StylistRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $stylist = Stylist::create($request->safe()->only(['name', 'specialty', 'bio', 'is_active']));
            $this->syncRelations($stylist, $request);
        });

        return redirect()->route('admin.stylists.index')->with('status', 'Estilista creado.');
    }

    public function edit(Stylist $stylist): View
    {
        return $this->form($stylist->load(['services', 'workingHours']));
    }

    public function update(StylistRequest $request, Stylist $stylist): RedirectResponse
    {
        DB::transaction(function () use ($request, $stylist) {
            $stylist->update($request->safe()->only(['name', 'specialty', 'bio', 'is_active']));
            $this->syncRelations($stylist, $request);
        });

        return redirect()->route('admin.stylists.index')->with('status', 'Estilista actualizado.');
    }

    public function destroy(Stylist $stylist): RedirectResponse
    {
        if ($stylist->appointments()->exists()) {
            $stylist->update(['is_active' => false]);

            return back()->with('status', 'El estilista tiene citas asociadas, así que se ha desactivado en lugar de borrarse.');
        }

        $stylist->delete();

        return back()->with('status', 'Estilista eliminado.');
    }

    private function form(Stylist $stylist): View
    {
        return view('admin.stylists.form', [
            'stylist' => $stylist,
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    private function syncRelations(Stylist $stylist, StylistRequest $request): void
    {
        $stylist->services()->sync($request->validated('services', []));

        $stylist->workingHours()->delete();
        $stylist->workingHours()->createMany($request->validated('hours', []));
    }
}
