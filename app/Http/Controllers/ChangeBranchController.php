<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\InventoryLocation;
use App\Support\UserContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeBranchController extends Controller
{
    public function show()
    {
        $user = Auth::user()->load(['companies', 'branches']);

        $companies = $user->companies()->orderBy('name')->get();

        if ($companies->isEmpty() && $user->company_id) {
            $companies = \App\Models\Company::where('id', $user->company_id)->get();
        }

        if ($companies->isEmpty()) {
            return view('auth.change-branch', [
                'companies' => collect(),
                'branches' => collect(),
                'locations' => collect(),
                'selectedCompanyId' => null,
            ])->with('error', 'No company assigned to your account. Contact administrator.');
        }

        $selectedCompanyId = (int) (old('company_id', session('company_id') ?: UserContext::defaultCompanyId($user)));
        if (!UserContext::userHasCompany($user, $selectedCompanyId)) {
            $selectedCompanyId = (int) $companies->first()->id;
        }

        $assignedBranchIds = UserContext::assignedBranchIds($user, $selectedCompanyId);
        $branches = Branch::whereIn('id', $assignedBranchIds)
            ->where('company_id', $selectedCompanyId)
            ->orderBy('branch_name')
            ->get();

        $locations = $user->locations()
            ->where('inventory_locations.company_id', $selectedCompanyId)
            ->select('inventory_locations.*')
            ->get();

        if ($companies->count() === 1 && $branches->count() === 1 && $locations->count() <= 1) {
            $branchId = (int) $branches->first()->id;
            $locationId = $locations->count() === 1 ? (int) $locations->first()->id : null;
            UserContext::setSession($selectedCompanyId, $branchId, $locationId);

            return redirect()->route('dashboard')->with('success', 'Welcome!');
        }

        return view('auth.change-branch', compact('companies', 'branches', 'locations', 'selectedCompanyId'));
    }

    public function change(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'branch_id' => 'required|integer|exists:branches,id',
            'location_id' => 'nullable|integer',
        ]);

        $companyId = (int) $request->company_id;
        $branchId = (int) $request->branch_id;

        if (!UserContext::userHasCompany($user, $companyId)) {
            return back()->with('error', 'You are not assigned to the selected company.');
        }

        if (!UserContext::userHasBranch($user, $branchId, $companyId)) {
            return back()->with('error', 'You are not assigned to the selected branch for this company.');
        }

        $locationId = null;
        if ($request->filled('location_id')) {
            $locationId = (int) $request->location_id;
            $validLocationId = $user->locations()
                ->where('inventory_locations.company_id', $companyId)
                ->where('inventory_locations.branch_id', $branchId)
                ->where('inventory_locations.id', $locationId)
                ->value('inventory_locations.id');

            if (!$validLocationId) {
                return back()->with('error', 'Invalid location for the selected company and branch.');
            }

            $locationId = (int) $validLocationId;
        } else {
            $locationId = $user->locations()
                ->where('inventory_locations.company_id', $companyId)
                ->where('inventory_locations.branch_id', $branchId)
                ->orderBy('inventory_locations.name')
                ->value('inventory_locations.id');

            $locationId = $locationId ? (int) $locationId : null;
        }

        UserContext::setSession($companyId, $branchId, $locationId);
        UserContext::applyToConfig($user);

        $target = \Route::has('dashboard') ? route('dashboard') : url('/');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Company and branch selected successfully!',
                'redirect_url' => $target,
            ]);
        }

        return redirect()->intended($target)->with('success', 'Company and branch selected successfully!');
    }

    public function branches(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
        ]);

        $companyId = (int) $request->query('company_id');

        if (!UserContext::userHasCompany($user, $companyId)) {
            return response()->json([], 403);
        }

        $assignedBranchIds = UserContext::assignedBranchIds($user, $companyId);

        $branches = Branch::whereIn('id', $assignedBranchIds)
            ->where('company_id', $companyId)
            ->orderBy('branch_name')
            ->get(['id', 'name', 'branch_name', 'company_id'])
            ->map(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->branch_name ?: $branch->name,
                'company_id' => $branch->company_id,
            ]);

        return response()->json($branches);
    }

    public function locations(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'company_id' => 'nullable|integer|exists:companies,id',
        ]);

        $branchId = (int) $request->query('branch_id');
        $companyId = (int) ($request->query('company_id') ?: session('company_id'));

        if ($companyId && !UserContext::userHasCompany($user, $companyId)) {
            return response()->json([], 403);
        }

        if (!UserContext::userHasBranch($user, $branchId, $companyId ?: null)) {
            return response()->json([], 403);
        }

        $query = $user->locations()->where('inventory_locations.branch_id', $branchId);

        if ($companyId) {
            $query->where('inventory_locations.company_id', $companyId);
        }

        $locations = $query
            ->select('inventory_locations.id', 'inventory_locations.name', 'inventory_locations.branch_id')
            ->orderBy('inventory_locations.name')
            ->get();

        return response()->json($locations);
    }
}
