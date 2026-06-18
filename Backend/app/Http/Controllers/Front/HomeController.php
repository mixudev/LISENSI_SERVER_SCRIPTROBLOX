<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $products = Product::active()->orderBy('name')->get();

        $stats = [
            'active_licenses' => License::where('status', 'active')->count(),
            'total_users' => User::where('role', 'user')->count(),
            'total_products' => Product::active()->count(),
            'uptime' => '99.9%',
        ];

        return view('front.home', compact('products', 'stats'));
    }
}
