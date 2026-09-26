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
                            Choose which features and fields to display on the Sale Creation screen (<code class="text-warning">/sale/create</code>).
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

            <form action="{{ route('sale.settings.update') }}" method="POST">
                @csrf

                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-eye text-primary"></i> Visible Screen Components
                        </h5>
                        <small class="text-muted">Disable options if your business does not use GST or discounts on invoices.</small>
                    </div>

                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-4">

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

                        </div>
                    </div>

                    <div class="card-footer bg-white p-3 px-4 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('sale.add') }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i> Save Settings
                        </button>
                    </div>
                </div>
            </form>

            {{-- LIVE PREVIEW SUMMARY --}}
            <div class="card border-0 rounded-4 shadow-sm bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-info-circle text-info me-1"></i> Preview Summary</h6>
                    <p class="small text-muted mb-0">
                        When options are unchecked, the corresponding input boxes, dropdowns, and header labels are automatically removed from the Sale Screen, simplifying your daily invoicing process.
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
    });
</script>
@endsection
