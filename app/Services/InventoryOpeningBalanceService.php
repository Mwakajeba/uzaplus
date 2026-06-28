<?php

namespace App\Services;

use App\Models\GlTransaction;
use App\Models\Inventory\Item;
use App\Models\Inventory\Movement;
use App\Models\Inventory\OpeningBalance;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;

class InventoryOpeningBalanceService
{
    public function postForItem(
        Item $item,
        float $quantity,
        float $unitCost,
        ?int $locationId = null,
        ?int $branchId = null,
        ?int $companyId = null,
        ?int $userId = null,
        string $source = 'item form'
    ): void {
        $loginLocationId = (int) ($locationId ?? session('location_id'));
        $branchId = (int) ($branchId ?? session('branch_id') ?? Auth::user()?->branch_id);
        $companyId = (int) ($companyId ?? current_company_id() ?? Auth::user()?->company_id);
        $userId = (int) ($userId ?? Auth::id());
        $movementDate = now()->toDateString();
        $reference = 'Opening Balance - ' . $item->code;
        $totalCost = $quantity * $unitCost;

        if (!$loginLocationId || !$branchId) {
            throw new \RuntimeException('Branch and location are required to post opening balance.');
        }

        if ($quantity <= 0) {
            throw new \RuntimeException('Opening balance quantity must be greater than zero.');
        }

        if ($unitCost <= 0) {
            throw new \RuntimeException('Cost price must be greater than zero to post opening balance.');
        }

        $alreadyExists = OpeningBalance::where('company_id', $companyId)
            ->where('inventory_location_id', $loginLocationId)
            ->where('item_id', $item->id)
            ->exists();

        if ($alreadyExists) {
            throw new \RuntimeException('Opening balance already exists for this item at the current location.');
        }

        $stockService = new InventoryStockService();
        $currentStock = $stockService->getItemStockAtLocation($item->id, $loginLocationId);
        $newStock = $currentStock + $quantity;

        $movement = Movement::create([
            'branch_id' => $branchId,
            'location_id' => $loginLocationId,
            'item_id' => $item->id,
            'user_id' => $userId,
            'movement_type' => 'opening_balance',
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'reference' => $reference,
            'reason' => 'Opening balance on item creation',
            'notes' => 'Opening balance recorded from ' . $source,
            'movement_date' => $movementDate,
            'balance_before' => $currentStock,
            'balance_after' => $newStock,
        ]);

        $inventoryAccountId = SystemSetting::where('key', 'inventory_default_inventory_account')->value('value');
        $openingBalanceAccountId = SystemSetting::where('key', 'inventory_default_opening_balance_account')->value('value');

        if ($inventoryAccountId && $openingBalanceAccountId) {
            GlTransaction::create([
                'chart_account_id' => $inventoryAccountId,
                'amount' => $totalCost,
                'nature' => 'debit',
                'transaction_id' => $movement->id,
                'transaction_type' => 'opening_balance',
                'date' => $movementDate,
                'description' => 'Opening balance - ' . $item->name,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);

            GlTransaction::create([
                'chart_account_id' => $openingBalanceAccountId,
                'amount' => $totalCost,
                'nature' => 'credit',
                'transaction_id' => $movement->id,
                'transaction_type' => 'opening_balance',
                'date' => $movementDate,
                'description' => 'Opening balance - ' . $item->name,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);
        }

        OpeningBalance::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'inventory_location_id' => $loginLocationId,
            'item_id' => $item->id,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'reference' => $reference,
            'notes' => 'Recorded from ' . $source,
            'opened_at' => $movementDate,
            'user_id' => $userId,
        ]);

        $costService = new InventoryCostService();
        $costService->addInventory(
            $item->id,
            $quantity,
            $unitCost,
            'opening_balance',
            $reference,
            $movementDate
        );

        $item->update([
            'has_opening_balance' => true,
            'opening_balance_quantity' => $quantity,
            'opening_balance_value' => $totalCost,
        ]);
    }
}
