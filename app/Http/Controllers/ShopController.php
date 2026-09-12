<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        if (!request()->user()->hasPermission('shops.view')) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $user = request()->user();
        $companyCode = $user->company_code;

        $query = Shop::where('company_code', $companyCode)->with('deliveryRoute');

        // Filter for Sales Reps: only show shops in their assigned routes
        if ($user->role && $user->role->level === 'sales_rep') {
            $assignedRouteIds = $user->routes()->pluck('delivery_routes.id');
            $query->whereIn('delivery_route_id', $assignedRouteIds);
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('route_id')) {
            $query->where('delivery_route_id', $request->input('route_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $shops = $query->get();
        $routes = \App\Models\DeliveryRoute::where('company_code', $companyCode)->where('is_active', true)->get(['id', 'name']);

        return Inertia::render('delivery/shops/index', [
            'shops' => $shops,
            'filters' => $request->only(['search', 'route_id', 'status']),
            'routes' => $routes
        ]);
    }

    public function create()
    {
        if (!request()->user()->hasPermission('shops.create')) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $companyCode = request()->user()->company_code;
        $routes = \App\Models\DeliveryRoute::where('company_code', $companyCode)->where('is_active', true)->get(['id', 'name']);

        return Inertia::render('delivery/shops/create', ['routes' => $routes]);
    }

    public function store(Request $request)
    {
        if (!request()->user()->hasPermission('shops.create')) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_phone' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'external_customer_id' => 'nullable|exists:customers,id',
            'delivery_route_id' => 'required|exists:delivery_routes,id',
        ]);

        // If a route is provided, ensure it belongs to the same company
        if ($request->filled('delivery_route_id')) {
            $route = \App\Models\DeliveryRoute::find($request->delivery_route_id);
            if (!$route || $route->company_code !== request()->user()->company_code) {
                return back()->withErrors(['delivery_route_id' => 'Selected route is invalid for your company.'])->withInput();
            }
        }

        Shop::create([
            'name' => $request->name,
            'address' => $request->address,
            'contact_phone' => $request->contact_phone,
            'external_customer_id' => $request->external_customer_id,
            'delivery_route_id' => $request->delivery_route_id,
            'company_code' => request()->user()->company_code,
        ]);

        return redirect()->route('delivery.shops.index')->with('success', 'Shop created.');
    }

    public function edit(Shop $shop)
    {
        if (!request()->user()->hasPermission('shops.edit')) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        if ($shop->company_code !== request()->user()->company_code) abort(404);

        $companyCode = request()->user()->company_code;
        $routes = \App\Models\DeliveryRoute::where('company_code', $companyCode)->where('is_active', true)->get(['id', 'name']);

        return Inertia::render('delivery/shops/edit', ['shop' => $shop, 'routes' => $routes]);
    }

    public function update(Request $request, Shop $shop)
    {
        if (!request()->user()->hasPermission('shops.edit')) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        if ($shop->company_code !== request()->user()->company_code) abort(404);

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_phone' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'external_customer_id' => 'nullable|exists:customers,id',
            'delivery_route_id' => 'required|exists:delivery_routes,id',
        ]);

        if ($request->filled('delivery_route_id')) {
            $route = \App\Models\DeliveryRoute::find($request->delivery_route_id);
            if (!$route || $route->company_code !== request()->user()->company_code) {
                return back()->withErrors(['delivery_route_id' => 'Selected route is invalid for your company.'])->withInput();
            }
        }

        $shop->update($request->only(['name', 'address', 'contact_phone', 'external_customer_id', 'delivery_route_id', 'is_active']));

        return redirect()->route('delivery.shops.index')->with('success', 'Shop updated.');
    }

    public function destroy(Shop $shop)
    {
        if (!request()->user()->hasPermission('shops.delete')) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        if ($shop->company_code !== request()->user()->company_code) abort(404);

        $shop->delete();

        return redirect()->route('delivery.shops.index')->with('success', 'Shop deleted.');
    }

    /**
     * Toggle the active state of a shop.
     */
    public function updateStatus(Request $request, Shop $shop)
    {
        if (!request()->user()->hasPermission('shops.edit')) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        if ($shop->company_code !== request()->user()->company_code) abort(404);

        $shop->update(['is_active' => !$shop->is_active]);

        return redirect()->back()->with('success', 'Shop status updated.');
    }
}
