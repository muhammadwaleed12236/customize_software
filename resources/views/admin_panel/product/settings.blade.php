@extends('admin_panel.layout.app')

@section('content')
<style>
    .settings-container {
        max-width: 900px;
        margin: 20px auto;
    }
    .settings-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .settings-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #ffffff;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .settings-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -0.2px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .settings-body {
        padding: 24px;
    }
    .settings-intro {
        background: #f8fafc;
        border-left: 4px solid #2563eb;
        padding: 14px 18px;
        border-radius: 0 8px 8px 0;
        margin-bottom: 24px;
        color: #475569;
        font-size: 13px;
        line-height: 1.5;
    }
    .setting-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 18px;
        border-radius: 8px;
        border: 1px solid #edf2f7;
        margin-bottom: 12px;
        transition: all 0.2s ease;
        background: #fff;
    }
    .setting-item:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .setting-info {
        max-width: 75%;
    }
    .setting-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .setting-desc {
        font-size: 12px;
        color: #64748b;
        margin: 0;
    }
    .badge-primary-soft {
        background-color: #eff6ff;
        color: #2563eb;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 9999px;
        font-weight: 600;
    }
    
    /* Modern Toggle Switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 26px;
        flex-shrink: 0;
    }
    .switch input { 
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    input:checked + .slider {
        background-color: #2563eb;
    }
    input:focus + .slider {
        box-shadow: 0 0 1px #2563eb;
    }
    input:checked + .slider:before {
        transform: translateX(22px);
    }

    .save-bar {
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
</style>

<div class="settings-container">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 8px;">
            <i class="fas fa-check-circle me-2" style="font-size: 18px; margin-right: 8px;"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="close ms-auto" data-dismiss="alert" aria-label="Close" style="background: none; border: none; font-size: 20px; line-height: 1;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="settings-card">
        <div class="settings-header">
            <h4>
                <i class="fas fa-sliders-h text-primary"></i>
                Product Form Settings
            </h4>
            <a href="{{ route('product') }}" class="btn btn-sm btn-outline-light" style="border-radius: 6px; font-weight: 600;">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>

        <div class="settings-body">
            <div class="settings-intro">
                <strong><i class="fas fa-info-circle"></i> Client Customization Control:</strong><br>
                Enable or disable specific fields on the product creation (<code>/create_prodcut</code>) and edit screens. If a client does not require fields like <strong>Product Tracking Nature</strong> or <strong>Packaging Type</strong>, toggle them OFF below. Hidden fields will automatically adopt safe default values without affecting software operations.
            </div>

            <form action="{{ route('product.settings.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <h6 class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.8px; margin-bottom: 12px;">
                        Specialized Inventory Features
                    </h6>

                    <!-- Tracking Nature -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">
                                <i class="fas fa-barcode text-primary"></i>
                                Product Tracking Nature (Standard / Serial)
                                <span class="badge-primary-soft">Core Feature</span>
                            </div>
                            <p class="setting-desc">
                                Enables selecting between Standard product or Serial/IMEI tracking nature. If disabled, all products default seamlessly to <strong>Standard</strong>.
                            </p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_tracking_type" value="1" {{ $settings->show_tracking_type ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- Packaging Type -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">
                                <i class="fas fa-boxes text-info"></i>
                                Packaging Type & Customized Pack Fields
                                <span class="badge-primary-soft">Packaging</span>
                            </div>
                            <p class="setting-desc">
                                Enables selecting Packaging Type (Standard / Customize) and configuring Pack Qty, Unit/Pack, and Loose Pieces. If disabled, all products default to <strong>Standard</strong>.
                            </p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_packaging_type" value="1" {{ $settings->show_packaging_type ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="mt-4 mb-3">
                    <h6 class="text-uppercase text-muted font-weight-bold" style="font-size: 11px; letter-spacing: 0.8px; margin-bottom: 12px;">
                        General Product Attributes
                    </h6>

                    <!-- Item Name Urdu -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">
                                <i class="fas fa-language text-secondary"></i>
                                Item Name in Urdu
                            </div>
                            <p class="setting-desc">Shows Urdu name input field with automatic on-screen Urdu virtual keyboard.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_item_name_urdu" value="1" {{ $settings->show_item_name_urdu ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- Model -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">
                                <i class="fas fa-tag text-secondary"></i>
                                Model / Series
                            </div>
                            <p class="setting-desc">Shows Model / Series specification input field.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_model" value="1" {{ $settings->show_model ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- HS Code -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">
                                <i class="fas fa-file-invoice text-secondary"></i>
                                HS Code (Harmonized System Code)
                            </div>
                            <p class="setting-desc">Shows customs HS Code input field for taxation and exports.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_hs_code" value="1" {{ $settings->show_hs_code ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- Color -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">
                                <i class="fas fa-palette text-secondary"></i>
                                Color Selection / Tagging
                            </div>
                            <p class="setting-desc">Shows color selector field for single/variant products.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_color" value="1" {{ $settings->show_color ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- Type -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">
                                <i class="fas fa-sitemap text-secondary"></i>
                                Product Type Dropdown
                            </div>
                            <p class="setting-desc">Shows Type dropdown selector in classification row.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_type" value="1" {{ $settings->show_type ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- Brand -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">
                                <i class="fas fa-trademark text-secondary"></i>
                                Brand Dropdown
                            </div>
                            <p class="setting-desc">Shows Brand dropdown selector with quick brand addition modal.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_brand" value="1" {{ $settings->show_brand ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="save-bar">
                    <a href="{{ route('product') }}" class="btn btn-secondary px-4" style="border-radius: 6px; font-weight: 600;">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius: 6px; font-weight: 600; box-shadow: 0 2px 6px rgba(37,99,235,0.3);">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
