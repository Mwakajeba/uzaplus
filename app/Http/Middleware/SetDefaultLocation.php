<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetDefaultLocation
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('change-branch', 'change-branch.submit', 'change-branch.locations', 'change-branch.branches')) {
            return $next($request);
        }

        if (Auth::check()
            && session('company_id')
            && session('branch_id')
            && (!session('location_id') || session('location_id') === '')) {
            $user = Auth::user();
            $branchId = (int) session('branch_id');

            $defaultLocation = $user->locations()
                ->where('inventory_locations.branch_id', $branchId)
                ->where('inventory_locations.company_id', session('company_id'))
                ->wherePivot('is_default', true)
                ->value('inventory_locations.id');

            if (!$defaultLocation) {
                $defaultLocation = $user->locations()
                    ->where('inventory_locations.branch_id', $branchId)
                    ->where('inventory_locations.company_id', session('company_id'))
                    ->orderBy('inventory_locations.name')
                    ->value('inventory_locations.id');
            }

            if ($defaultLocation) {
                session(['location_id' => $defaultLocation]);
            }
        }

        return $next($request);
    }
}
