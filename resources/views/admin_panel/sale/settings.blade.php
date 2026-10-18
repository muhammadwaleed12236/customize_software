@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            
            {{-- HEADER BANNER --}}
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div class="card-body p-4 text-white d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-1 rounded-pill mb-2">
                            <i class="fas fa-sliders-h me-1"></i> POS Configuration
                        </span>
                        <h3 class="fw-bold mb-1"><i class="fas fa-cog text-warning me-2"></i>Sale Screen Customization Settings</h3>
                        <p class="text-white-50 mb-0 small">
                            Choose which features, fields, and warehouse modes to display on the Sale Creation screen (<code class="text-warning">/sale/create</code>).
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('sale.add') }}" class="btn btn-outline-light btn-sm rounded-pill px-3 shadow-sm">
                            <i class="fas fa-arrow-left me-1"></i> Go to Sale Screen
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                    <i class="fas fa-check-circle me-2 fs-5 align-middle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('sale.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- CARD 1: VISIBLE COMPONENTS --}}
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-eye text-primary"></i> Visible Screen Components
                        </h5>
                        <small class="text-muted">Disable or enable columns and options according to your business workflow.</small>
                    </div>

                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-3">

                            {{-- TOGGLE 1: GST / TAX --}}
                            <div class="p-3 rounded-3 border bg-light-subtle d-flex align-items-center justify-content-between flex-wrap gap-3 hover-shadow transition-all">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="bg-success-subtle text-success p-3 rounded-3 fs-4">
                                        <i class="fas fa-percent"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark">1. GST / Tax Column</h6>
                                        <p class="text-muted small mb-0">
                                            Enable or disable the <strong>GST %</strong> column on each product row and in the Order Summary.
                                        </p>
                                    </div>
                                </div>
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input style-switch" type="checkbox" name="show_gst" id="show_gst" {{ $settings->show_gst ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="show_gst">
                                        <span class="badge {{ $settings->show_gst ? 'bg-success' : 'bg-secondary' }}" id="badge_gst">
                                            {{ $settings->show_gst ? 'Show GST' : 'Hidden' }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            {{-- TOGGLE 2: LINE DISCOUNT --}}
                            <div class="p-3 rounded-3 border bg-light-subtle d-flex align-items-center justify-content-between flex-wrap gap-3 hover-shadow transition-all">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-4">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark">2. Line Discount (Per Item Discount)</h6>
                                        <p class="text-muted small mb-0">
                                            Enable or disable individual item discount inputs (<strong>DISCOUNT % / PKR</strong> column) on sale rows.
                                        </p>
                                    </div>
                                </div>
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input style-switch" type="checkbox" name="show_line_discount" id="show_line_discount" {{ $settings->show_line_discount ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="show_line_discount">
                                        <span class="badge {{ $settings->show_line_discount ? 'bg-primary' : 'bg-secondary' }}" id="badge_line_disc">
                                            {{ $settings->show_line_discount ? 'Show Line Discount' : 'Hidden' }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            {{-- TOGGLE 3: OVERALL DISCOUNT --}}
                            <div class="p-3 rounded-3 border bg-light-subtle d-flex align-items-center justify-content-between flex-wrap gap-3 hover-shadow transition-all">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="bg-warning-subtle text-warning p-3 rounded-3 fs-4">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark">3. Overall / Additional Discount</h6>
                                        <p class="text-muted small mb-0">
                                            Enable or disable total invoice level discount (<strong>Additional Disc</strong> field under Payment card).
                                        </p>
                                    </div>
                                </div>
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input style-switch" type="checkbox" name="show_overall_discount" id="show_overall_discount" {{ $settings->show_overall_discount ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="show_overall_discount">
                                        <span class="badge {{ $settings->show_overall_discount ? 'bg-warning text-dark' : 'bg-secondary' }}" id="badge_overall_disc">
                                            {{ $settings->show_overall_discount ? 'Show Overall Discount' : 'Hidden' }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            {{-- TOGGLE 4: WATT (SOLAR / POWER RATING) --}}
                            <div class="p-3 rounded-3 border bg-light-subtle d-flex align-items-center justify-content-between flex-wrap gap-3 hover-shadow transition-all">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="bg-info-subtle text-info p-3 rounded-3 fs-4">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark">4. Watt (Power / Solar Panel Rating)</h6>
                                        <p class="text-muted small mb-0">
                                            Enable or disable the <strong>WATT</strong> column on each product row. When enabled, calculation supports solar rate (<em>Watt &times; Price &times; Qty</em>).
                                        </p>
                                    </div>
                                </div>
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input style-switch" type="checkbox" name="show_watt" id="show_watt" {{ $settings->show_watt ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="show_watt">
                                        <span class="badge {{ $settings->show_watt ? 'bg-info text-dark' : 'bg-secondary' }}" id="badge_watt">
                                            {{ $settings->show_watt ? 'Show Watt' : 'Hidden' }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- CARD 2: WAREHOUSE SELECTION MODE --}}
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-warehouse text-primary"></i> Warehouse Selection Mode
                        </h5>
                        <small class="text-muted">Choose whether warehouse/godown is selected globally for the invoice or separately per item row.</small>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            {{-- OPTION 1: GLOBAL WAREHOUSE --}}
                            <div class="col-md-6">
                                <label class="mode-card p-3 rounded-3 border d-block cursor-pointer position-relative h-100 {{ ($settings->warehouse_mode ?? 'global') === 'global' ? 'active-mode' : '' }}" for="mode_global">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <input class="form-check-input mt-0 mode-radio" type="radio" name="warehouse_mode" id="mode_global" value="global" {{ ($settings->warehouse_mode ?? 'global') === 'global' ? 'checked' : '' }}>
                                        <span class="fs-5 text-primary"><i class="fas fa-globe"></i></span>
                                        <h6 class="fw-bold mb-0 text-dark">Global Warehouse</h6>
                                    </div>
                                    <p class="small text-muted mb-0 ps-4">
                                        Header mein aik single <strong>Location / Warehouse</strong> dropdown show hoga jo poori sale invoice ke tamam items par apply hoga.
                                    </p>
                                </label>
                            </div>

                            {{-- OPTION 2: PRODUCT-WISE WAREHOUSE --}}
                            <div class="col-md-6">
                                <label class="mode-card p-3 rounded-3 border d-block cursor-pointer position-relative h-100 {{ ($settings->warehouse_mode ?? 'global') === 'product_wise' ? 'active-mode' : '' }}" for="mode_product_wise">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <input class="form-check-input mt-0 mode-radio" type="radio" name="warehouse_mode" id="mode_product_wise" value="product_wise" {{ ($settings->warehouse_mode ?? 'global') === 'product_wise' ? 'checked' : '' }}>
                                        <span class="fs-5 text-success"><i class="fas fa-boxes"></i></span>
                                        <h6 class="fw-bold mb-0 text-dark">Product-Wise Warehouse</h6>
                                    </div>
                                    <p class="small text-muted mb-0 ps-4">
                                        Har product line ke sath table mein <strong>Warehouse</strong> column display hoga (<code class="text-primary">Product | Warehouse | Qty</code>). User har item ka alag godown select kar sakega.
                                    </p>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: A4 INVOICE PRINT & DESIGN CUSTOMIZATION --}}
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-print text-danger"></i> A4 Invoice Print & Design Customization
                            </h5>
                            <small class="text-muted">Logo, Contact Numbers, Colors, Branch Addresses, aur Terms ko A4 invoice ke liye customize karein.</small>
                        </div>
                        <span class="badge bg-danger-subtle text-danger fw-bold px-3 py-1 rounded-pill">
                            <i class="fas fa-file-invoice me-1"></i> A4 Template
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">

                            {{-- 1. LOGO & COMPANY NAME --}}
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small mb-1">Company / Invoice Logo</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="border rounded-3 p-2 bg-light text-center" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        @if($settings->invoice_logo && file_exists(public_path($settings->invoice_logo)))
                                            <img src="{{ asset($settings->invoice_logo) }}" alt="Logo" id="logo_preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                        @else
                                            <span class="text-danger fw-extrabold fs-4" id="logo_preview_text">{{ $settings->invoice_company_name ?? 'GLC' }}</span>
                                            <img src="" alt="Logo" id="logo_preview" class="d-none" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="file" name="invoice_logo" id="invoice_logo_input" class="form-control form-control-sm" accept="image/*">
                                        <div class="form-text small mt-1">PNG, JPG or SVG. Transparent PNG recommended.</div>
                                        @if($settings->invoice_logo)
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox" name="remove_invoice_logo" value="1" id="remove_invoice_logo">
                                                <label class="form-check-label text-danger small" for="remove_invoice_logo">
                                                    Remove Logo
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small mb-1">Company Name</label>
                                <input type="text" name="invoice_company_name" class="form-control" value="{{ old('invoice_company_name', $settings->invoice_company_name ?? 'GLC') }}" placeholder="e.g. GLC">
                                <div class="form-text small">Center main show hone wala Main Name</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small mb-1">Tagline / Sub-Name</label>
                                <input type="text" name="invoice_tagline" class="form-control" value="{{ old('invoice_tagline', $settings->invoice_tagline ?? 'Long Life Accessories') }}" placeholder="e.g. Long Life Accessories">
                                <div class="form-text small">Logo ke bilkul neechay ka tagline</div>
                            </div>

                            {{-- 2. DEALING ITEMS / SUB DESCRIPTION --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small mb-1">Header Categories / Deals In (Tagline Description)</label>
                                <textarea name="invoice_sub_description" rows="2" class="form-control" placeholder="e.g. Power Bank, Battery, Data Cable, Charger, Head Phone Handsfree, Bluetooth & Multimedia">{{ old('invoice_sub_description', $settings->invoice_sub_description ?? "Power Bank, Battery, Data Cable, Charger, Head Phone\nHandsfree, Bluetooth & Multimedia") }}</textarea>
                            </div>

                            {{-- 3. LEFT & RIGHT CONTACTS --}}
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border bg-light-subtle h-100">
                                    <label class="form-label fw-bold text-dark small mb-1 d-flex align-items-center gap-2">
                                        <i class="fas fa-user text-primary"></i> Left Header Contacts (Names & Numbers)
                                    </label>
                                    <textarea name="invoice_contacts_left" rows="5" class="form-control font-monospace small" placeholder="Faisal Mehmood&#10;0325 4682466&#10;Furqaan Shakeel&#10;03044576200">{{ old('invoice_contacts_left', $settings->invoice_contacts_left ?? "Faisal Mehmood\n0325 4682466\nFurqaan Shakeel\n03044576200") }}</textarea>
                                    <div class="form-text small">Har line par Name aur Number enter karein.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border bg-light-subtle h-100">
                                    <label class="form-label fw-bold text-dark small mb-1 d-flex align-items-center gap-2">
                                        <i class="fas fa-phone-alt text-success"></i> Right Header Contacts (Names, Numbers & PTCL)
                                    </label>
                                    <textarea name="invoice_contacts_right" rows="5" class="form-control font-monospace small" placeholder="Anjum Shakeel&#10;03008033184&#10;Tayyab Shakeel&#10;03054453139&#10;PTCL #: 042 37322272&#10;042 37232370">{{ old('invoice_contacts_right', $settings->invoice_contacts_right ?? "Anjum Shakeel\n03008033184\nTayyab Shakeel\n03054453139\nPTCL #: 042 37322272\n042 37232370") }}</textarea>
                                    <div class="form-text small">Right side ke contacts aur telephone numbers.</div>
                                </div>
                            </div>

                            {{-- 4. COLORS & ROWS SETUP --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small mb-1">Invoice Theme / Accent Color</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" id="invoice_primary_color_picker" class="form-control form-control-color" value="{{ old('invoice_primary_color', $settings->invoice_primary_color ?? '#c23321') }}" title="Choose Theme Color" oninput="document.getElementById('invoice_primary_color').value=this.value">
                                    <input type="text" name="invoice_primary_color" id="invoice_primary_color" class="form-control font-monospace" value="{{ old('invoice_primary_color', $settings->invoice_primary_color ?? '#c23321') }}" oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) document.getElementById('invoice_primary_color_picker').value=this.value">
                                </div>
                                <div class="form-text small">Table borders aur footer banner ka color (Default: #c23321)</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small mb-1">Text Color (Default: Black)</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" id="invoice_text_color_picker" class="form-control form-control-color" value="{{ old('invoice_text_color', $settings->invoice_text_color ?? '#000000') }}" title="Choose Text Color" oninput="document.getElementById('invoice_text_color').value=this.value">
                                    <input type="text" name="invoice_text_color" id="invoice_text_color" class="form-control font-monospace" value="{{ old('invoice_text_color', $settings->invoice_text_color ?? '#000000') }}" oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) document.getElementById('invoice_text_color_picker').value=this.value">
                                </div>
                                <div class="form-text small">Body aur invoice text color (Default: Black #000000)</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small mb-1">Min Table Rows (A4 Height)</label>
                                <select name="invoice_min_rows" class="form-select">
                                    @foreach([12, 14, 15, 16, 18, 20] as $r)
                                        <option value="{{ $r }}" {{ ($settings->invoice_min_rows ?? 16) == $r ? 'selected' : '' }}>{{ $r }} Rows (Recommended: 16)</option>
                                    @endforeach
                                </select>
                                <div class="form-text small">Table ko A4 page par full height rakhne ke liye khali lines</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small mb-1">Watermark Logo in Table</label>
                                <div class="form-check form-switch form-switch-md mt-2">
                                    <input class="form-check-input" type="checkbox" name="invoice_show_watermark" id="invoice_show_watermark" {{ ($settings->invoice_show_watermark ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="invoice_show_watermark">
                                        Show Background Watermark
                                    </label>
                                </div>
                            </div>

                            {{-- 5. TERMS / NOTE --}}
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-dark small mb-1 d-flex align-items-center gap-2">
                                    <i class="fas fa-sticky-note text-warning"></i> NOTE / Return Policy Terms
                                </label>
                                <textarea name="invoice_terms_notes" rows="3" class="form-control" placeholder="1. No Return no Exchange without bill&#10;2. Change of Mind is not accepted&#10;3. Damaged or Burned not Accepted">{{ old('invoice_terms_notes', $settings->invoice_terms_notes ?? "1. No Return no Exchange without bill\n2. Change of Mind is not accepted\n3. Damaged or Burned not Accepted") }}</textarea>
                                <div class="form-text small">Invoice ke bottom-left par print hone wali sharaait.</div>
                            </div>

                            {{-- 6. FOOTER BRANCHES --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small mb-1">Branch #1 Address (Footer Banner)</label>
                                <input type="text" name="invoice_branch_1" class="form-control" value="{{ old('invoice_branch_1', $settings->invoice_branch_1 ?? 'Branch#1: Shop #22,45, Basement Madina Center The Mall Hall Road, Lhr.') }}" placeholder="Branch#1: Address...">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small mb-1">Branch #2 Address (Footer Banner)</label>
                                <input type="text" name="invoice_branch_2" class="form-control" value="{{ old('invoice_branch_2', $settings->invoice_branch_2 ?? 'Branch#2: Shop #40,43, 3rd Floor Hassan Center Hall Road, Lhr.') }}" placeholder="Branch#2: Address...">
                            </div>

                            {{-- 7. POWERED BY / FOOTER CREDIT --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small mb-1">Footer Powered By / Software Credit</label>
                                <input type="text" name="invoice_footer_credit" class="form-control" value="{{ old('invoice_footer_credit', $settings->invoice_footer_credit ?? 'Powered by Prowave Technologies 📞 +92 317 3836 223') }}" placeholder="Powered by...">
                            </div>

                        </div>
                    </div>

                    <div class="card-footer bg-white p-3 px-4 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('sale.add') }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i> Save All Settings
                        </button>
                    </div>
                </div>
            </form>

            {{-- LIVE PREVIEW SUMMARY --}}
            <div class="card border-0 rounded-4 shadow-sm bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-info-circle text-info me-1"></i> Preview Summary</h6>
                    <p class="small text-muted mb-0">
                        In settings ko save karne par Sale Screen (<a href="{{ route('sale.add') }}" class="fw-bold text-decoration-none">/sale/create</a>) par selected columns aur warehouse controls instant update ho jayenge.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .form-switch-md .form-check-input {
        width: 3em;
        height: 1.6em;
        cursor: pointer;
    }
    .hover-shadow {
        transition: all 0.2s ease-in-out;
    }
    .hover-shadow:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        background-color: #ffffff !important;
    }
    .mode-card {
        transition: all 0.2s ease-in-out;
        background: #f8fafc;
        border-color: #e2e8f0 !important;
    }
    .mode-card:hover {
        background: #ffffff;
        border-color: #cbd5e1 !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.04);
    }
    .mode-card.active-mode {
        background: #f0f7ff;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 1px #3b82f6;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const gstSwitch = document.getElementById('show_gst');
        const badgeGst = document.getElementById('badge_gst');
        gstSwitch.addEventListener('change', function() {
            badgeGst.className = this.checked ? 'badge bg-success' : 'badge bg-secondary';
            badgeGst.textContent = this.checked ? 'Show GST' : 'Hidden';
        });

        const lineSwitch = document.getElementById('show_line_discount');
        const badgeLine = document.getElementById('badge_line_disc');
        lineSwitch.addEventListener('change', function() {
            badgeLine.className = this.checked ? 'badge bg-primary' : 'badge bg-secondary';
            badgeLine.textContent = this.checked ? 'Show Line Discount' : 'Hidden';
        });

        const overallSwitch = document.getElementById('show_overall_discount');
        const badgeOverall = document.getElementById('badge_overall_disc');
        overallSwitch.addEventListener('change', function() {
            badgeOverall.className = this.checked ? 'badge bg-warning text-dark' : 'badge bg-secondary';
            badgeOverall.textContent = this.checked ? 'Show Overall Discount' : 'Hidden';
        });

        const wattSwitch = document.getElementById('show_watt');
        const badgeWatt = document.getElementById('badge_watt');
        wattSwitch.addEventListener('change', function() {
            badgeWatt.className = this.checked ? 'badge bg-info text-dark' : 'badge bg-secondary';
            badgeWatt.textContent = this.checked ? 'Show Watt' : 'Hidden';
        });

        // Logo image preview
        const logoInput = document.getElementById('invoice_logo_input');
        const logoPreview = document.getElementById('logo_preview');
        const logoPreviewText = document.getElementById('logo_preview_text');
        if (logoInput && logoPreview) {
            logoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        logoPreview.src = e.target.result;
                        logoPreview.classList.remove('d-none');
                        if (logoPreviewText) logoPreviewText.classList.add('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endsection
