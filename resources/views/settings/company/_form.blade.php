<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="name" class="form-label">Company Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror"
                   id="name" name="name" value="{{ old('name', $company->name ?? '') }}"
                   placeholder="Enter company name" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email', $company->email ?? '') }}"
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
                   id="phone" name="phone" value="{{ old('phone', $company->phone ?? '') }}"
                   placeholder="Enter phone number" required>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="license_number" class="form-label">License Number</label>
            <input type="text" class="form-control @error('license_number') is-invalid @enderror"
                   id="license_number" name="license_number" value="{{ old('license_number', $company->license_number ?? '') }}"
                   placeholder="Enter license number">
            @error('license_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="registration_date" class="form-label">Registration Date</label>
            <input type="date" class="form-control @error('registration_date') is-invalid @enderror"
                   id="registration_date" name="registration_date" value="{{ old('registration_date', $company->registration_date ?? '') }}">
            @error('registration_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="functional_currency" class="form-label">Functional Currency</label>
            <input type="text" class="form-control @error('functional_currency') is-invalid @enderror"
                   id="functional_currency" name="functional_currency" maxlength="3"
                   value="{{ old('functional_currency', $company->functional_currency ?? 'TZS') }}"
                   placeholder="e.g. TZS">
            @error('functional_currency')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="active" {{ old('status', $company->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $company->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ old('status', $company->status ?? '') == 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="mb-3">
            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
            <textarea class="form-control @error('address') is-invalid @enderror"
                      id="address" name="address" rows="3"
                      placeholder="Enter company address" required>{{ old('address', $company->address ?? '') }}</textarea>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="logo" class="form-label">Company Logo</label>
            <input type="file" class="form-control @error('logo') is-invalid @enderror"
                   id="logo" name="logo" accept="image/*">
            @error('logo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Upload a logo image (JPG, PNG, GIF). Max size: 2MB.</small>
        </div>
    </div>

    @if(isset($company) && $company->logo)
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Current Logo</label>
            <div>
                <img src="{{ asset('storage/' . $company->logo) }}" alt="Company Logo"
                     class="img-thumbnail" style="max-height: 100px;">
            </div>
        </div>
    </div>
    @endif
</div>
