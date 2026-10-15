<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Stylist;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'services' => Service::active()->orderBy('price')->get(),
            'stylists' => Stylist::active()->with('services')->get(),
        ]);
    }
}
