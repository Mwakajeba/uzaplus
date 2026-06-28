@extends('layouts.main')

@section('title', 'Create User')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'User Management', 'url' => route('users.index'), 'icon' => 'bx bx-user'],
            ['label' => 'Create User', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE USER</h6>
        <hr/>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Add New User</h4>

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-2"></i>
                                Please fix the following errors:
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('users.store') }}" method="POST" id="userForm">
                            @csrf
                            @include('users._form')

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Users
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="page-footer">
    <p class="mb-0">Copyright © {{ date('Y') }}. All right reserved. -- By SAFCO FINTECH</p>
</footer>

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function() {
    function setupToggle(buttonId, inputId) {
        const button = document.getElementById(buttonId);
        const input = document.getElementById(inputId);
        if (!button || !input) return;

        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('bx-show', !isHidden);
            icon.classList.toggle('bx-hide', isHidden);
        });
    }

    setupToggle('togglePassword', 'password');
    setupToggle('toggleConfirmPassword', 'password_confirmation');

    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    const form = document.getElementById('userForm');

    if (form) {
        form.addEventListener('submit', function(e) {
            if (password.value.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return;
            }

            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match.');
            }
        });
    }
});
</script>
@include('users._company_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function() {
    initUserCompanySelect2({
        companiesId: 'companies',
        primaryId: 'primary_company_id'
    });
});
</script>
@endpush
@endsection
