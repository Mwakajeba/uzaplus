@php
    $selectedCompanyIds = old('companies', isset($user)
        ? $user->companies->pluck('id')->toArray()
        : array_filter([current_company_id()]));
    $primaryCompanyId = old('primary_company_id', isset($user)
        ? ($user->companies->firstWhere('pivot.is_default', true)?->id ?? $user->company_id)
        : current_company_id());
@endphp

<div class="row">
    <div class="col-12">
        <div class="mb-3">
            <label for="companies" class="form-label">Companies <span class="text-danger">*</span></label>
            <select class="form-select select2-companies @error('companies') is-invalid @enderror"
                    id="companies" name="companies[]" multiple required>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}"
                        {{ in_array($company->id, $selectedCompanyIds) ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
            @error('companies')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Select one or more companies this user can access.</small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="primary_company_id" class="form-label">Primary Company <span class="text-danger">*</span></label>
            <select class="form-select select2-primary-company @error('primary_company_id') is-invalid @enderror"
                    id="primary_company_id" name="primary_company_id" required>
                <option value="">Select primary company</option>
                @foreach($companies as $company)
                    @if(in_array($company->id, $selectedCompanyIds))
                        <option value="{{ $company->id }}"
                            {{ (string) $primaryCompanyId === (string) $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endif
                @endforeach
            </select>
            @error('primary_company_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Default company used for login and scoping.</small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror"
                   id="name" name="name"
                   value="{{ old('name', isset($user) ? $user->name : '') }}"
                   placeholder="Enter full name" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email"
                   value="{{ old('email', isset($user) ? $user->email : '') }}"
                   placeholder="Enter email address" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                   id="phone" name="phone"
                   value="{{ old('phone', isset($user) ? $user->phone : '') }}"
                   placeholder="Enter phone number" required>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
            <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                <option value="">Select role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}"
                        {{ (string) old('role_id', isset($user) ? ($user->roles->first()->id ?? '') : '') === (string) $role->id ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
            @error('role_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="active" {{ old('status', isset($user) ? $user->status : 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', isset($user) ? $user->status : '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="password" class="form-label">
                Password
                @if(!isset($user))
                    <span class="text-danger">*</span>
                @else
                    <span class="text-muted">(Optional)</span>
                @endif
            </label>
            <div class="input-group">
                <input type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       placeholder="{{ isset($user) ? 'Leave blank to keep current password' : 'Enter password' }}"
                       {{ !isset($user) ? 'required' : '' }}>
                <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                    <i class="bx bx-show"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <small class="form-text text-muted">
                @if(isset($user))
                    Leave blank to keep the current password.
                @else
                    Minimum 6 characters.
                @endif
            </small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">
                Confirm Password
                @if(!isset($user))
                    <span class="text-danger">*</span>
                @endif
            </label>
            <div class="input-group">
                <input type="password"
                       class="form-control @error('password_confirmation') is-invalid @enderror"
                       id="password_confirmation"
                       name="password_confirmation"
                       placeholder="Confirm password"
                       {{ !isset($user) ? 'required' : '' }}>
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword" tabindex="-1">
                    <i class="bx bx-show"></i>
                </button>
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>
