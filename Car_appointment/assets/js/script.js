document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });
    }

    // Date input validation
    const dateInput = document.querySelector('input[type="date"]');
    if (dateInput) {
        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;

        dateInput.addEventListener('change', function() {
            validateDate(this.value);
        });
    }

    // Phone number validation
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+]/g, '');
        });
    }

    // Engine number validation
    const engineInput = document.querySelector('input[name="engine_no"]');
    if (engineInput) {
        engineInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^A-Za-z0-9-]/g, '');
        });
    }

    // Dynamic mechanic availability check
    const mechanicSelect = document.querySelector('select[name="mechanic_id"]');
    const dateInputForMechanic = document.querySelector('input[name="appointment_date"]');
    
    if (mechanicSelect && dateInputForMechanic) {
        dateInputForMechanic.addEventListener('change', function() {
            updateMechanicAvailability(this.value);
        });
    }
});

function validateForm() {
    let isValid = true;
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        if (input.hasAttribute('required') && !input.value.trim()) {
            showError(input, 'This field is required');
            isValid = false;
        } else {
            clearError(input);
        }
    });

    // Specific validations
    const phone = form.querySelector('input[name="phone"]');
    if (phone && phone.value) {
        if (!validatePhone(phone.value)) {
            showError(phone, 'Please enter a valid phone number');
            isValid = false;
        }
    }

    const engineNo = form.querySelector('input[name="engine_no"]');
    if (engineNo && engineNo.value) {
        if (!validateEngineNo(engineNo.value)) {
            showError(engineNo, 'Please enter a valid engine number');
            isValid = false;
        }
    }

    const date = form.querySelector('input[name="appointment_date"]');
    if (date && date.value) {
        if (!validateDate(date.value)) {
            showError(date, 'Please select a valid future date');
            isValid = false;
        }
    }

    const mechanic = form.querySelector('select[name="mechanic_id"]');
    if (mechanic && mechanic.hasAttribute('required')) {
        if (!mechanic.value) {
            showError(mechanic, 'Please select a mechanic');
            isValid = false;
        }
    }

    return isValid;
}

function validatePhone(phone) {
    const phoneRegex = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
    return phoneRegex.test(phone);
}

function validateEngineNo(engineNo) {
    return engineNo.length >= 5 && /^[A-Za-z0-9-]+$/.test(engineNo);
}

function validateDate(date) {
    const selectedDate = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return selectedDate >= today;
}

function showError(input, message) {
    const formGroup = input.closest('.form-group');
    const errorDiv = formGroup.querySelector('.error-message') || document.createElement('div');
    errorDiv.className = 'error-message alert alert-error';
    errorDiv.textContent = message;
    
    if (!formGroup.querySelector('.error-message')) {
        formGroup.appendChild(errorDiv);
    }
    
    input.classList.add('error');
}

function clearError(input) {
    const formGroup = input.closest('.form-group');
    const errorDiv = formGroup.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
    input.classList.remove('error');
}

function updateMechanicAvailability(date) {
    if (!date) return;

    const mechanicSelect = document.querySelector('select[name="mechanic_id"]');
    if (!mechanicSelect) return;

    // Show loading state
    mechanicSelect.disabled = true;
    const originalHTML = mechanicSelect.innerHTML;
    mechanicSelect.innerHTML = '<option value="">Loading...</option>';

    // Fetch available mechanics
    fetch(`get_available_mechanics.php?date=${date}`)
        .then(response => response.json())
        .then(data => {
            mechanicSelect.innerHTML = '<option value="">Select a Mechanic</option>';
            data.forEach(mechanic => {
                const option = document.createElement('option');
                option.value = mechanic.id;
                option.textContent = `${mechanic.name} (${mechanic.available_slots} slots available)`;
                mechanicSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            mechanicSelect.innerHTML = originalHTML;
        })
        .finally(() => {
            mechanicSelect.disabled = false;
        });
}

// Add smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
}); 