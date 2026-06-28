@extends('layouts.main')

@section('title', 'Company Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Company Settings', 'url' => route('settings.company'), 'icon' => 'bx bx-building'],
            ['label' => $company->name, 'url' => '#', 'icon' => 'bx bx-detail']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">Company Details: {{ $company->name }}</h4>
                            <div class="d-flex gap-2">
                                <a href="{{ route('settings.company.edit', $company) }}" class="btn btn-primary">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('settings.company') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Companies
                                </a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="card border-primary">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="bx bx-info-circle me-2"></i>Basic Information
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Company Name:</strong></div>
                                            <div class="col-sm-8">{{ $company->name }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Email:</strong></div>
                                            <div class="col-sm-8">{{ $company->email ?? '-' }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Phone:</strong></div>
                                            <div class="col-sm-8">{{ $company->phone ?? '-' }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>License Number:</strong></div>
                                            <div class="col-sm-8">{{ $company->license_number ?? '-' }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Registration Date:</strong></div>
                                            <div class="col-sm-8">
                                                {{ $company->registration_date ? \Carbon\Carbon::parse($company->registration_date)->format('d M Y') : '-' }}
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Functional Currency:</strong></div>
                                            <div class="col-sm-8">{{ $company->functional_currency ?? 'TZS' }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Status:</strong></div>
                                            <div class="col-sm-8">
                                                @switch($company->status)
                                                    @case('active')
                                                        <span class="badge bg-success">Active</span>
                                                        @break
                                                    @case('inactive')
                                                        <span class="badge bg-warning">Inactive</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-danger">Suspended</span>
                                                @endswitch
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Address:</strong></div>
                                            <div class="col-sm-8">{{ $company->address ?? '-' }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Branches:</strong></div>
                                            <div class="col-sm-8">{{ $company->branches_count }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Users:</strong></div>
                                            <div class="col-sm-8">{{ $company->users_count }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Created:</strong></div>
                                            <div class="col-sm-8">{{ $company->created_at?->format('d M Y, h:i A') ?? '-' }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>Last Updated:</strong></div>
                                            <div class="col-sm-8">{{ $company->updated_at?->format('d M Y, h:i A') ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="bx bx-image me-2"></i>Company Logo
                                        </h5>
                                    </div>
                                    <div class="card-body text-center">
                                        @if($company->logo)
                                            <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->name }}"
                                                 class="img-fluid rounded" style="max-height: 200px;">
                                        @else
                                            <p class="text-muted mb-0">No logo uploaded.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
