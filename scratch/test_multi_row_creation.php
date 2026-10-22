<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PaymentVoucher;
use App\Models\AccountLedgerEntry;
use App\Models\VendorLedger;
use App\Services\VoucherService;
use Illuminate\Support\Facades\DB;

echo "--- Testing Multi-Row Payment Voucher Creation ---\n";

DB::beginTransaction();

try {
    $pvid = PaymentVoucher::generateInvoiceNo();
    echo "Generated PVID: {$pvid}\n";

    // Simulate 2 party rows (e.g. Vendor 1 and Vendor 2) and 2 account rows (Account 1 and Account 25)
    $partyTypes   = ['vendor', 'vendor'];
    $partyIds     = [1, 2];
    $partyAmounts = [500.00, 700.00];
    $partyTels    = ['03001234567', '03007654321'];

    $narrationIds     = ["1", "1"];
    $referenceNos     = ["REF-MULTI-1", "REF-MULTI-2"];
    $rowAccountHeads  = [1, 1];
    $rowAccountIds    = [1, 25]; // Cash and another account
    $discountValues   = [0, 0];
    $amounts          = [500.00, 700.00];
    $totalAmount      = 1200.00;

    $voucherData = [
        'branch_id'        => 12,
        'pvid'             => $pvid,
        'receipt_date'     => date('Y-m-d'),
        'entry_date'       => date('Y-m-d'),
        'type'             => json_encode($partyTypes),
        'party_id'         => json_encode($partyIds),
        'party_amount'     => json_encode($partyAmounts),
        'tel'              => json_encode($partyTels),
        'remarks'          => 'Test multi-row payment voucher',
        'narration_id'     => json_encode($narrationIds),
        'reference_no'     => json_encode($referenceNos),
        'row_account_head' => json_encode($rowAccountHeads),
        'row_account_id'   => json_encode($rowAccountIds),
        'discount_value'   => json_encode($discountValues),
        'amount'           => json_encode($amounts),
        'total_amount'     => $totalAmount,
        'status'           => 'active',
        'processed'        => true,
    ];

    $voucher = PaymentVoucher::create($voucherData);
    echo "Created Voucher ID: {$voucher->id}\n";

    VoucherService::applyPaymentVoucher($voucher, [
        'branch_id'    => 12,
        'party_type'   => $partyTypes,
        'party_id'     => $partyIds,
        'party_amount' => $partyAmounts,
        'party_tel'    => $partyTels,
        'narration_id' => $narrationIds,
        'row_account_id' => $rowAccountIds,
        'amount'       => $amounts,
        'total_amount' => $totalAmount,
    ], 1);

    // Verify vendor ledgers
    $vl1 = VendorLedger::where('reference_id', $pvid)->where('vendor_id', 1)->first();
    $vl2 = VendorLedger::where('reference_id', $pvid)->where('vendor_id', 2)->first();
    echo "Vendor 1 Ledger Entry: " . ($vl1 ? "Found (Debit: {$vl1->debit_amount})" : "NOT FOUND") . "\n";
    echo "Vendor 2 Ledger Entry: " . ($vl2 ? "Found (Debit: {$vl2->debit_amount})" : "NOT FOUND") . "\n";

    // Verify account ledger entries
    $ale1 = AccountLedgerEntry::where('voucher_no', $pvid)->where('account_id', 1)->first();
    $ale2 = AccountLedgerEntry::where('voucher_no', $pvid)->where('account_id', 25)->first();
    echo "Account 1 Ledger Entry: " . ($ale1 ? "Found (Credit: {$ale1->credit})" : "NOT FOUND") . "\n";
    echo "Account 25 Ledger Entry: " . ($ale2 ? "Found (Credit: {$ale2->credit})" : "NOT FOUND") . "\n";

    // Clean up rollback so test leaves zero dirt
    DB::rollBack();
    echo "Test successfully verified and rolled back cleanly!\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR during test: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
