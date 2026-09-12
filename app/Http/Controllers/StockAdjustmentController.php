<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Product;
use App\Models\Section;
use App\Models\StockInHand;
use App\Models\Sequence;
use App\Models\Vehicle;
use App\Models\VehicleStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\ItemMaster;
use App\Models\Purchase;
use App\Models\PurchaseDet;
use App\Notifications\StockAdjustmentPendingNotification;

class StockAdjustmentController extends Controller
{
    /**
     * Get authenticated user from either web or company guard.
     */
    private function authUser()
    {
        return Auth::user() ?? Auth::guard('company')->user();
    }

    /**
     * Display a listing of stock adjustments.
     */
    public function index()
    {
        $user = $this->authUser();
        if (! $user || ! $user->hasPermission('stock_adjustments.view')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to view stock adjustments.');
        }

        $companyCode = $user->company_code;

        $adjustments = StockAdjustment::with(['section', 'vehicle', 'recorder', 'items.product'])
            ->where('company_code', $companyCode)
            ->orderBy('adjustment_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return Inertia::render('stock-adjustment/Index', [
            'adjustments' => $adjustments,
        ]);
    }

    /**
     * Show the form for creating a new stock adjustment.
     */
    public function create()
    {
        $user = $this->authUser();
        if (! $user || ! $user->hasPermission('stock_adjustments.create')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to create stock adjustments.');
        }

        $companyCode = $user->company_code;
        $userBusinessUnit = (!empty($companyCode) && str_starts_with(strtoupper($companyCode), 'MAL')) ? 'malibo' : 'vismass';
        
        $products = ItemMaster::where(function($query) use ($companyCode, $userBusinessUnit) {
            $query->where('company_code', $companyCode)
                  ->orWhereJsonContains('available_business_units', $userBusinessUnit);
        })
            ->select('ItmKy as id', 'ItmNm as name', 'ItemCode as code', 'Unit as unit', 'item_type')
            ->get();

        $sections = Section::where('company_code', $companyCode)->get();
        
        $suggestedBatch = '';
        if ($sections->count() > 0) {
            $suggestedBatch = \App\Services\NumberGeneratorService::preview('ADJ', $companyCode, $sections[0]->section_code);
        }

        return Inertia::render('stock-adjustment/Create', [
            'products' => $products,
            'sections' => $sections,
            'suggested_batch' => $suggestedBatch,
        ]);
    }

    /**
     * Store a newly created stock adjustment in storage.
     */
    public function store(Request $request)
    {
        $user = $this->authUser();
        if (! $user || ! $user->hasPermission('stock_adjustments.create')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to create stock adjustments.');
        }

        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'batch_no' => 'nullable|string|max:255',
            'adjustment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:itemmaster,ItmKy',
            'items.*.adjustment_type' => 'required|in:addition,subtraction',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.reason' => 'nullable|string|max:255',
            'items.*.batch_no' => 'nullable|string|max:255',
            'items.*.serial_number' => 'nullable|string|max:255',
            'items.*.cost_price' => 'nullable|numeric|min:0',
            'items.*.sale_price' => 'nullable|numeric|min:0',
            'items.*.wholesale_price' => 'nullable|numeric|min:0',
            'items.*.vehicle_sale_price' => 'nullable|numeric|min:0',
            'items.*.current_stock' => 'nullable|numeric',
        ]);

        $companyCode = $user->company_code;

        DB::beginTransaction();
        try {
            // Always generate a fresh sequential batch number to ensure the counter increments correctly
            $section = Section::find($validated['section_id']);
            $sectionCode = $section ? $section->section_code : 'MAIN';
            $headerBatchNo = \App\Services\NumberGeneratorService::generate('ADJ', $companyCode, $sectionCode);

            // 1. Create Header (status is 'pending' - awaiting company admin approval)
            $adjustment = StockAdjustment::create([
                'company_code' => $companyCode,
                'adjustment_number' => Sequence::generateNextNumber('stock_adjustment'),
                'batch_no' => $headerBatchNo,
                'section_id' => $validated['section_id'],
                'vehicle_id' => null,
                'adjustment_date' => $validated['adjustment_date'],
                'notes' => $validated['notes'],
                'status' => 'pending',
                'recorded_by' => Auth::id(),
                'approved_by' => null,
                'total_amount' => 0,
            ]);

            $grandTotal = 0;

            // 2. Create Items (stock NOT updated yet - pending approval)
            foreach ($validated['items'] as $itemData) {
                // Use header batch if item batch is not provided or set to 'NEW'
                if (empty($itemData['batch_no']) || $itemData['batch_no'] === 'NEW') {
                    $itemData['batch_no'] = $headerBatchNo;
                }

                // Calculate line total
                $lineTotal = (float)($itemData['quantity'] * ($itemData['cost_price'] ?? 0));
                $itemData['line_total'] = $lineTotal;
                $grandTotal += $lineTotal;

                $item = new StockAdjustmentItem($itemData);
                $adjustment->items()->save($item);
            }

            // Update header total
            $adjustment->update(['total_amount' => $grandTotal]);

            // 3. Send notification to company admins
            $companyAdmins = \App\Models\User::where('company_code', $companyCode)
                ->whereHas('role', function($q) {
                    $q->where('level', 'company_admin');
                })
                ->get();

            foreach ($companyAdmins as $admin) {
                $admin->notify(new StockAdjustmentPendingNotification($adjustment));
            }

            DB::commit();
            return redirect()->route('stock-adjustments.index')->with('success', 'Stock adjustment submitted for approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing stock adjustment batch: " . $e->getMessage());
            return back()->with('error', 'Failed to process adjustments. ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Helper to update stock records based on adjustment.
     */
    private function processStockUpdate($header, $item, $prices)
    {
        if ($header->vehicle_id) {
            $this->processVehicleStockUpdate($header, $item, $prices);
            return;
        }

        $section = Section::find($header->section_id);
        $sectionCode = $section ? $section->section_code : 'MAIN';

        // Check if printer needs serialization validation
        $product = ItemMaster::find($item->product_id);
        if ($product && $product->item_type === 'printer' && empty($item->serial_number)) {
            throw new \Exception("Serial number is required for printer: " . $product->ItmNm);
        }

        // Addition logic
        if ($item->adjustment_type === 'addition') {
            StockInHand::insert([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'company_code' => $header->company_code,
                'owner_company_code' => $header->company_code,
                'section_code' => $sectionCode,
                'ItemKy' => $item->product_id,
                'batch_no' => $item->batch_no,
                'serial_number' => $item->serial_number,
                'Qty' => $item->quantity,
                'FreeQty' => 0,
                'TrnTyp' => 'ADJ-IN',
                'OrdKy' => $header->id,
                'OrdDate' => $header->adjustment_date,
                'RefNo' => $header->adjustment_number,
                'CounterID' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update item prices if provided
            DB::table('item_price_det')->updateOrInsert(
                [
                    'ItmKy' => $item->product_id,
                    'company_code' => $header->company_code,
                    'batch_no' => $item->batch_no,
                    'serial_number' => $item->serial_number, // Include serial number in matching for printers
                ],
                [
                    'section_code' => $sectionCode,
                    'CosPri' => $prices['cost_price'],
                    'NCostPrice' => $prices['cost_price'],
                    'SlsPri' => $prices['sale_price'],
                    'WholePrice' => $prices['wholesale_price'],
                    'VehicleSalePrice' => $prices['vehicle_sale_price'],
                    'ChangedDate' => now(),
                    'Status' => 'A',
                    'fInAct' => 0,
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'updated_at' => now(),
                    'serial_number' => $item->serial_number,
                ]
            );

            // Special handling for printers to ensure they are searchable in POS
            if ($product && $product->item_type === 'printer' && !empty($item->serial_number)) {
                // Find or create a dummy purchase header for adjustments
                $purchaseNo = 900000 + $header->id; // High number to avoid collision
                
                $purchase = Purchase::where('company_code', $header->company_code)
                    ->where('PurchaseNo', $purchaseNo)
                    ->first();

                if (!$purchase) {
                    $purchase = new Purchase();
                    $purchase->PurchaseKey = (Purchase::max('PurchaseKey') ?? 0) + 1;
                    $purchase->company_code = $header->company_code;
                    $purchase->PurchaseNo = $purchaseNo;
                    $purchase->section_code = $sectionCode;
                    $purchase->GRNDate = $header->adjustment_date;
                    $purchase->type = 'adjustment';
                    $purchase->Status = 'A';
                    $purchase->flnact = false;
                    $purchase->AddUser = 'SYSTEM-ADJ';
                    $purchase->CostTotal = $header->total_amount;
                    $purchase->TotalVal = $header->total_amount;
                    $purchase->save();
                }

                $purchaseKey = $purchase->PurchaseKey;

                // Create/Update PurchaseDet for this item/batch/serial
                $detailQuery = PurchaseDet::where('company_code', $header->company_code)
                    ->where('iTimKy', $item->product_id)
                    ->where('batch_no', $item->batch_no);
                
                if (!empty($item->serial_number)) {
                    $detailQuery->where('serial_number', $item->serial_number);
                } else {
                    $detailQuery->whereNull('serial_number');
                }
                
                $detail = $detailQuery->first();

                if (!$detail) {
                    $detail = new PurchaseDet();
                    $detail->PerchaseDetKy = (PurchaseDet::max('PerchaseDetKy') ?? 0) + 1;
                    $detail->company_code = $header->company_code;
                    $detail->PurchaseKey = $purchaseKey;
                    $detail->serial_number = !empty($item->serial_number) ? $item->serial_number : null;
                    $detail->section_code = $sectionCode;
                    $detail->iTimKy = $item->product_id;
                    $detail->Qty = $item->quantity;
                    $detail->CostPrice = $prices['cost_price'];
                    $detail->SalePrice = $prices['sale_price'];
                    $detail->WholePrice = $prices['wholesale_price'];
                    $detail->VehicleSalePrice = $prices['vehicle_sale_price'];
                    $detail->batch_no = $item->batch_no;
                    $detail->Status = 'A';
                    $detail->flnAct = false;
                    $detail->save();
                } else {
                    $detail->update([
                        'section_code' => $sectionCode,
                        'CostPrice' => $prices['cost_price'],
                        'SalePrice' => $prices['sale_price'],
                        'WholePrice' => $prices['wholesale_price'],
                        'VehicleSalePrice' => $prices['vehicle_sale_price'],
                        'batch_no' => $item->batch_no,
                    ]);
                }
            }
        } else {
            // Subtraction logic
            // Check availability
            $available = StockInHand::where('company_code', $header->company_code)
                ->where('section_code', $sectionCode)
                ->where('ItemKy', $item->product_id)
                ->where('batch_no', $item->batch_no)
                ->when($item->serial_number, function($q) use ($item) {
                    return $q->where('serial_number', $item->serial_number);
                })
                ->sum('Qty');

            if ($available < $item->quantity) {
                throw new \Exception("Insufficient stock for product: " . ($product->ItmNm ?? 'Unknown') . ". Available: $available, Required: {$item->quantity}");
            }

            StockInHand::insert([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'company_code' => $header->company_code,
                'owner_company_code' => $header->company_code,
                'section_code' => $sectionCode,
                'ItemKy' => $item->product_id,
                'batch_no' => $item->batch_no,
                'serial_number' => $item->serial_number,
                'Qty' => -$item->quantity,
                'FreeQty' => 0,
                'TrnTyp' => 'ADJ-OUT',
                'OrdKy' => $header->id,
                'OrdDate' => $header->adjustment_date,
                'RefNo' => $header->adjustment_number,
                'CounterID' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function processVehicleStockUpdate($header, $item, $prices)
    {
        $product = ItemMaster::find($item->product_id);
        
        // Addition logic
        if ($item->adjustment_type === 'addition') {
            StockInHand::insert([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'company_code' => $header->company_code,
                'owner_company_code' => $header->company_code,
                'section_code' => null,
                'vehicle_id' => $header->vehicle_id,
                'ItemKy' => $item->product_id,
                'batch_no' => $item->batch_no,
                'serial_number' => $item->serial_number,
                'Qty' => $item->quantity,
                'FreeQty' => 0,
                'TrnTyp' => 'V-ADJ-IN',
                'OrdKy' => $header->id,
                'OrdDate' => $header->adjustment_date,
                'RefNo' => $header->adjustment_number,
                'CounterID' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $vs = VehicleStock::where('vehicle_id', $header->vehicle_id)
                ->where('item_ky', $item->product_id)
                ->where('batch_no', $item->batch_no)
                ->lockForUpdate()
                ->first();

            if ($vs) {
                $vs->quantity = $vs->quantity + $item->quantity;
                $vs->loaded_quantity = $vs->loaded_quantity + $item->quantity;
                $vs->last_date = now()->toDateString();
                $vs->save();
            } else {
                VehicleStock::create([
                    'vehicle_id' => $header->vehicle_id,
                    'item_ky' => $item->product_id,
                    'batch_no' => $item->batch_no,
                    'serial_number' => $item->serial_number,
                    'quantity' => $item->quantity,
                    'loaded_quantity' => $item->quantity,
                    'delivered_quantity' => 0,
                    'reserved_quantity' => 0,
                    'company_code' => $header->company_code,
                    'last_date' => now()->toDateString(),
                ]);
            }
        } else {
            // Subtraction logic
            $vs = VehicleStock::where('vehicle_id', $header->vehicle_id)
                ->where('item_ky', $item->product_id)
                ->where('batch_no', $item->batch_no)
                ->lockForUpdate()
                ->first();

            $available = $vs ? ((float) $vs->quantity - (float) $vs->reserved_quantity) : 0;

            if ($available < $item->quantity) {
                throw new \Exception("Insufficient vehicle stock for product: " . ($product->ItmNm ?? 'Unknown') . ". Available: $available, Required: {$item->quantity}");
            }

            $vs->quantity = $vs->quantity - $item->quantity;
            $vs->loaded_quantity = max(0, $vs->loaded_quantity - $item->quantity);
            $vs->save();

            StockInHand::insert([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'company_code' => $header->company_code,
                'owner_company_code' => $header->company_code,
                'section_code' => null,
                'vehicle_id' => $header->vehicle_id,
                'ItemKy' => $item->product_id,
                'batch_no' => $item->batch_no,
                'serial_number' => $item->serial_number,
                'Qty' => -$item->quantity,
                'FreeQty' => 0,
                'TrnTyp' => 'V-ADJ-OUT',
                'OrdKy' => $header->id,
                'OrdDate' => $header->adjustment_date,
                'RefNo' => $header->adjustment_number,
                'CounterID' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Show the specified adjustment batch.
     */
    public function show($id)
    {
        $user = $this->authUser();
        if (! $user || ! $user->hasPermission('stock_adjustments.view')) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $adjustment = StockAdjustment::with(['section', 'vehicle', 'recorder', 'approver', 'items.product'])
            ->findOrFail($id);

        return Inertia::render('stock-adjustment/Show', [
            'adjustment' => $adjustment,
        ]);
    }

    /**
     * API to fetch product details for adjustment.
     */
    public function getProductDetails(Request $request)
    {
        $productId = $request->query('product_id');
        $sectionId = $request->query('section_id');
        $vehicleId = $request->query('vehicle_id');
        $user = $this->authUser();
        $companyCode = $user->company_code;

        $product = ItemMaster::where('ItmKy', $productId)->firstOrFail();

        if ($vehicleId) {
            $vehicle = Vehicle::findOrFail($vehicleId);
            $batches = DB::table('vehicle_stocks as vs')
                ->leftJoin('item_price_det as ipd', function($join) use ($companyCode) {
                    $join->on('vs.item_ky', '=', 'ipd.ItmKy')
                         ->on('vs.batch_no', '=', 'ipd.batch_no')
                         ->where('ipd.company_code', '=', $companyCode);
                })
                ->select(
                    'vs.batch_no', 
                    DB::raw('SUM(vs.quantity - vs.reserved_quantity) as total_quantity'),
                    DB::raw('MAX(ipd.CosPri) as cost_price'),
                    DB::raw('MAX(ipd.SlsPri) as sale_price'),
                    DB::raw('MAX(ipd.WholePrice) as wholesale_price'),
                    DB::raw('MAX(ipd.VehicleSalePrice) as vehicle_sale_price')
                )
                ->where('vs.item_ky', $productId)
                ->where('vs.company_code', $companyCode)
                ->where('vs.vehicle_id', $vehicleId)
                ->groupBy('vs.batch_no')
                ->having('total_quantity', '>', 0)
                ->get()
                ->map(function($batch) use ($vehicle) {
                    $batch->section_code = 'VEH';
                    $batch->section_name = $vehicle->name;
                    return $batch;
                });

            $suggestedBatch = \App\Services\NumberGeneratorService::preview('ADJ', $companyCode, 'VEH');
        } else {
            $section = Section::findOrFail($sectionId);

            // Get batches available in this section with their current stock and prices
            $batches = DB::table('stock_in_hand as sih')
                ->leftJoin('item_price_det as ipd', function($join) use ($companyCode) {
                    $join->on('sih.ItemKy', '=', 'ipd.ItmKy')
                         ->on('sih.batch_no', '=', 'ipd.batch_no')
                         ->where('ipd.company_code', '=', $companyCode);
                })
                ->select(
                    'sih.batch_no', 
                    DB::raw('SUM(sih.Qty) as total_quantity'),
                    DB::raw('MAX(ipd.CosPri) as cost_price'),
                    DB::raw('MAX(ipd.SlsPri) as sale_price'),
                    DB::raw('MAX(ipd.WholePrice) as wholesale_price'),
                    DB::raw('MAX(ipd.VehicleSalePrice) as vehicle_sale_price')
                )
                ->where('sih.ItemKy', $productId)
                ->where('sih.company_code', $companyCode)
                ->where('sih.section_code', $section->section_code)
                ->groupBy('sih.batch_no')
                ->having('total_quantity', '>', 0)
                ->get()
                ->map(function($batch) use ($section) {
                    $batch->section_code = $section->section_code;
                    $batch->section_name = $section->name;
                    return $batch;
                });
            
            $suggestedBatch = \App\Services\NumberGeneratorService::preview('ADJ', $companyCode, $section->section_code);
        }

        // Get latest prices
        $prices = DB::table('item_price_det')
            ->where('ItmKy', $productId)
            ->where('company_code', $companyCode)
            ->orderBy('ChangedDate', 'desc')
            ->first();

        // Fallback to product owner's company if prices not found locally (for shared products)
        if (!$prices && !empty($product->company_code) && $product->company_code !== $companyCode) {
            $prices = DB::table('item_price_det')
                ->where('ItmKy', $productId)
                ->where('company_code', $product->company_code)
                ->orderBy('ChangedDate', 'desc')
                ->first();
        }



        return response()->json([
            'product' => [
                'id' => $product->ItmKy,
                'name' => $product->ItmNm,
                'code' => $product->ItemCode,
                'item_type' => $product->item_type,
            ],
            'batches' => $batches,
            'default_prices' => [
                'cost_price' => $prices->CosPri ?? 0,
                'sale_price' => $prices->SlsPri ?? 0,
                'wholesale_price' => $prices->WholePrice ?? 0,
                'vehicle_sale_price' => $prices->VehicleSalePrice ?? 0,
            ],
            'suggested_batch' => $suggestedBatch,
        ]);
    }

    public function getNextBatchNumber(Request $request)
    {
        $sectionId = $request->query('section_id');
        $vehicleId = $request->query('vehicle_id');
        $user = $this->authUser();
        $companyCode = $user->company_code;

        if ($vehicleId) {
            $batchNo = \App\Services\NumberGeneratorService::preview('ADJ', $companyCode, 'VEH');
        } else if ($sectionId) {
            $section = Section::findOrFail($sectionId);
            $batchNo = \App\Services\NumberGeneratorService::preview('ADJ', $companyCode, $section->section_code);
        } else {
            $batchNo = \App\Services\NumberGeneratorService::preview('ADJ', $companyCode, 'MAIN');
        }

        return response()->json(['batch_no' => $batchNo]);
    }

    /**
     * Approve a pending stock adjustment.
     */
    public function approve(Request $request, StockAdjustment $stock_adjustment)
    {
        $user = $this->authUser();
        if (! $user || ! $user->hasPermission('stock_adjustments.approve')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have approval permission.');
        }

        if ($stock_adjustment->status !== 'pending') {
            return redirect()->back()->with('error', 'This adjustment cannot be approved.');
        }

        // Validate updated items if provided
        $validated = $request->validate([
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:stock_adjustment_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Update items if provided
            if (!empty($validated['items'])) {
                Log::info("Updating stock adjustment items before approval", [
                    'adjustment_id' => $stock_adjustment->id,
                    'items_count' => count($validated['items']),
                    'items' => $validated['items'],
                ]);

                foreach ($validated['items'] as $itemData) {
                    $item = \App\Models\StockAdjustmentItem::where('id', $itemData['id'])
                        ->where('adjustment_id', $stock_adjustment->id)
                        ->first();
                    if ($item) {
                        $item->update([
                            'quantity' => $itemData['quantity'],
                            'cost_price' => $itemData['cost_price'],
                            'line_total' => $itemData['quantity'] * $itemData['cost_price'],
                        ]);
                        Log::info("Updated stock adjustment item", [
                            'item_id' => $item->id,
                            'quantity' => $itemData['quantity'],
                            'cost_price' => $itemData['cost_price'],
                        ]);
                    } else {
                        Log::warning("Stock adjustment item not found", [
                            'item_id' => $itemData['id'],
                            'adjustment_id' => $stock_adjustment->id,
                        ]);
                    }
                }

                // Reload items to get updated values for total calculation
                $stock_adjustment->load('items');

                // Update header total
                $totalAmount = $stock_adjustment->items->sum(function ($item) {
                    return (float)$item->quantity * (float)$item->cost_price;
                });
                $stock_adjustment->update(['total_amount' => $totalAmount]);
            }

            // Reload items to get updated values
            $stock_adjustment->load('items');

            // Apply stock changes for each item
            foreach ($stock_adjustment->items as $item) {
                $prices = [
                    'cost_price' => $item->cost_price,
                    'sale_price' => $item->sale_price,
                    'wholesale_price' => $item->wholesale_price,
                    'vehicle_sale_price' => $item->vehicle_sale_price,
                ];
                $this->processStockUpdate($stock_adjustment, $item, $prices);
            }

            // Update status to approved
            $stock_adjustment->update([
                'status' => 'approved',
                'approved_by' => $user->id,
            ]);

            // Mark related notifications as read
            $user->notifications()
                ->where('type', StockAdjustmentPendingNotification::class)
                ->whereRaw("JSON_EXTRACT(data, '$.adjustment_id') = ?", [$stock_adjustment->id])
                ->update(['read_at' => now()]);

            DB::commit();
            return redirect()->back()->with('success', 'Stock adjustment approved and applied successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error approving stock adjustment: " . $e->getMessage());
            return back()->with('error', 'Failed to approve adjustment. ' . $e->getMessage());
        }
    }

    /**
     * Reject a pending stock adjustment.
     */
    public function reject(Request $request, StockAdjustment $stock_adjustment)
    {
        $user = $this->authUser();
        if (! $user || ! $user->hasPermission('stock_adjustments.approve')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have approval permission.');
        }

        if ($stock_adjustment->status !== 'pending') {
            return redirect()->back()->with('error', 'This adjustment cannot be rejected.');
        }

        $stock_adjustment->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
        ]);

        // Mark related notifications as read
        $user->notifications()
            ->where('type', StockAdjustmentPendingNotification::class)
            ->whereRaw("JSON_EXTRACT(data, '$.adjustment_id') = ?", [$stock_adjustment->id])
            ->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'Stock adjustment rejected.');
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead(Request $request)
    {
        $user = Auth::user() ?? Auth::guard('company')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notificationId = $request->input('notification_id');
        $user->notifications()
            ->where('id', $notificationId)
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread notifications count.
     */
    public function getNotifications()
    {
        $user = Auth::user() ?? Auth::guard('company')->user();
        if (!$user) {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }

        $notifications = $user->notifications()
            ->whereNull('read_at')
            ->latest()
            ->limit(10)
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
}
