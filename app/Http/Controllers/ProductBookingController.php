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
