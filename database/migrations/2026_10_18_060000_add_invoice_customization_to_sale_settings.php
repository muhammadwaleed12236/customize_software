<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sale_settings', function (Blueprint $table) {
            $table->string('invoice_logo')->nullable()->after('warehouse_mode');
            $table->string('invoice_company_name')->nullable()->default('GLC')->after('invoice_logo');
            $table->string('invoice_tagline')->nullable()->default('Long Life Accessories')->after('invoice_company_name');
            $table->text('invoice_sub_description')->nullable()->after('invoice_tagline');
            $table->text('invoice_contacts_left')->nullable()->after('invoice_sub_description');
            $table->text('invoice_contacts_right')->nullable()->after('invoice_contacts_left');
            $table->string('invoice_primary_color')->nullable()->default('#c23321')->after('invoice_contacts_right');
            $table->string('invoice_text_color')->nullable()->default('#000000')->after('invoice_primary_color');
            $table->text('invoice_terms_notes')->nullable()->after('invoice_text_color');
            $table->string('invoice_branch_1')->nullable()->after('invoice_terms_notes');
            $table->string('invoice_branch_2')->nullable()->after('invoice_branch_1');
            $table->string('invoice_footer_credit')->nullable()->after('invoice_branch_2');
            $table->integer('invoice_min_rows')->default(16)->after('invoice_footer_credit');
            $table->boolean('invoice_show_watermark')->default(true)->after('invoice_min_rows');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_settings', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_logo',
                'invoice_company_name',
                'invoice_tagline',
                'invoice_sub_description',
                'invoice_contacts_left',
                'invoice_contacts_right',
                'invoice_primary_color',
                'invoice_text_color',
                'invoice_terms_notes',
                'invoice_branch_1',
                'invoice_branch_2',
                'invoice_footer_credit',
                'invoice_min_rows',
                'invoice_show_watermark',
            ]);
        });
    }
};
