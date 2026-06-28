<div class="card border-info h-100">
    <div class="card-header bg-info bg-opacity-10">
        <h5 class="card-title mb-0 text-info">
            <i class="bx bx-info-circle me-2"></i>Guidelines
        </h5>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            @if(isset($branch))
                Update the branch details below. Changes apply immediately across the system for the selected company.
            @else
                Use this form to register a new branch under a company. All fields marked with <span class="text-danger">*</span> are required.
            @endif
        </p>

        <h6 class="fw-semibold mb-2">
            <i class="bx bx-building-house me-1 text-primary"></i> Company
        </h6>
        <p class="small text-muted mb-3">
            Select the company this branch belongs to. Use the search box to find a company quickly when you have many records.
        </p>

        <h6 class="fw-semibold mb-2">
            <i class="bx bx-git-branch me-1 text-primary"></i> Branch Name
        </h6>
        <p class="small text-muted mb-3">
            Enter a clear, unique name for the branch (e.g. <em>Main Branch</em>, <em>Dar es Salaam Office</em>). This name appears in reports, user assignments, and branch selection screens.
        </p>

        <h6 class="fw-semibold mb-2">
            <i class="bx bx-phone me-1 text-primary"></i> Phone
        </h6>
        <p class="small text-muted mb-3">
            Provide a valid contact number for the branch. Include the country code where applicable (e.g. +255…).
        </p>

        <h6 class="fw-semibold mb-2">
            <i class="bx bx-toggle-left me-1 text-primary"></i> Status
        </h6>
        <ul class="small text-muted mb-3 ps-3">
            <li class="mb-1"><strong>Active</strong> — branch is available for transactions, user assignment, and daily operations.</li>
            <li><strong>Inactive</strong> — branch is hidden from new assignments but historical data is retained.</li>
        </ul>

        @if(isset($branch))
            <div class="alert alert-light border small mb-0">
                <i class="bx bx-time-five me-1"></i>
                <strong>Last updated:</strong> {{ $branch->updated_at?->format('d M Y, h:i A') ?? 'N/A' }}
            </div>
        @else
            <div class="alert alert-light border small mb-0">
                <i class="bx bx-bulb me-1 text-warning"></i>
                <strong>Tip:</strong> New branches default to <strong>Active</strong>. You can change the status later from the edit screen.
            </div>
        @endif
    </div>
</div>
