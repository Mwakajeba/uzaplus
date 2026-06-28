<?php

/**
 * INVENTORY ITEMS CLEANUP SCRIPT
 *
 * Deletes all inventory items and related records.
 *
 * DELETES:
 * - GL entries for inventory movements (opening balance, adjustments, write-offs)
 * - Inventory movements, stock levels, cost layers, expiry tracking
 * - Opening balances, branch/location prices, import batches
 * - Count entries, variances, adjustments
 * - Transfer/store requisition line items
 * - Production batches, item batches, work order BOM & material issues
 * - Transaction line items that block deletion (purchase invoice lines, GRN lines, cash purchase lines)
 * - Cascade line items (sales orders, deliveries, cash sales, purchase orders/quotations, etc.)
 * - Activity logs for inventory items
 * - All inventory_items rows
 *
 * PRESERVES:
 * - Inventory categories and locations
 * - Parent documents (invoices, orders) — item links are cleared or line rows removed where required
 *
 * USAGE: php cleanup_inventory_items.php
 *
 * WARNING: This operation cannot be undone!
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "==============================================\n";
echo "  INVENTORY ITEMS CLEANUP UTILITY\n";
echo "==============================================\n\n";

$itemCount = DB::table('inventory_items')->count();

if ($itemCount === 0) {
    echo "No inventory items found. Nothing to delete.\n\n";
    exit(0);
}

echo "Found {$itemCount} inventory item(s) to delete.\n\n";

$tablesToCount = [
    'inventory_movements',
    'inventory_stock_levels',
    'inventory_cost_layers',
    'inventory_expiry_tracking',
    'inventory_opening_balances',
    'inventory_item_prices',
    'inventory_item_location_prices',
    'inventory_count_entries',
    'inventory_count_variances',
    'inventory_count_adjustments',
    'inventory_import_batches',
    'item_batch',
    'production_batches',
    'work_order_bom',
    'material_issues',
    'purchase_invoice_items',
    'goods_receipt_items',
    'cash_purchase_items',
    'activity_logs',
];

echo "Records before cleanup:\n";
foreach ($tablesToCount as $table) {
    if (!DB::getSchemaBuilder()->hasTable($table)) {
        continue;
    }
    $count = DB::table($table)->count();
    echo "  - {$table}: {$count}\n";
}
echo "  - inventory_items: {$itemCount}\n\n";

echo "Deleting...\n";

$deleted = [];

DB::transaction(function () use (&$deleted) {
    $itemIds = DB::table('inventory_items')->pluck('id');

    $movementIds = DB::table('inventory_movements')
        ->whereIn('item_id', $itemIds)
        ->pluck('id');

    if ($movementIds->isNotEmpty()) {
        $deleted['gl_transactions'] = DB::table('gl_transactions')
            ->whereIn('transaction_id', $movementIds)
            ->whereIn('transaction_type', [
                'opening_balance',
                'inventory_adjustment',
                'inventory_write_off',
            ])
            ->delete();
    } else {
        $deleted['gl_transactions'] = 0;
    }

    if (DB::getSchemaBuilder()->hasTable('item_batch')) {
        $deleted['item_batch'] = DB::table('item_batch')->whereIn('item_id', $itemIds)->delete();
    }

    if (DB::getSchemaBuilder()->hasTable('production_batches')) {
        $deleted['production_batches'] = DB::table('production_batches')->whereIn('item_id', $itemIds)->delete();
    }

    if (DB::getSchemaBuilder()->hasTable('work_order_bom')) {
        $deleted['work_order_bom'] = DB::table('work_order_bom')->whereIn('material_item_id', $itemIds)->delete();
    }

    if (DB::getSchemaBuilder()->hasTable('material_issues')) {
        $deleted['material_issues'] = DB::table('material_issues')->whereIn('material_item_id', $itemIds)->delete();
    }

    if (DB::getSchemaBuilder()->hasTable('purchase_invoice_items')) {
        $deleted['purchase_invoice_items'] = DB::table('purchase_invoice_items')
            ->whereIn('inventory_item_id', $itemIds)
            ->delete();
    }

    if (DB::getSchemaBuilder()->hasTable('goods_receipt_items')) {
        $deleted['goods_receipt_items'] = DB::table('goods_receipt_items')
            ->whereIn('inventory_item_id', $itemIds)
            ->delete();
    }

    if (DB::getSchemaBuilder()->hasTable('cash_purchase_items')) {
        $deleted['cash_purchase_items'] = DB::table('cash_purchase_items')
            ->whereIn('inventory_item_id', $itemIds)
            ->delete();
    }

    if (DB::getSchemaBuilder()->hasTable('inventory_opening_balances')) {
        $deleted['inventory_opening_balances'] = DB::table('inventory_opening_balances')
            ->whereIn('item_id', $itemIds)
            ->delete();
    }

    if (DB::getSchemaBuilder()->hasTable('inventory_import_batches')) {
        $deleted['inventory_import_batches'] = DB::table('inventory_import_batches')->delete();
    }

    if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
        $deleted['activity_logs'] = DB::table('activity_logs')
            ->where('model', 'Item')
            ->whereIn('model_id', $itemIds)
            ->delete();
    }

    $deleted['inventory_items'] = DB::table('inventory_items')->delete();
});

echo "\nDeleted records:\n";
foreach ($deleted as $table => $count) {
    echo "  - {$table}: {$count}\n";
}

$remaining = DB::table('inventory_items')->count();
echo "\nRemaining inventory items: {$remaining}\n";
echo $remaining === 0 ? "✓ Cleanup completed successfully.\n\n" : "✗ Some items may still remain — check foreign key constraints.\n\n";
