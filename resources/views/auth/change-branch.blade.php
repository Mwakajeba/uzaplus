@extends('layouts.auth')

@section('title', 'SmartAccounting – Select Company & Branch')

@section('content')
    <div class="authentication-header"></div>
    <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
        <div class="container">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                <div class="col mx-auto">
                    <div class="mb-4 text-center">
                        <img src="{{ asset('assets/images/logo1.png') }}" width="180" alt="" />
                    </div>
                    <div class="card rounded-4 shadow-lg">
                        <div class="card-body">
                            <div class="p-4 rounded">
                                <div class="text-center mb-3">
                                    <img src="{{ asset('assets/images/icons/lock.png')}}" width="120" alt="" />
                                </div>
                                <div class="login-separater text-center mb-4">
                                    <span class="fw-bold fs-5">Select Company & Branch</span>
                                    <hr />
                                </div>

                                @if (session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                @if(isset($companies) && $companies->count() > 0)
                                <form method="POST" action="{{ route('change-branch.submit') }}" id="contextForm">
                                    @csrf

                                    <div class="form-group mb-4">
                                        <label for="company_id" class="form-label fw-semibold">Company</label>
                                        <select name="company_id" id="company_id" class="form-control rounded-pill px-3 py-2" required>
                                            <option value="">Choose company...</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}"
                                                    {{ (int) ($selectedCompanyId ?? 0) === (int) $company->id ? 'selected' : '' }}>
                                                    {{ $company->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label for="branch_id" class="form-label fw-semibold">Branch</label>
                                        <select name="branch_id" id="branch_id" class="form-control rounded-pill px-3 py-2" required>
                                            <option value="">Choose branch...</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">
                                                    {{ $branch->branch_name ?: $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Only branches assigned to you for the selected company are listed.</small>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label for="location_id" class="form-label fw-semibold">Location (optional)</label>
                                        <select name="location_id" id="location_id" class="form-control rounded-pill px-3 py-2" disabled>
                                            <option value="">Select branch first</option>
                                            @foreach($locations as $location)
                                                <option value="{{ $location->id }}" data-branch="{{ $location->branch_id }}">
                                                    {{ $location->name }} ({{ optional($location->branch)->name }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Locations are filtered by company and branch.</small>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill mt-2">Continue</button>
                                </form>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        No company has been assigned to your account. Please contact your administrator.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function() {
    const companySelect = document.getElementById('company_id');
    const branchSelect = document.getElementById('branch_id');
    const locationSelect = document.getElementById('location_id');

    if (!companySelect || !branchSelect) {
        return;
    }

    function loadBranches() {
        const companyId = companySelect.value;

        branchSelect.innerHTML = '<option value="">Choose branch...</option>';
        branchSelect.disabled = true;

        if (locationSelect) {
            locationSelect.innerHTML = '<option value="">Select branch first</option>';
            locationSelect.disabled = true;
        }

        if (!companyId) {
            return;
        }

        branchSelect.innerHTML = '<option value="">Loading...</option>';

        fetch("{{ route('change-branch.branches') }}?company_id=" + encodeURIComponent(companyId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            method: 'GET'
        })
            .then(function(res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function(data) {
                branchSelect.innerHTML = '<option value="">Choose branch...</option>';
                data.forEach(function(branch) {
                    const opt = document.createElement('option');
                    opt.value = branch.id;
                    opt.textContent = branch.name;
                    branchSelect.appendChild(opt);
                });
                branchSelect.disabled = data.length === 0;
            })
            .catch(function() {
                branchSelect.innerHTML = '<option value="">Failed to load branches</option>';
            });
    }

    function loadLocations() {
        if (!locationSelect) return;

        const branchId = branchSelect.value;
        const companyId = companySelect.value;

        locationSelect.innerHTML = '<option value="">Select branch first</option>';
        locationSelect.disabled = true;

        if (!branchId || !companyId) {
            return;
        }

        locationSelect.innerHTML = '<option value="">Loading...</option>';

        fetch("{{ route('change-branch.locations') }}?branch_id=" + encodeURIComponent(branchId) + "&company_id=" + encodeURIComponent(companyId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            method: 'GET'
        })
            .then(function(res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function(data) {
                locationSelect.innerHTML = '<option value="">No location (optional)</option>';
                data.forEach(function(loc) {
                    const opt = document.createElement('option');
                    opt.value = loc.id;
                    opt.textContent = loc.name;
                    locationSelect.appendChild(opt);
                });
                locationSelect.disabled = false;
            })
            .catch(function() {
                locationSelect.innerHTML = '<option value="">Failed to load</option>';
            });
    }

    companySelect.addEventListener('change', loadBranches);
    branchSelect.addEventListener('change', loadLocations);

    if (companySelect.value && branchSelect.options.length <= 1) {
        loadBranches();
    }
});
</script>
@endpush
