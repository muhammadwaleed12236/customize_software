<?php

namespace App\Http\Controllers;

use App\Models\ProductBooking;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductBookingController extends Controller
{
    public function index()
    {
        $query = ProductBooking::with('customer','items', 'salesman')->latest();

        // For non-super-admin users, show bookings that belong to their branch.
        // Include bookings where `customer` is null (walking customers) by checking booking.branch_id
        if (!auth()->user() || !auth()->user()->hasRole('super admin')) {
            $branchId = auth()->user()->branch_id ?? 0;
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereHas('customer', function ($q2) use ($branchId) {
                      $q2->where('branch_id', $branchId);
                  });
            });
        }

        $bookings = $query->get();
        return view('admin_panel.booking.index', compact('bookings'));
    }
    public function receipt($id)
    {
        $booking = ProductBooking::with('customer')->findOrFail($id);
        return view('admin_panel.booking.receipt', compact('booking'));
    }

    public function create()
    {
        return app(SaleController::class)->addsale();
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $product_ids     = $request->product_id;
            $product_names   = $request->product_id;
            $product_codes   = $request->item_code;
            $brands          = $request->uom;
            $units           = $request->unit;
            $prices          = $request->price;
            $discounts       = $request->item_disc;
            $quantities      = $request->qty;
            $totals          = $request->total;
            $colors          = $request->color;

            $combined_products   = [];
            $combined_codes      = [];
            $combined_brands     = [];
            $combined_units      = [];
            $combined_prices     = [];
            $combined_discounts  = [];
            $combined_qtys       = [];
            $combined_totals     = [];
            $combined_colors     = [];

            $total_items = 0;

            foreach ($product_ids as $index => $product_id) {
                $qty   = $quantities[$index] ?? 0;
                $price = $prices[$index] ?? 0;

                if (!$product_id || !$qty || !$price) {
                    continue;
                }

                $combined_products[]   = $product_names[$index] ?? '';
                $combined_codes[]      = $product_codes[$index] ?? '';
                $combined_brands[]     = $brands[$index] ?? '';
                $combined_units[]      = $units[$index] ?? '';
                $combined_prices[]     = $prices[$index] ?? 0;
                $combined_discounts[]  = $discounts[$index] ?? 0;
                $combined_qtys[]       = $quantities[$index] ?? 0;
                $combined_totals[]     = $totals[$index] ?? 0;

                $rowColors = $colors[$index] ?? [];
                $combined_colors[] = json_encode($rowColors);

                $total_items += $qty;
            }

            $booking = new ProductBooking();
            $booking->customer_id         = $request->customer; // ✅ Corrected from $booking->customer
            $booking->salesman_id         = $request->salesman_id ?? null;
            $booking->reference           = $request->reference;
            $booking->product             = implode(',', $combined_products);
            $booking->product_code        = implode(',', $combined_codes);
            $booking->brand               = implode(',', $combined_brands);
            $booking->unit                = implode(',', $combined_units);
            $booking->per_price           = implode(',', $combined_prices);
            $booking->per_discount        = implode(',', $combined_discounts);
            $booking->qty                 = implode(',', $combined_qtys);
            $booking->per_total           = implode(',', $combined_totals);
            $booking->color               = json_encode($combined_colors);

            $booking->total_amount_Words = $request->total_amount_Words;
            $booking->total_bill_amount  = $request->total_subtotal;
            $booking->total_extradiscount = $request->total_extra_cost;
            $booking->total_net          = $request->total_net;

            $booking->cash   = $request->cash;
            $booking->card   = $request->card;
            $booking->change = $request->change;
            $booking->total_items = $total_items;
            $booking->save();

            DB::commit();
            return back()->with('success', 'Product booking saved (no stock reduced).');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $booking = ProductBooking::with('items')->findOrFail($id);
        $products = Product::get();
        $Customer = Customer::get();
        $salesmen = \App\Models\SalesOfficer::all();

        $bookingItems = [];
        if ($booking->items && $booking->items->count() > 0) {
            foreach ($booking->items as $item) {
                $bookingItems[] = [
                    'product_id' => $item->product_id,
                    'item_name'  => $item->product?->product_name ?? $item->product_name ?? '',
                    'item_code'  => $item->product_code ?? $item->product?->item_code ?? '',
                    'color'      => is_array($item->color) ? $item->color : json_decode($item->color ?? '[]', true),
                    'uom'        => $item->brand ?? $item->product?->brand ?? '',
                    'unit'       => $item->unit ?? $item->product?->unit ?? '',
                    'price'      => $item->per_price ?? $item->price ?? 0,
                    'discount'   => $item->per_discount ?? $item->discount_amount ?? 0,
                    'qty'        => $item->qty ?? 1,
                    'total'      => $item->per_total ?? $item->total ?? 0,
                ];
            }
        } else {
            $p_names   = explode(',', $booking->product ?? '');
            $p_codes   = explode(',', $booking->product_code ?? '');
            $brands    = explode(',', $booking->brand ?? '');
            $units     = explode(',', $booking->unit ?? '');
            $prices    = explode(',', $booking->per_price ?? '');
            $discounts = explode(',', $booking->per_discount ?? '');
            $qtys      = explode(',', $booking->qty ?? '');
            $totals    = explode(',', $booking->per_total ?? '');
            $colors    = json_decode($booking->color ?? '[]', true);

            foreach ($p_names as $i => $name) {
                if (empty($name)) continue;
                $prod = Product::find($name);
                $bookingItems[] = [
                    'product_id' => $name,
                    'item_name'  => $prod?->product_name ?? $name,
                    'item_code'  => $p_codes[$i] ?? '',
                    'color'      => is_array($colors[$i] ?? null) ? $colors[$i] : json_decode($colors[$i] ?? '[]', true),
                    'uom'        => $brands[$i] ?? '',
                    'unit'       => $units[$i] ?? '',
                    'price'      => $prices[$i] ?? 0,
                    'discount'   => $discounts[$i] ?? 0,
                    'qty'        => $qtys[$i] ?? 1,
                    'total'      => $totals[$i] ?? 0,
                ];
            }
        }

        return view('admin_panel.sale.booking_edit', compact('booking', 'bookingItems', 'products', 'Customer', 'salesmen'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $booking = ProductBooking::findOrFail($id);

            $product_ids     = $request->product_id ?? [];
            $product_names   = $request->product_id ?? [];
            $product_codes   = $request->item_code ?? [];
            $brands          = $request->uom ?? [];
            $units           = $request->unit ?? [];
            $prices          = $request->price ?? [];
            $discounts       = $request->item_disc ?? [];
            $quantities      = $request->qty ?? [];
            $totals          = $request->total ?? [];
            $colors          = $request->color ?? [];

            $combined_products   = [];
            $combined_codes      = [];
            $combined_brands     = [];
            $combined_units      = [];
            $combined_prices     = [];
            $combined_discounts  = [];
            $combined_qtys       = [];
            $combined_totals     = [];
            $combined_colors     = [];

            $total_items = 0;

            foreach ($product_ids as $index => $product_id) {
                $qty   = $quantities[$index] ?? 0;
                $price = $prices[$index] ?? 0;

                if (!$product_id || !$qty || !$price) {
                    continue;
                }

                $combined_products[]   = $product_names[$index] ?? '';
                $combined_codes[]      = $product_codes[$index] ?? '';
                $combined_brands[]     = $brands[$index] ?? '';
                $combined_units[]      = $units[$index] ?? '';
                $combined_prices[]     = $prices[$index] ?? 0;
                $combined_discounts[]  = $discounts[$index] ?? 0;
                $combined_qtys[]       = $quantities[$index] ?? 0;
                $combined_totals[]     = $totals[$index] ?? 0;

                $rowColors = $colors[$index] ?? [];
                $combined_colors[] = json_encode($rowColors);

                $total_items += $qty;
            }

            if ($request->customer) {
                $booking->customer_id = $request->customer;
            }
            if ($request->salesman_id) {
                $booking->salesman_id = $request->salesman_id;
            }
            if ($request->reference) {
                $booking->reference = $request->reference;
            }

            $booking->product             = implode(',', $combined_products);
            $booking->product_code        = implode(',', $combined_codes);
            $booking->brand               = implode(',', $combined_brands);
            $booking->unit                = implode(',', $combined_units);
            $booking->per_price           = implode(',', $combined_prices);
            $booking->per_discount        = implode(',', $combined_discounts);
            $booking->qty                 = implode(',', $combined_qtys);
            $booking->per_total           = implode(',', $combined_totals);
            $booking->color               = json_encode($combined_colors);

            if ($request->total_amount_Words) $booking->total_amount_Words = $request->total_amount_Words;
            if ($request->total_subtotal)     $booking->total_bill_amount  = $request->total_subtotal;
            if ($request->total_extra_cost)   $booking->total_extradiscount = $request->total_extra_cost;
            if ($request->total_net)          $booking->total_net          = $request->total_net;

            if ($request->cash)   $booking->cash   = $request->cash;
            if ($request->card)   $booking->card   = $request->card;
            if ($request->change) $booking->change = $request->change;
            $booking->total_items = $total_items;
            $booking->save();

            DB::commit();
            return redirect()->route('bookings.index')->with('success', 'Booking updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // Redirect direct URL access to the booking invoice view
        return redirect()->route('booking.invoice', $id);
    }

    public function deliverForm($id)
    {
        $booking = ProductBooking::with(['items.product', 'customer'])->findOrFail($id);

        // Check if fully delivered
        if ($booking->delivery_status === 'delivered') {
            return back()->with('error', 'This booking has already been fully delivered.');
        }

        // Calculate remaining for each item
        $items = $booking->items->map(function ($item) {
            $item->remaining_qty = max(0, floatval($item->sales_qty) - floatval($item->delivered_qty));
            return $item;
        })->filter(fn($item) => $item->remaining_qty > 0);

        if ($items->isEmpty()) {
            return back()->with('error', 'All items have been fully delivered.');
        }

        // Get warehouse stocks for each product
        $warehouses = \App\Models\WarehouseStock::with('warehouse')
            ->whereIn('product_id', $items->pluck('product_id'))
            ->where('quantity', '>', 0)
            ->get()
            ->groupBy('product_id');

        return view('admin_panel.booking.partial_delivery', compact('booking', 'items', 'warehouses'));
    }

    public function deliverStore(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $booking = ProductBooking::with(['items.product', 'customer'])->lockForUpdate()->findOrFail($id);

            if ($booking->delivery_status === 'delivered') {
                return back()->with('error', 'This booking has already been fully delivered.');
            }

            $deliverNow   = $request->input('deliver_qty', []);
            $warehouseIds = $request->input('warehouse_id', []);

            // Validate quantities
            foreach ($deliverNow as $itemId => $qty) {
                $qty = floatval($qty);
                if ($qty <= 0) continue;

                $item = $booking->items->firstWhere('id', $itemId);
                if (!$item) continue;

                $remaining = floatval($item->sales_qty) - floatval($item->delivered_qty);
                if ($qty > $remaining) {
                    return back()->with('error', "Qty {$qty} for product {$item->product->item_name} exceeds remaining {$remaining}.");
                }
            }

            // Generate sale invoice number
            $branch = \App\Models\Branch::lockForUpdate()->find($booking->branch_id);
            if ($branch) {
                $branch->invoice_counter = ((int)($branch->invoice_counter ?? 0)) + 1;
                $branch->save();
                $invoiceNo = 'INV-' . str_pad($branch->invoice_counter, 4, '0', STR_PAD_LEFT);
            } else {
                $invoiceNo = \App\Models\Sale::generateInvoiceNo();
            }

            // Calculate partial totals
            $partialSubTotal = 0;
            $deliveryItems   = [];

            foreach ($deliverNow as $itemId => $qty) {
                $qty = floatval($qty);
                if ($qty <= 0) continue;

                $item = $booking->items->firstWhere('id', $itemId);
                if (!$item) continue;

                $remaining = floatval($item->sales_qty) - floatval($item->delivered_qty);
                if ($qty > $remaining) continue;

                $unitPrice    = floatval($item->retail_price);
                $unitDiscount = floatval($item->sales_qty) > 0 ? (floatval($item->discount_amount) / floatval($item->sales_qty)) : 0;
                $lineAmount   = ($unitPrice - $unitDiscount) * $qty;
                $partialSubTotal += $lineAmount;

                $deliveryItems[] = [
                    'item'          => $item,
                    'qty'           => $qty,
                    'warehouse_id'  => $warehouseIds[$itemId] ?? $item->warehouse_id ?? null,
                    'unit_price'    => $unitPrice,
                    'unit_discount' => $unitDiscount,
                    'line_amount'   => $lineAmount,
                ];
            }

            if (empty($deliveryItems)) {
                return back()->with('error', 'No valid quantities entered for delivery.');
            }

            // Proportional discount and charges
            $bookingTotal             = floatval($booking->sub_total1 ?: ($booking->sub_total2 ?: 1));
            $ratio                    = $bookingTotal > 0 ? ($partialSubTotal / $bookingTotal) : 1;
            $partialAdditionalDiscount = floatval($booking->additional_discount) * $ratio;
            $partialExtraCharges      = floatval($booking->extra_charges) * $ratio;
            $partialNet               = $partialSubTotal - $partialAdditionalDiscount + $partialExtraCharges;

            // Create Sale record
            $saleData = [
                'invoice_no'          => $invoiceNo,
                'manual_invoice'      => $booking->manual_invoice,
                'customer_id'         => $booking->customer_id,
                'salesman_id'         => $booking->salesman_id,
                'sub_customer'        => ($booking->party_type === 'walking') ? ($booking->customer_name ?? null) : null,
                'party_type'          => $booking->party_type,
                'address'             => $booking->address,
                'tel'                 => $booking->tel,
                'remarks'             => ($booking->remarks ?? '') . ' [Partial Delivery from ' . $booking->invoice_no . ']',
                'sub_total1'          => $partialSubTotal,
                'sub_total2'          => $partialNet,
                'discount_percent'    => 0,
                'discount_amount'     => 0,
                'additional_discount' => $partialAdditionalDiscount,
                'extra_charges'       => $partialExtraCharges,
                'previous_balance'    => 0,
                'total_balance'       => $partialNet,
                'total_net'           => $partialNet,
                'branch_id'           => $booking->branch_id,
                'booking_id'          => $booking->id,
            ];

            $sale = \App\Models\Sale::create($saleData);

            // Create sale items, deduct stock
            foreach ($deliveryItems as $di) {
                $item     = $di['item'];
                $qty      = $di['qty'];
                $wid      = $di['warehouse_id'];
                $branchId = $booking->branch_id;

                // Deduct WarehouseStock
                $warehousestock = \App\Models\WarehouseStock::lockForUpdate()
                    ->where('product_id', $item->product_id)
                    ->where('branch_id', $branchId)
                    ->where('warehouse_id', $wid)
                    ->first();

                if ($warehousestock) {
                    $warehousestock->quantity -= $qty;
                    $warehousestock->save();
                }

                // Deduct Stock (branch-level)
                $stock = \App\Models\Stock::lockForUpdate()
                    ->where('product_id', $item->product_id)
                    ->where('branch_id', $branchId)
                    ->first();

                if ($stock) {
                    $stock->qty -= $qty;
                    $stock->save();
                }

                // Sale Item
                \App\Models\SaleItem::create([
                    'invoice_no'       => $sale->invoice_no,
                    'branch_id'        => $sale->branch_id,
                    'sale_id'          => $sale->id,
                    'warehouse_id'     => $wid,
                    'product_id'       => $item->product_id,
                    'sales_qty'        => $qty,
                    'retail_price'     => $di['unit_price'],
                    'discount_percent' => 0,
                    'discount_amount'  => $di['unit_discount'] * $qty,
                    'amount'           => $di['line_amount'],
                ]);

                // Stock Movement
                \App\Models\StockMovement::create([
                    'product_id'    => $item->product_id,
                    'type'          => 'out',
                    'qty'           => $qty,
                    'ref_type'      => 'PARTIAL_DELIVERY',
                    'ref_id'        => $sale->id,
                    'ref_uuid'      => $booking->invoice_no,
                    'is_auto_pluck' => 1,
                    'note'          => 'Partial Delivery ' . $invoiceNo . ' from Booking ' . $booking->invoice_no,
                ]);

                // Update booking item's delivered_qty
                $item->delivered_qty = floatval($item->delivered_qty) + $qty;
                $item->save();
            }

            // Customer Ledger for credit customers
            if ($booking->party_type === 'credit' && $booking->customer_id) {
                $lastLedger = \App\Models\CustomerLedger::where('customer_id', $booking->customer_id)
                    ->latest('id')->lockForUpdate()->first();
                $customer    = \App\Models\Customer::find($booking->customer_id);
                $prevBalance = $lastLedger ? floatval($lastLedger->closing_balance) : floatval($customer->opening_balance ?? 0);
                $closing     = $prevBalance + $partialNet;

                \App\Models\CustomerLedger::create([
                    'customer_id'      => $booking->customer_id,
                    'admin_or_user_id' => auth()->id(),
                    'transaction_type' => 'Partial Delivery',
                    'reference_id'     => (string) $sale->id,
                    'description'      => 'Partial Delivery - Invoice ' . $invoiceNo . ' (Booking: ' . $booking->invoice_no . ')',
                    'opening_balance'  => floatval($customer->opening_balance ?? 0),
                    'previous_balance' => $prevBalance,
                    'total_debit'      => $partialNet,
                    'total_credit'     => 0,
                    'closing_balance'  => $closing,
                ]);
            }

            // Update booking delivery_status
            $booking->refresh();
            $allDelivered = $booking->items->every(fn($i) => floatval($i->delivered_qty) >= floatval($i->sales_qty));
            $anyDelivered = $booking->items->contains(fn($i) => floatval($i->delivered_qty) > 0);

            $booking->delivery_status = $allDelivered ? 'delivered' : ($anyDelivered ? 'partial' : 'pending');
            if ($allDelivered) {
                $booking->status = 'approved';
            }
            $booking->save();

            return redirect()->route('bookings.index')
                ->with('success', "Partial delivery done! Invoice {$invoiceNo} created. Delivered " . count($deliveryItems) . " item(s).");
        });
    }

    public function destroy($id)
    {
        try {
            $booking = ProductBooking::findOrFail($id);
            if ($booking->items()) {
                $booking->items()->delete();
            }
            $booking->delete();
            return back()->with('success', 'Booking deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting booking: ' . $e->getMessage());
        }
    }
}
