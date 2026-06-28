<?php

namespace App\Http\Middleware;

use App\Support\UserContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireBranchSelection
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs(
            'change-branch',
            'change-branch.submit',
            'change-branch.locations',
            'change-branch.branches',
            'logout',
            'login',
            'subscription.expired'
        )) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();

            if ($request->has('branch_id')) {
                $requested = trim((string) $request->query('branch_id'));
                $companyId = UserContext::sessionCompanyId($user);

                if ($requested !== '' && strtolower($requested) !== 'all' && strtolower($requested) !== 'null' && $companyId) {
                    $branchId = (int) $requested;

                    if (UserContext::userHasBranch($user, $branchId, $companyId)) {
                        session(['branch_id' => $branchId]);
                    }
                }
            }

            if (!UserContext::hasSelectedContext($user)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select your company and branch first.',
                        'redirect' => route('change-branch'),
                    ], 403);
                }

                return redirect()->route('change-branch');
            }

            UserContext::applyToConfig($user);
        }

        return $next($request);
    }
}
