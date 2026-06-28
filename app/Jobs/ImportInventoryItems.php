<?php

namespace App\Jobs;

use App\Models\Inventory\ImportBatch;
use App\Models\Inventory\Item;
use App\Services\InventoryOpeningBalanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportInventoryItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $categoryId;
    protected $itemType;
    protected $companyId;
    protected $userId;
    protected $batchId;
    protected $branchId;
    protected $locationId;

    public $timeout = 3600;
    public $tries = 3;
    public $backoff = [60, 120, 300];

    public function __construct(
        $filePath,
        $categoryId,
        $itemType,
        $companyId,
        $userId,
        $batchId = null,
        $branchId = null,
        $locationId = null
    ) {
        $this->filePath = $filePath;
        $this->categoryId = $categoryId;
        $this->itemType = $itemType;
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->batchId = $batchId;
        $this->branchId = $branchId;
        $this->locationId = $locationId;
    }

    public function handle(): void
    {
        $batch = null;

        try {
            Log::info('Starting import job', [
                'file' => $this->filePath,
                'batch_id' => $this->batchId,
            ]);

            if ($this->batchId) {
                $batch = ImportBatch::find($this->batchId);
                if ($batch) {
                    $batch->markAsProcessing();
                }
            }

            if (!file_exists($this->filePath)) {
                throw new \Exception("CSV file not found: {$this->filePath}");
            }

            $csvData = array_map('str_getcsv', file($this->filePath));
            if (empty($csvData)) {
                throw new \Exception('CSV file is empty');
            }

            $header = array_map('trim', array_shift($csvData));

            $requiredColumns = ['name', 'code', 'unit_price'];
            $missingColumns = array_diff($requiredColumns, $header);

            if (!empty($missingColumns)) {
                throw new \Exception('Missing required columns: ' . implode(', ', $missingColumns));
            }

            $openingBalanceService = new InventoryOpeningBalanceService();
            $imported = 0;
            $errors = [];

            foreach ($csvData as $rowIndex => $row) {
                if (count($row) !== count($header)) {
                    $errors[] = 'Row ' . ($rowIndex + 2) . ': Column count mismatch';
                    continue;
                }

                $data = array_combine($header, $row);

                if (empty(trim($data['name'] ?? '')) || empty(trim($data['code'] ?? ''))) {
                    continue;
                }

                try {
                    $hasWholesaleCol = array_key_exists('has_wholesale', $data);
                    $wholesalePriceCol = array_key_exists('wholesale_unit_price', $data);

                    $wantsWholesale = false;
                    if ($hasWholesaleCol) {
                        $rawFlag = strtolower(trim((string) ($data['has_wholesale'] ?? '')));
                        $wantsWholesale = $rawFlag !== '' && in_array($rawFlag, ['yes', 'true', '1', 'y'], true);
                    }

                    $wholesaleUnitPrice = null;
                    if ($wantsWholesale) {
                        $rawWs = $wholesalePriceCol ? trim((string) ($data['wholesale_unit_price'] ?? '')) : '';
                        if ($rawWs === '' || !is_numeric($rawWs) || (float) $rawWs <= 0) {
                            throw new \Exception('wholesale_unit_price is required and must be greater than 0 when has_wholesale is Yes');
                        }
                        $wholesaleUnitPrice = (float) $rawWs;
                    }

                    $costPrice = isset($data['cost_price']) && is_numeric($data['cost_price'])
                        ? (float) $data['cost_price']
                        : 0;

                    $openingBalanceQuantity = 0;
                    if (array_key_exists('opening_balance_quantity', $data)) {
                        $rawQty = trim((string) ($data['opening_balance_quantity'] ?? ''));
                        if ($rawQty !== '') {
                            if (!is_numeric($rawQty) || (float) $rawQty <= 0) {
                                throw new \Exception('opening_balance_quantity must be a number greater than 0 when provided');
                            }
                            $openingBalanceQuantity = (float) $rawQty;
                        }
                    }

                    if ($openingBalanceQuantity > 0) {
                        if ($this->itemType !== 'product') {
                            throw new \Exception('opening_balance_quantity is only allowed for product imports');
                        }
                        if ($costPrice <= 0) {
                            throw new \Exception('cost_price is required and must be greater than 0 when opening_balance_quantity is provided');
                        }
                        if (!$this->branchId || !$this->locationId) {
                            throw new \Exception('Branch and location must be selected before importing items with opening_balance_quantity');
                        }
                    }

                    $item = Item::create([
                        'company_id' => $this->companyId,
                        'category_id' => $this->categoryId,
                        'name' => trim($data['name']),
                        'code' => trim($data['code']),
                        'description' => isset($data['description']) ? trim($data['description']) : null,
                        'item_type' => $this->itemType,
                        'unit_of_measure' => isset($data['unit_of_measure']) ? trim($data['unit_of_measure']) : null,
                        'cost_price' => $costPrice ?: null,
                        'unit_price' => is_numeric($data['unit_price']) ? $data['unit_price'] : 0,
                        'has_wholesale' => $wantsWholesale,
                        'wholesale_unit_price' => $wholesaleUnitPrice,
                        'minimum_stock' => isset($data['minimum_stock']) && is_numeric($data['minimum_stock']) ? $data['minimum_stock'] : null,
                        'maximum_stock' => isset($data['maximum_stock']) && is_numeric($data['maximum_stock']) ? $data['maximum_stock'] : null,
                        'reorder_level' => isset($data['reorder_level']) && is_numeric($data['reorder_level']) ? $data['reorder_level'] : null,
                        'is_active' => true,
                        'track_stock' => $this->itemType === 'product',
                        'track_expiry' => isset($data['track_expiry'])
                            ? in_array(strtolower(trim($data['track_expiry'])), ['yes', 'true', '1'], true)
                            : false,
                        'has_opening_balance' => false,
                        'opening_balance_quantity' => 0,
                        'opening_balance_value' => 0,
                    ]);

                    if ($openingBalanceQuantity > 0) {
                        $openingBalanceService->postForItem(
                            $item,
                            $openingBalanceQuantity,
                            $costPrice,
                            $this->locationId,
                            $this->branchId,
                            $this->companyId,
                            $this->userId,
                            'inventory item import'
                        );
                    }

                    $imported++;
                } catch (\Exception $e) {
                    Log::error('Error processing row ' . ($rowIndex + 2), [
                        'error' => $e->getMessage(),
                    ]);
                    $errors[] = 'Row ' . ($rowIndex + 2) . ': ' . $e->getMessage();
                }
            }

            $message = "Successfully imported {$imported} inventory items.";
            if (!empty($errors)) {
                $message .= ' ' . count($errors) . ' errors occurred.';
                Log::warning($message, ['errors' => $errors]);
            } else {
                Log::info($message);
            }

            if ($batch) {
                $batch->markAsCompleted($imported, count($errors), json_encode($errors));
            }

            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        } catch (\Exception $e) {
            Log::error('Inventory items import failed: ' . $e->getMessage(), [
                'file' => $this->filePath,
                'company_id' => $this->companyId,
                'user_id' => $this->userId,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($batch) {
                $batch->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Inventory items import job failed after retries: ' . $exception->getMessage(), [
            'file' => $this->filePath,
            'company_id' => $this->companyId,
            'user_id' => $this->userId,
        ]);

        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->markAsFailed($exception->getMessage());
            }
        }

        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }
}
