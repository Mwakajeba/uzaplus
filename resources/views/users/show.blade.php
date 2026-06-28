@extends('layouts.main')

@section('title', 'User Profile')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'User Management', 'url' => route('users.index'), 'icon' => 'bx bx-user'],
            ['label' => $user->name, 'url' => '#', 'icon' => 'bx bx-user-circle']
        ]" />

            <h6 class="mb-0 text-uppercase">USER PROFILE</h6>
            <hr />
            <div class="row">
                <!-- Profile Card -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="avatar-lg mx-auto mb-4">
                                <img
                                    src="{{ $user->photo ? asset('storage/' . $user->photo) : asset('assets/images/avatars/default.png') }}"
                                    alt="{{ $user->name }}"
                                    class="rounded-circle p-1 bg-primary"
                                    width="110" />
                            </div>
                                <h5 class="font-size-16 mb-1 text-truncate">{{ $user->name }}</h5>
                                <p class="text-muted text-truncate mb-3">{{ $user->email ?? 'No email' }}</p>

                                <!-- Status Badge -->
                                @if($user->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($user->status === 'inactive')
                                    <span class="badge bg-warning">Inactive</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                            </div>

                            <hr class="my-4">

                            <div class="text-muted">
                                <div class="table-responsive">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                           <tr>
                                                <th scope="row">User ID :</th>
                                                <td>{{ $user->user_id }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Phone :</th>
                                                <td>{{ $user->phone }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Branches :</th>
                                                <td>
                                                    @if($user->branches && $user->branches->count() > 0)
                                                        @foreach($user->branches as $branch)
                                                            <span class="badge bg-primary me-1 mb-1">{{ $branch->name }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Companies :</th>
                                                <td>
                                                    @if($user->companies && $user->companies->count() > 0)
                                                        @foreach($user->companies as $company)
                                                            <span class="badge {{ $company->pivot->is_default ? 'bg-success' : 'bg-primary' }} me-1 mb-1">
                                                                {{ $company->name }}
                                                                @if($company->pivot->is_default)
                                                                    <small>(Primary)</small>
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">{{ $user->company->name ?? 'N/A' }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Joined :</th>
                                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Last Updated :</th>
                                                <td>{{ $user->updated_at->format('M d, Y') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                @can('edit user')
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                    <i class="bx bx-edit me-1"></i> Edit User
                                </a>
                                @endcan

                                @if($user->status === 'active')
                                    @can('edit user')
                                    <form action="{{ route('users.status', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-warning w-100">
                                            <i class="bx bx-pause-circle me-1"></i> Deactivate
                                        </button>
                                    </form>
                                    @endcan
                                @else
                                    @can('edit user')
                                    <form action="{{ route('users.status', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="bx bx-play-circle me-1"></i> Activate
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                @can('delete user')
                                    @if($user->id !== auth()->id())
                                    <button type="button" class="btn btn-danger w-100 delete-user-btn" 
                                            data-user-id="{{ $user->id }}" 
                                            data-user-name="{{ $user->name }}">
                                        <i class="bx bx-trash me-1"></i> Delete User
                                    </button>
                                    @endif
                                @endcan
                            </div>

                            @can('edit user')
                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignCompanyModal">
                                    <i class="bx bx-building"></i> Assign/View Companies
                                </button>
                            </div>
                            @endcan

                            @can('assign branches')
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#assignBranchModal">
                                    <i class="bx bx-git-branch"></i> Assign/View Branch
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#assignLocationModal">
                                    <i class="bx bx-buildings"></i> Assign/View Locations
                                </button>
                            </div>
                            @endcan

                            <!-- Assign Company Modal -->
                            @can('edit user')
                            <div class="modal fade" id="assignCompanyModal" tabindex="-1" aria-labelledby="assignCompanyModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form id="assignCompanyForm" action="{{ route('users.assign-companies', $user) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="assignCompanyModalLabel">Assign Companies</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                @php
                                                    $assignedCompanyIds = $user->companies->pluck('id')->toArray();
                                                    $modalPrimaryCompanyId = $user->companies->firstWhere('pivot.is_default', true)?->id ?? $user->company_id;
                                                @endphp
                                                <div class="mb-3">
                                                    <label for="modal_companies" class="form-label fw-bold">Companies user can access</label>
                                                    <select class="form-select select2-modal-companies" id="modal_companies" name="companies[]" multiple required>
                                                        @foreach($companies as $company)
                                                            <option value="{{ $company->id }}"
                                                                {{ in_array($company->id, $assignedCompanyIds) ? 'selected' : '' }}>
                                                                {{ $company->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label for="modal_primary_company_id" class="form-label fw-bold">Primary company</label>
                                                    <select class="form-select select2-modal-primary" id="modal_primary_company_id" name="primary_company_id" required>
                                                        @foreach($companies as $company)
                                                            @if(in_array($company->id, $assignedCompanyIds))
                                                                <option value="{{ $company->id }}"
                                                                    {{ (string) $modalPrimaryCompanyId === (string) $company->id ? 'selected' : '' }}>
                                                                    {{ $company->name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary" id="assignCompanyBtn">
                                                    <i class="bx bx-save"></i> Save
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            <!-- Assign Branch Modal -->
                            <div class="modal fade" id="assignBranchModal" tabindex="-1" aria-labelledby="assignBranchModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form id="assignBranchForm" action="{{ route('users.assign-branches', $user->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="assignBranchModalLabel">Assign Branches</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-2 fw-bold">Select branches user can access:</div>
                                                @foreach(App\Models\Branch::all() as $branch)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="branches[]" value="{{ $branch->id }}"
                                                            id="branch_{{ $branch->id }}"
                                                            {{ $user->branches && $user->branches->contains($branch->id) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="branch_{{ $branch->id }}">
                                                            {{ $branch->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary" id="assignBranchBtn">
                                                    <i class="bx bx-save"></i> Save
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Assign Location Modal -->
                        <div class="modal fade" id="assignLocationModal" tabindex="-1" aria-labelledby="assignLocationModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form id="assignLocationForm" action="{{ route('users.assign-locations', $user->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="assignLocationModalLabel">Assign Locations</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @php $defaultLocationId = optional($user->locations->firstWhere('pivot.is_default', true))->id ?? null; @endphp
                                            <div class="mb-2 fw-bold">Select locations user can access:</div>
                                            <div class="row">
                                                <div class="col-12">
                                                    @php
                                                        $availableLocations = App\Models\InventoryLocation::where('company_id', current_company_id())->orderBy('name')->get();
                                                    @endphp
                                                    @if($availableLocations->count() > 0)
                                                        @foreach($availableLocations as $location)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="locations[]" value="{{ $location->id }}"
                                                                id="loc_{{ $location->id }}"
                                                                {{ $user->locations && $user->locations->contains($location->id) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="loc_{{ $location->id }}">
                                                                {{ $location->name }} @if($location->branch) <small class="text-muted">({{ $location->branch->name }})</small>@endif
                                                            </label>
                                                        </div>
                                                        @endforeach
                                                    @else
                                                        <div class="alert alert-warning">
                                                            <i class="bx bx-info-circle me-2"></i>
                                                            No locations available for this company. Please create locations first.
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary" id="assignLocationBtn">
                                                <i class="bx bx-save"></i> Save
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Details -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">User Information</h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Full Name</label>
                                        <p class="text-muted mb-0">{{ $user->name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email Address</label>
                                        <p class="text-muted mb-0">{{ $user->email ?? 'Not provided' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Phone Number</label>
                                        <p class="text-muted mb-0">{{ $user->phone }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Branches</label>
                                        <div>
                                            @if($user->branches && $user->branches->count() > 0)
                                                @foreach($user->branches as $branch)
                                                    <span class="badge bg-primary me-1 mb-1">{{ $branch->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Companies</label>
                                        <div>
                                            @if($user->companies && $user->companies->count() > 0)
                                                @foreach($user->companies as $company)
                                                    <span class="badge {{ $company->pivot->is_default ? 'bg-success' : 'bg-primary' }} me-1 mb-1">
                                                        {{ $company->name }}
                                                        @if($company->pivot->is_default)
                                                            (Primary)
                                                        @endif
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">{{ $user->company->name ?? 'Not assigned' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status</label>
                                        <p class="mb-0">
                                            @if($user->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($user->status === 'inactive')
                                                <span class="badge bg-warning">Inactive</span>
                                            @else
                                                <span class="badge bg-danger">Suspended</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Account Created</label>
                                        <p class="text-muted mb-0">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Roles & Permissions -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Roles & Permissions</h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-3">Assigned Roles</h6>
                                    @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-primary me-2">{{ $role->name }}</span>
                                                <small class="text-muted">{{ $role->permissions->count() }} permissions</small>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No roles assigned</p>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <h6 class="mb-3">Direct Permissions</h6>
                                    @if($user->permissions->count() > 0)
                                        @foreach($user->permissions as $permission)
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-info me-2">{{ $permission->name }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No direct permissions</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    <footer class="page-footer">
        <p class="mb-0">Copyright © {{ date('Y') }}. All right reserved. -- By SAFCO FINTECH</p>
    </footer>
@endsection

@push('scripts')
@include('users._company_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
$(function() {
    // Initialize any necessary scripts
});

// Delete user functionality with SweetAlert
$(document).on('click', '.delete-user-btn', function(e) {
    e.preventDefault();
    const userId = $(this).data('user-id');
    const userName = $(this).data('user-name');
    
    Swal.fire({
        title: 'Delete User',
        text: `Are you sure you want to delete user "${userName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            submitDeleteUserForm(userId);
        }
    });
});

// Helper function to submit delete user form
function submitDeleteUserForm(userId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/users/${userId}`;
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add method override
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);
    
    // Submit the form
    document.body.appendChild(form);
    form.submit();
}

const assignCompanyForm = document.getElementById('assignCompanyForm');
const assignCompanyModal = document.getElementById('assignCompanyModal');
let modalCompanySelect2Ready = false;

if (assignCompanyModal) {
    assignCompanyModal.addEventListener('shown.bs.modal', function() {
        if (!modalCompanySelect2Ready) {
            initUserCompanySelect2({
                companiesId: 'modal_companies',
                primaryId: 'modal_primary_company_id',
                dropdownParent: '#assignCompanyModal'
            });
            modalCompanySelect2Ready = true;
        }
    });
}

if (assignCompanyForm) {
    assignCompanyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const btn = document.getElementById('assignCompanyBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Saving...';
        btn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Companies Assigned!',
                    text: data.message || 'Company access updated.',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => window.location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to assign companies.'
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Network or server error.'
            });
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}

document.getElementById('assignBranchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('assignBranchBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Saving...';
    btn.disabled = true;

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Branches Assigned!',
                text: data.message || 'Branch access updated.',
                timer: 2000,
                timerProgressBar: true
            });
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('assignBranchModal'));
            modalInstance.hide();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to assign branches.'
            });
        }
    })
    .catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network or server error.'
        });
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

// Assign locations via AJAX
document.getElementById('assignLocationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('assignLocationBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Saving...';
    btn.disabled = true;

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Locations Assigned!',
                text: data.message || 'Location access updated.',
                timer: 2000,
                timerProgressBar: true
            });
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('assignLocationModal'));
            modalInstance && modalInstance.hide();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to assign locations.'
            });
        }
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Network or server error.' });
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>
@endpush