document.addEventListener('DOMContentLoaded', function () {
    const numericInputs = document.querySelectorAll('.numeric-only');
    const form = document.getElementById('incomingPoForm');

    // Helper to validate a single field (no leading zero)
    function validateNoLeadingZero(input) {
        const value = input.value;
        const errorDivId = input.id + '-error'; // assumes error div id = inputId + '-error'
        const errorDiv = document.getElementById(errorDivId);

        // Clear previous custom validity
        input.setCustomValidity('');

        if (value.length > 1 && value[0] === '0') {
            // Leading zero error
            input.classList.add('is-invalid');
            if (errorDiv) {
                errorDiv.textContent = 'Value cannot start with zero.';
            }
            input.setCustomValidity('Value cannot start with zero.');
            return false;
        } else {
            // Check other requirements (e.g., required, min) – let browser handle
            input.classList.remove('is-invalid');
            if (errorDiv) {
                // Restore original message (or dynamic based on other checks)
                // For simplicity, we'll keep the generic message; you could enhance
                if (input.id === 'qty') errorDiv.textContent = 'Quantity must be at least 1.';
                else if (input.id === 'harga_display') errorDiv.textContent = 'Price is required.';
                else if (input.id === 'margin_percentage') errorDiv.textContent = 'Margin percentage is required.';
                else if (input.id === 'tambahan_margin_display') errorDiv.textContent = 'Invalid amount.';
            }
            input.setCustomValidity('');
            return true;
        }
    }

    numericInputs.forEach(input => {
        // Block non‑digit keys (same as before)
        input.addEventListener('keydown', function (e) {
            const key = e.key;
            const isCtrlKey = e.ctrlKey || e.metaKey;
            const allowedSpecialKeys = [
                'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
                'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                'Home', 'End'
            ];

            if (allowedSpecialKeys.includes(key) || isCtrlKey) return;

            if (!/^[0-9]$/.test(key)) {
                e.preventDefault();
            }
        });

        // Clean input and validate on any change
        input.addEventListener('input', function () {
            // Remove non‑digits
            let sanitized = this.value.replace(/\D/g, '');

            // Apply maxlength
            const maxLen = this.getAttribute('maxlength');
            if (maxLen && sanitized.length > parseInt(maxLen, 10)) {
                sanitized = sanitized.slice(0, maxLen);
            }

            // Update field if changed
            if (this.value !== sanitized) {
                this.value = sanitized;
            }

            // Validate leading zero
            validateNoLeadingZero(this);

            // Sync hidden fields
            if (this.id === 'harga_display') {
                document.getElementById('harga').value = sanitized;
            }
            if (this.id === 'tambahan_margin_display') {
                document.getElementById('tambahan_margin').value = sanitized;
            }
        });

        // Validate on blur as well (in case user tabs out)
        input.addEventListener('blur', function () {
            validateNoLeadingZero(this);
        });

        // Sanitize paste
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            let sanitized = paste.replace(/\D/g, '');

            const maxLen = this.getAttribute('maxlength');
            if (maxLen) {
                sanitized = sanitized.slice(0, parseInt(maxLen, 10));
            }

            const start = this.selectionStart;
            const end = this.selectionEnd;
            const currentValue = this.value;
            const newValue = currentValue.substring(0, start) + sanitized + currentValue.substring(end);
            this.value = newValue;

            this.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });

    // Final validation on form submit
    form.addEventListener('submit', function (e) {
        let isValid = true;

        numericInputs.forEach(input => {
            // Update hidden fields one last time
            if (input.id === 'harga_display') {
                document.getElementById('harga').value = input.value.replace(/\D/g, '');
            }
            if (input.id === 'tambahan_margin_display') {
                document.getElementById('tambahan_margin').value = input.value.replace(/\D/g, '');
            }

            // Check leading zero
            if (!validateNoLeadingZero(input)) {
                isValid = false;
            }

            // Also run browser validation (required, min, etc.)
            if (!input.checkValidity()) {
                isValid = false;
                // Let browser show its own message; we can also mark field
                input.classList.add('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            e.stopPropagation();
            form.classList.add('was-validated');
        }
    });
});