<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Product;
use App\Models\Stock;
use App\Models\WarehouseStock;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\SalesOfficer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure basic seeders are executed
        $this->call([
            BranchAccountSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            UnitSeeder::class,
            ProductTypeSeeder::class,
            WarehouseSeeder::class,
            BranchWarehouseSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
        ]);

        $branches = Branch::all();
        if ($branches->isEmpty()) {
            $this->command->error('No branches found.');
            return;
        }

        $branch1 = Branch::find(1) ?? $branches->first();
        $branch2 = Branch::find(2) ?? $branches->first();
        $branch3 = Branch::find(3) ?? $branches->first();

        $warehouses = Warehouse::all();
        $whMain = Warehouse::where('warehouse_name', 'like', '%Main%')->first() ?? $warehouses->first();
        $wh1 = Warehouse::where('warehouse_name', 'like', '%1%')->first() ?? $whMain;
        $wh2 = Warehouse::where('warehouse_name', 'like', '%2%')->first() ?? $whMain;

        $catElectronics = Category::where('name', 'Electronics')->first() ?? Category::first();
        $catTools = Category::where('name', 'Tools')->first() ?? $catElectronics;
        $catElectrical = Category::where('name', 'Electrical')->first() ?? $catElectronics;

        $subFan = Subcategory::where('name', 'like', '%Fan%')->first() ?? Subcategory::first();
        $subFridge = Subcategory::where('name', 'like', '%Fridge%')->first() ?? $subFan;
        $subAc = Subcategory::where('name', 'like', '%Air-Condition%')->first() ?? $subFan;
        $subWashing = Subcategory::where('name', 'like', '%Washing%')->first() ?? $subFan;
        $subLight = Subcategory::where('name', 'like', '%Light%')->first() ?? $subFan;
        $subWire = Subcategory::where('name', 'like', '%Wire%')->first() ?? $subFan;
        $subDrill = Subcategory::where('name', 'like', '%Drill%')->first() ?? $subFan;

        $brandPakFan = Brand::firstOrCreate(['name' => 'Pak Fan']);
        $brandSuperAsia = Brand::firstOrCreate(['name' => 'Super Asia']);
        $brandHaier = Brand::firstOrCreate(['name' => 'Haier']);
        $brandDawlance = Brand::firstOrCreate(['name' => 'Dawlance']);
        $brandGree = Brand::firstOrCreate(['name' => 'Gree']);
        $brandBosch = Brand::firstOrCreate(['name' => 'Bosch']);

        $unitPiece = Unit::firstOrCreate(['name' => 'Piece']);
        $unitMeter = Unit::firstOrCreate(['name' => 'Meter']);
        $unitSet = Unit::firstOrCreate(['name' => 'Set']);

        // 2. Add realistic new products
        $newProductsData = [
            [
                'branch_id' => $branch1->id,
                'category_id' => $catElectronics->id,
                'sub_category_id' => $subFan->id,
                'brand_id' => $brandSuperAsia->id,
                'unit_id' => $unitPiece->id,
                'item_name' => 'Super Asia Ceiling Fan 56 inch Deluxe',
                'item_name_urdu' => 'سپر ایشیا سیلنگ فین 56 انچ',
                'item_code' => 'FAN-SA-56',
                'price' => 6500.00,
                'wholesale_price' => 5800.00,
                'initial_stock' => 50,
                'alert_quantity' => 5,
                'model' => 'SA-DLX-2026',
                'pack_type' => 'Box',
                'pack_qty' => 1,
                'piece_per_pack' => 1,
            ],
            [
                'branch_id' => $branch1->id,
                'category_id' => $catElectronics->id,
                'sub_category_id' => $subFan->id,
                'brand_id' => $brandPakFan->id,
                'unit_id' => $unitPiece->id,
                'item_name' => 'Pak Fan Pedestal Fan Deluxe 24 inch',
                'item_name_urdu' => 'پاک فین پیڈسٹل فین 24 انچ',
                'item_code' => 'FAN-PF-24',
                'price' => 8200.00,
                'wholesale_price' => 7400.00,
                'initial_stock' => 35,
                'alert_quantity' => 4,
                'model' => 'PF-PED-2026',
                'pack_type' => 'Box',
                'pack_qty' => 1,
                'piece_per_pack' => 1,
            ],
            [
                'branch_id' => $branch1->id,
                'category_id' => $catElectronics->id,
                'sub_category_id' => $subAc->id,
                'brand_id' => $brandDawlance->id,
                'unit_id' => $unitPiece->id,
                'item_name' => 'Dawlance Inverter AC 1.5 Ton Chrome Pro',
                'item_name_urdu' => 'ڈولینس انورٹر اے سی 1.5 ٹن کروم',
                'item_code' => 'AC-DW-15C',
                'price' => 145000.00,
                'wholesale_price' => 132000.00,
                'initial_stock' => 15,
                'alert_quantity' => 2,
                'model' => 'Chrome-15-Inv',
                'pack_type' => 'Box',
                'pack_qty' => 1,
                'piece_per_pack' => 1,
            ],
            [
                'branch_id' => $branch1->id,
                'category_id' => $catElectronics->id,
                'sub_category_id' => $subFridge->id,
                'brand_id' => $brandHaier->id,
                'unit_id' => $unitPiece->id,
                'item_name' => 'Haier Direct Cool Refrigerator HRF-336',
                'item_name_urdu' => 'ہائیر ریفریجریٹر HRF-336',
                'item_code' => 'FRG-HR-336',
                'price' => 88000.00,
                'wholesale_price' => 79000.00,
                'initial_stock' => 20,
                'alert_quantity' => 3,
                'model' => 'HRF-336-DC',
                'pack_type' => 'Box',
                'pack_qty' => 1,
                'piece_per_pack' => 1,
            ],
            [
                'branch_id' => $branch2->id,
                'category_id' => $catElectronics->id,
                'sub_category_id' => $subAc->id,
                'brand_id' => $brandGree->id,
                'unit_id' => $unitPiece->id,
                'item_name' => 'Gree Fairy Inverter AC 1.0 Ton Heat & Cool',
                'item_name_urdu' => 'گری فیری انورٹر اے سی 1 ٹن',
                'item_code' => 'AC-GR-10F',
                'price' => 115000.00,
                'wholesale_price' => 104000.00,
                'initial_stock' => 12,
                'alert_quantity' => 2,
                'model' => 'GWH-12-Fairy',
                'pack_type' => 'Box',
                'pack_qty' => 1,
                'piece_per_pack' => 1,
            ],
            [
                'branch_id' => $branch2->id,
                'category_id' => $catElectronics->id,
                'sub_category_id' => $subWashing->id,
                'brand_id' => $brandSuperAsia->id,
                'unit_id' => $unitPiece->id,
                'item_name' => 'Super Asia Washing Machine Twin Tub 10Kg',
                'item_name_urdu' => 'سپر ایشیا واشنگ مشین 10 کلو',
                'item_code' => 'WM-SA-10K',
                'price' => 38000.00,
                'wholesale_price' => 33500.00,
                'initial_stock' => 25,
                'alert_quantity' => 3,
                'model' => 'SA-240-Twin',
                'pack_type' => 'Box',
                'pack_qty' => 1,
                'piece_per_pack' => 1,
            ],
            [
                'branch_id' => $branch2->id,
                'category_id' => $catElectrical->id,
                'sub_category_id' => $subWire->id,
                'brand_id' => $brandPakFan->id,
                'unit_id' => $unitMeter->id,
                'item_name' => 'Pak Cables 7/29 Single Core Red 90m Roll',
                'item_name_urdu' => 'پاک کیبلز 7/29 وائر رول 90 میٹر',
                'item_code' => 'WIR-PK-729',
                'price' => 12500.00,
                'wholesale_price' => 11000.00,
                'initial_stock' => 40,
                'alert_quantity' => 5,
                'model' => '7-29-Copper',
                'pack_type' => 'Roll',
                'pack_qty' => 1,
                'piece_per_pack' => 1,
            ],
            [
                'branch_id' => $branch2->id,
                'category_id' => $catTools->id,
                'sub_category_id' => $subDrill->id,
                'brand_id' => $brandBosch->id,
                'unit_id' => $unitPiece->id,
                'item_name' => 'Bosch Impact Drill 13mm 600W Professional',
                'item_name_urdu' => 'بوش امپیکٹ ڈرل مشین 13 ایم ایم',
                'item_code' => 'DRL-BS-13M',
                'price' => 9500.00,
                'wholesale_price' => 8200.00,
                'initial_stock' => 30,
                'alert_quantity' => 4,
                'model' => 'GSB-600',
                'pack_type' => 'Box',
                'pack_qty' => 1,
                'piece_per_pack' => 1,
            ],
            [
                'branch_id' => $branch3->id,
                'category_id' => $catTools->id,
                'sub_category_id' => $subDrill->id,
                'brand_id' => $brandBosch->id,
                'unit_id' => $unitPiece->id,
                'item_name' => 'Bosch Angle Grinder 850W Heavy Duty',
                'item_name_urdu' => 'بوش اینگل گرائنڈر 850 واٹ',
                'item_code' => 'GND-BS-850',
                'price' => 7200.00,
                'wholesale_price' => 6100.00,
                'initial_stock' => 20,
                'alert_quantity' => 3,
                'model' => 'GWS-850',
                'pack_type' => 'Box',
                'pack_qty' => 1,
                'piece_per_pack' => 1,
            ],
            [
                'branch_id' => $branch3->id,
                'category_id' => $catTools->id,
                'sub_category_id' => $subDrill->id,
                'brand_id' => $brandBosch->id,
                'unit_id' => $unitSet->id,
                'item_name' => 'Crown Screwdriver Pro Set 6 Pieces',
                'item_name_urdu' => 'کراؤن اسکرو ڈرائیور سیٹ 6 عدد',
                'item_code' => 'TOL-CR-SCR',
                'price' => 1400.00,
                'wholesale_price' => 1100.00,
                'initial_stock' => 60,
                'alert_quantity' => 10,
                'model' => 'CR-SET-6',
                'pack_type' => 'Pack',
                'pack_qty' => 1,
                'piece_per_pack' => 6,
            ],
        ];

        foreach ($newProductsData as $pData) {
            $product = Product::updateOrCreate(
                ['item_code' => $pData['item_code']],
                array_merge($pData, ['creater_id' => 1])
            );

            // Update branch stock
            Stock::updateOrCreate(
                ['branch_id' => $product->branch_id, 'product_id' => $product->id],
                ['qty' => $pData['initial_stock'], 'reserved_qty' => 0]
            );

            // Update warehouse stock in Main warehouse and branch warehouse
            if ($whMain) {
                WarehouseStock::updateOrCreate(
                    [
                        'branch_id' => $product->branch_id,
                        'warehouse_id' => $whMain->id,
                        'product_id' => $product->id
                    ],
                    [
                        'quantity' => ceil($pData['initial_stock'] * 0.6),
                        'price' => $pData['price'],
                    ]
                );
            }
            if ($wh1 && $wh1->id !== $whMain->id) {
                WarehouseStock::updateOrCreate(
                    [
                        'branch_id' => $product->branch_id,
                        'warehouse_id' => $wh1->id,
                        'product_id' => $product->id
                    ],
                    [
                        'quantity' => floor($pData['initial_stock'] * 0.4),
                        'price' => $pData['price'],
                    ]
                );
            }
        }

        // Also ensure all existing products have warehouse stock
        $allProducts = Product::all();
        foreach ($allProducts as $prod) {
            if ($whMain) {
                WarehouseStock::firstOrCreate(
                    [
                        'branch_id' => $prod->branch_id ?? $branch1->id,
                        'warehouse_id' => $whMain->id,
                        'product_id' => $prod->id
                    ],
                    [
                        'quantity' => 25,
                        'price' => $prod->price ?? 0,
                    ]
                );
            }
        }

        // 3. Vendors (Suppliers)
        $vendorsData = [
            [
                'branch_id' => $branch1->id,
                'name' => 'Super Asia Manufacturing Ltd',
                'email' => 'sales@superasia.biz',
                'phone' => '0321-4455667',
                'address' => 'G.T. Road Industrial Estate, Gujranwala',
                'company_names' => ['Super Asia Group', 'Super Asia Appliances'],
                'brand_ids' => [$brandSuperAsia->id],
                'opening_balance' => 0.00,
            ],
            [
                'branch_id' => $branch1->id,
                'name' => 'Haier Electronics Pakistan Pvt Ltd',
                'email' => 'orders@haier.com.pk',
                'phone' => '0311-7788990',
                'address' => 'Haier Industrial Estate, Raiwind Road, Lahore',
                'company_names' => ['Haier Pakistan', 'Ruba General Trading'],
                'brand_ids' => [$brandHaier->id],
                'opening_balance' => 25000.00,
            ],
            [
                'branch_id' => $branch1->id,
                'name' => 'Dawlance Private Limited',
                'email' => 'corporate@dawlance.com.pk',
                'phone' => '0300-9988112',
                'address' => 'Dawlance Center, West Wharf Road, Karachi',
                'company_names' => ['Dawlance Arcelik Group'],
                'brand_ids' => [$brandDawlance->id],
                'opening_balance' => 50000.00,
            ],
            [
                'branch_id' => $branch2->id,
                'name' => 'Pakistan Cables Limited',
                'email' => 'info@pakcables.com',
                'phone' => '0333-5566778',
                'address' => 'Brandreth Road Commercial Area, Lahore',
                'company_names' => ['Pak Cables Ltd'],
                'brand_ids' => [$brandPakFan->id],
                'opening_balance' => 10000.00,
            ],
            [
                'branch_id' => $branch2->id,
                'name' => 'Al-Madina Power Tools & Hardware',
                'email' => 'almadinatools@gmail.com',
                'phone' => '0345-9988776',
                'address' => 'Saddar Hardware Market, Rawalpindi',
                'company_names' => ['Al Madina Tools Importers'],
                'brand_ids' => [$brandBosch->id],
                'opening_balance' => 0.00,
            ],
        ];

        $vendors = [];
        foreach ($vendorsData as $vData) {
            $vendors[] = Vendor::updateOrCreate(
                ['name' => $vData['name']],
                $vData
            );
        }

        // 4. Sales Officers (Salesmen)
        $salesmenData = [
            ['branch_id' => $branch1->id, 'name' => 'Muhammad Usman', 'name_urdu' => 'محمد عثمان', 'mobile' => '0300-7654321'],
            ['branch_id' => $branch1->id, 'name' => 'Tariq Mahmood', 'name_urdu' => 'طارق محمود', 'mobile' => '0301-8765432'],
            ['branch_id' => $branch2->id, 'name' => 'Kashif Ali', 'name_urdu' => 'کاشف علی', 'mobile' => '0302-9876543'],
            ['branch_id' => $branch3->id, 'name' => 'Bilal Ahmed', 'name_urdu' => 'بلال احمد', 'mobile' => '0303-1239874'],
        ];

        $salesmen = [];
        foreach ($salesmenData as $smData) {
            $salesmen[] = SalesOfficer::updateOrCreate(
                ['mobile' => $smData['mobile']],
                $smData
            );
        }

        // 5. Purchases (Incoming Stock / Purchase Invoices)
        $purchasesList = [
            [
                'branch_id' => $branch1->id,
                'warehouse_id' => $whMain->id,
                'vendor_id' => $vendors[0]->id,
                'vendor_name' => $vendors[0]->name,
                'purchase_date' => Carbon::now()->subDays(15),
                'invoice_no' => 'P-INV-0001',
                'purchase_type' => 'supplier',
                'note' => 'Season opening stock for Fans & Appliances',
                'transport_name' => 'TCS Freight / Faisal Movers',
                'items' => [
                    ['code' => 'FAN-SA-56', 'qty' => 20, 'price' => 5200.00, 'discount' => 200.00],
                    ['code' => 'FAN-PF-24', 'qty' => 15, 'price' => 6800.00, 'discount' => 300.00],
                ],
                'paid_amount' => 195000.00,
            ],
            [
                'branch_id' => $branch1->id,
                'warehouse_id' => $whMain->id,
                'vendor_id' => $vendors[2]->id,
                'vendor_name' => $vendors[2]->name,
                'purchase_date' => Carbon::now()->subDays(10),
                'invoice_no' => 'P-INV-0002',
                'purchase_type' => 'supplier',
                'note' => 'Inverter ACs supply shipment',
                'transport_name' => 'Direct Factory Container',
                'items' => [
                    ['code' => 'AC-DW-15C', 'qty' => 5, 'price' => 125000.00, 'discount' => 2000.00],
                    ['code' => 'FRG-HR-336', 'qty' => 6, 'price' => 74000.00, 'discount' => 1000.00],
                ],
                'paid_amount' => 500000.00,
            ],
            [
                'branch_id' => $branch2->id,
                'warehouse_id' => $wh1->id,
                'vendor_id' => $vendors[3]->id,
                'vendor_name' => $vendors[3]->name,
                'purchase_date' => Carbon::now()->subDays(5),
                'invoice_no' => 'P-INV-0003',
                'purchase_type' => 'supplier',
                'note' => 'Cables and wiring stock delivery',
                'transport_name' => 'Pak Goods Transport',
                'items' => [
                    ['code' => 'WIR-PK-729', 'qty' => 25, 'price' => 10500.00, 'discount' => 200.00],
                    ['code' => 'DRL-BS-13M', 'qty' => 10, 'price' => 7800.00, 'discount' => 150.00],
                ],
                'paid_amount' => 335000.00,
            ],
        ];

        foreach ($purchasesList as $pInfo) {
            $existing = Purchase::where('invoice_no', $pInfo['invoice_no'])->first();
            if ($existing) {
                continue;
            }

            $subtotal = 0;
            $totalDiscount = 0;
            $itemsToCreate = [];

            foreach ($pInfo['items'] as $itemData) {
                $prod = Product::where('item_code', $itemData['code'])->first();
                if (!$prod) continue;

                $lineTotal = ($itemData['price'] - $itemData['discount']) * $itemData['qty'];
                $subtotal += ($itemData['price'] * $itemData['qty']);
                $totalDiscount += ($itemData['discount'] * $itemData['qty']);

                $itemsToCreate[] = [
                    'product_id' => $prod->id,
                    'color' => 'Standard',
                    'warehouse_id' => $pInfo['warehouse_id'],
                    'unit' => 'Piece',
                    'price' => $itemData['price'],
                    'item_discount' => $itemData['discount'],
                    'qty' => $itemData['qty'],
                    'packing_type' => 'Box',
                    'packing_qty' => 1,
                    'item_per_piece' => 1,
                    'loose_piece' => 0,
                    'line_total' => $lineTotal,
                ];
            }

            $netAmount = $subtotal - $totalDiscount;
            $paid = $pInfo['paid_amount'];
            $due = max(0, $netAmount - $paid);

            $purchase = Purchase::create([
                'branch_id' => $pInfo['branch_id'],
                'warehouse_id' => $pInfo['warehouse_id'],
                'vendor_id' => $pInfo['vendor_id'],
                'vendor_name' => $pInfo['vendor_name'],
                'purchase_date' => $pInfo['purchase_date'],
                'invoice_no' => $pInfo['invoice_no'],
                'purchase_type' => $pInfo['purchase_type'],
                'note' => $pInfo['note'],
                'transport_name' => $pInfo['transport_name'],
                'subtotal' => $subtotal,
                'discount' => $totalDiscount,
                'extra_cost' => 0,
                'net_amount' => $netAmount,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'status_purchase' => 'completed',
            ]);

            foreach ($itemsToCreate as $itemArr) {
                $itemArr['purchase_id'] = $purchase->id;
                PurchaseItem::create($itemArr);
            }
        }

        // 6. Realistic Sales & Sale Items
        $customers = Customer::all();
        $custAli = Customer::where('customer_name', 'Ali Ahmed')->first() ?? $customers->first();
        $custFatima = Customer::where('customer_name', 'Fatima Khan')->first() ?? $customers->skip(1)->first() ?? $custAli;
        $custCash = Customer::where('customer_type', 'cash')->first() ?? $custAli;
        $custWalking = Customer::where('customer_type', 'walking')->first() ?? $custAli;

        $salesList = [
            [
                'branch_id' => $branch1->id,
                'invoice_no' => 'INV-0002',
                'customer_id' => $custAli->id,
                'party_type' => 'credit',
                'salesman_id' => $salesmen[0]->id ?? null,
                'filer_type' => 'filer',
                'remarks' => 'Monthly installment customer order',
                'created_at' => Carbon::now()->subDays(7),
                'receipt_amount' => 15000.00,
                'items' => [
                    ['code' => 'FAN-SA-56', 'qty' => 3, 'price' => 6500.00, 'discount' => 200.00],
                    ['code' => 'FAN-PF-24', 'qty' => 2, 'price' => 8200.00, 'discount' => 250.00],
                ],
            ],
            [
                'branch_id' => $branch1->id,
                'invoice_no' => 'INV-0003',
                'customer_id' => $custFatima->id,
                'party_type' => 'credit',
                'salesman_id' => $salesmen[0]->id ?? null,
                'filer_type' => 'filer',
                'remarks' => 'Direct delivery to residential address',
                'created_at' => Carbon::now()->subDays(4),
                'receipt_amount' => 140000.00,
                'items' => [
                    ['code' => 'AC-DW-15C', 'qty' => 1, 'price' => 145000.00, 'discount' => 5000.00],
                ],
            ],
            [
                'branch_id' => $branch2->id,
                'invoice_no' => 'INV-0004',
                'customer_id' => $custCash->id,
                'party_type' => 'cash',
                'salesman_id' => $salesmen[2]->id ?? null,
                'filer_type' => 'non-filer',
                'remarks' => 'Cash counter sale with full payment',
                'created_at' => Carbon::now()->subDays(3),
                'receipt_amount' => null, // Full payment
                'items' => [
                    ['code' => 'DRL-BS-13M', 'qty' => 2, 'price' => 9500.00, 'discount' => 500.00],
                    ['code' => 'WIR-PK-729', 'qty' => 1, 'price' => 12500.00, 'discount' => 500.00],
                ],
            ],
            [
                'branch_id' => $branch2->id,
                'invoice_no' => 'INV-0005',
                'customer_id' => $custWalking->id,
                'party_type' => 'walking',
                'salesman_id' => $salesmen[2]->id ?? null,
                'filer_type' => 'non-filer',
                'remarks' => 'Retail shop walking customer counter sale',
                'created_at' => Carbon::now()->subDays(1),
                'receipt_amount' => null, // Full payment
                'items' => [
                    ['code' => 'TOL-CR-SCR', 'qty' => 4, 'price' => 1400.00, 'discount' => 100.00],
                    ['code' => 'GND-BS-850', 'qty' => 1, 'price' => 7200.00, 'discount' => 200.00],
                ],
            ],
            [
                'branch_id' => $branch3->id,
                'invoice_no' => 'INV-0006',
                'customer_id' => $custAli->id,
                'party_type' => 'credit',
                'salesman_id' => $salesmen[3]->id ?? null,
                'filer_type' => 'filer',
                'remarks' => 'Karachi Branch corporate bulk sale',
                'created_at' => Carbon::now()->subHours(8),
                'receipt_amount' => 50000.00,
                'items' => [
                    ['code' => 'FRG-HR-336', 'qty' => 1, 'price' => 88000.00, 'discount' => 3000.00],
                    ['code' => 'WM-SA-10K', 'qty' => 1, 'price' => 38000.00, 'discount' => 1500.00],
                ],
            ],
        ];

        foreach ($salesList as $saleData) {
            $existing = Sale::where('invoice_no', $saleData['invoice_no'])->first();
            if ($existing) {
                continue;
            }

            $subtotal = 0;
            $totalDiscount = 0;
            $totalQty = 0;
            $itemsList = [];

            foreach ($saleData['items'] as $item) {
                $prod = Product::where('item_code', $item['code'])->first();
                if (!$prod) continue;

                $itemSubtotal = $item['price'] * $item['qty'];
                $itemDiscountAmt = $item['discount'] * $item['qty'];
                $itemTotal = $itemSubtotal - $itemDiscountAmt;

                $subtotal += $itemSubtotal;
                $totalDiscount += $itemDiscountAmt;
                $totalQty += $item['qty'];

                $discountPct = ($item['price'] > 0) ? round(($item['discount'] / $item['price']) * 100, 2) : 0;

                $itemsList[] = [
                    'branch_id' => $saleData['branch_id'],
                    'invoice_no' => $saleData['invoice_no'],
                    'warehouse_id' => $whMain ? $whMain->id : null,
                    'product_id' => $prod->id,
                    'watt' => 0,
                    'stock' => 50,
                    'price_level' => 0,
                    'sales_price' => $item['price'] - $item['discount'],
                    'sales_qty' => $item['qty'],
                    'retail_price' => $item['price'],
                    'discount_percent' => $discountPct,
                    'discount_amount' => $itemDiscountAmt,
                    'amount' => $itemTotal,
                    'ready_for_delivery' => 1,
                    'created_at' => $saleData['created_at'],
                    'updated_at' => $saleData['created_at'],
                ];
            }

            $netTotal = $subtotal - $totalDiscount;
            $receipt = $saleData['receipt_amount'] ?? $netTotal;
            $finalBalance = max(0, $netTotal - $receipt);

            $sale = Sale::create([
                'branch_id' => $saleData['branch_id'],
                'invoice_no' => $saleData['invoice_no'],
                'party_type' => $saleData['party_type'],
                'customer_id' => $saleData['customer_id'],
                'salesman_id' => $saleData['salesman_id'],
                'filer_type' => $saleData['filer_type'],
                'remarks' => $saleData['remarks'],
                'quantity' => $totalQty,
                'sub_total1' => $subtotal,
                'sub_total2' => $subtotal,
                'discount_percent' => ($subtotal > 0) ? round(($totalDiscount / $subtotal) * 100, 2) : 0,
                'discount_amount' => $totalDiscount,
                'additional_discount' => 0,
                'extra_charges' => 0,
                'previous_balance' => 0,
                'total_balance' => $netTotal,
                'receipt1' => $receipt,
                'receipt2' => 0,
                'final_balance1' => $finalBalance,
                'final_balance2' => 0,
                'total_net' => $netTotal,
                'status' => 'posted',
                'is_posted' => 1,
                'posted_at' => $saleData['created_at'],
                'finalized_at' => $saleData['created_at'],
                'finalized_by' => 1,
                'created_at' => $saleData['created_at'],
                'updated_at' => $saleData['created_at'],
            ]);

            foreach ($itemsList as $it) {
                $it['sale_id'] = $sale->id;
                SaleItem::create($it);
            }
        }

        // 7. Update branch invoice & purchase counters
        if ($branch1) {
            $branch1->update(['invoice_counter' => 3, 'purchase_counter' => 2]);
        }
        if ($branch2) {
            $branch2->update(['invoice_counter' => 5, 'purchase_counter' => 3]);
        }
        if ($branch3) {
            $branch3->update(['invoice_counter' => 6, 'purchase_counter' => 3]);
        }

        $this->command->info('✅ Dummy data successfully seeded!');
        $this->command->info('   - Categories, Brands, Units, Warehouses');
        $this->command->info('   - Products & Stocks (Branch + Warehouse)');
        $this->command->info('   - Vendors & Suppliers');
        $this->command->info('   - Sales Officers');
        $this->command->info('   - Purchases with line items');
        $this->command->info('   - Sales with line items');
    }
}
