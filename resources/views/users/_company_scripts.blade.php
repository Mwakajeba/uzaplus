<script nonce="{{ $cspNonce ?? '' }}">
function initUserCompanySelect2(config) {
    const companiesEl = document.getElementById(config.companiesId);
    const primaryEl = document.getElementById(config.primaryId);

    if (!companiesEl || !primaryEl || !window.jQuery || !jQuery.fn.select2) {
        return;
    }

    const $companies = jQuery(companiesEl);
    const $primary = jQuery(primaryEl);
    const dropdownParent = config.dropdownParent ? jQuery(config.dropdownParent) : null;

    const allCompanyOptions = Array.from(companiesEl.options).map(function(option) {
        return { value: option.value, label: option.text };
    });

    function destroySelect2($element) {
        if ($element.hasClass('select2-hidden-accessible')) {
            $element.select2('destroy');
        }
    }

    function initPrimarySelect2() {
        destroySelect2($primary);
        $primary.select2({
            theme: 'bootstrap-5',
            placeholder: 'Search primary company',
            allowClear: false,
            width: '100%',
            dropdownParent: dropdownParent
        });
    }

    function refreshPrimaryOptions() {
        const selectedValues = $companies.val() || [];
        const currentPrimary = $primary.val();

        destroySelect2($primary);
        primaryEl.innerHTML = '<option value="">Select primary company</option>';

        allCompanyOptions.forEach(function(option) {
            if (!selectedValues.includes(option.value)) {
                return;
            }

            const el = document.createElement('option');
            el.value = option.value;
            el.textContent = option.label;
            if (option.value === currentPrimary) {
                el.selected = true;
            }
            primaryEl.appendChild(el);
        });

        if (!primaryEl.value && selectedValues.length > 0) {
            primaryEl.value = selectedValues[0];
        }

        initPrimarySelect2();
    }

    destroySelect2($companies);
    $companies.select2({
        theme: 'bootstrap-5',
        placeholder: 'Search and select companies',
        allowClear: true,
        width: '100%',
        dropdownParent: dropdownParent
    });

    initPrimarySelect2();
    refreshPrimaryOptions();

    $companies.off('change.userCompanySelect2').on('change.userCompanySelect2', refreshPrimaryOptions);
}
</script>
