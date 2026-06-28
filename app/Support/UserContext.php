<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\User;

class UserContext
{
    public static function assignedCompanyIds(User $user): array
    {
        if ($user->relationLoaded('companies')) {
            $ids = $user->companies->pluck('id')->map(fn ($id) => (int) $id)->all();
        } else {
            $ids = $user->companies()->pluck('companies.id')->map(fn ($id) => (int) $id)->all();
        }

        if (!empty($ids)) {
            return array_values(array_unique($ids));
        }

        return $user->company_id ? [(int) $user->company_id] : [];
    }

    public static function assignedBranchIds(User $user, ?int $companyId = null): array
    {
        $query = $user->branches();

        if ($companyId) {
            $query->where('branches.company_id', $companyId);
        }

        return $query->pluck('branches.id')->map(fn ($id) => (int) $id)->all();
    }

    public static function userHasCompany(User $user, int $companyId): bool
    {
        return in_array($companyId, self::assignedCompanyIds($user), true);
    }

    public static function userHasBranch(User $user, int $branchId, ?int $companyId = null): bool
    {
        $query = $user->branches()->where('branches.id', $branchId);

        if ($companyId) {
            $query->where('branches.company_id', $companyId);
        }

        return $query->exists();
    }

    public static function defaultCompanyId(User $user): ?int
    {
        $default = $user->companies()->wherePivot('is_default', true)->value('companies.id');

        if ($default) {
            return (int) $default;
        }

        $assigned = self::assignedCompanyIds($user);

        return $assigned[0] ?? ($user->company_id ? (int) $user->company_id : null);
    }

    public static function sessionCompanyId(User $user): ?int
    {
        $sessionId = session('company_id');

        if (!$sessionId) {
            return null;
        }

        $companyId = (int) $sessionId;

        return self::userHasCompany($user, $companyId) ? $companyId : null;
    }

    public static function sessionBranchId(User $user, ?int $companyId): ?int
    {
        if (!$companyId) {
            return null;
        }

        $sessionBranch = session('branch_id');

        if (!$sessionBranch) {
            return null;
        }

        $branchId = (int) $sessionBranch;

        return self::userHasBranch($user, $branchId, $companyId) ? $branchId : null;
    }

    public static function hasSelectedContext(User $user): bool
    {
        $companyId = self::sessionCompanyId($user);

        return $companyId && self::sessionBranchId($user, $companyId);
    }

    public static function applyToConfig(User $user): void
    {
        $companyId = self::sessionCompanyId($user);
        $branchId = $companyId ? self::sessionBranchId($user, $companyId) : null;

        config([
            'app.current_company_id' => $companyId,
            'app.current_branch_id' => $branchId,
        ]);
    }

    public static function setSession(int $companyId, int $branchId, ?int $locationId = null): void
    {
        session([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        if ($locationId) {
            session(['location_id' => $locationId]);
        } else {
            session()->forget('location_id');
        }
    }

    public static function clearSession(): void
    {
        session()->forget(['company_id', 'branch_id', 'location_id']);
        config([
            'app.current_company_id' => null,
            'app.current_branch_id' => null,
        ]);
    }
}
