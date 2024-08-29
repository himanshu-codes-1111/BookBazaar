document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const errorMessage = document.getElementById('error-message');

    form.addEventListener('submit', function(event) {
        let valid = true;
        errorMessage.textContent = ''; // Clear previous errors

        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        // Validate password and confirm password match
        if (password !== confirmPassword) {
            errorMessage.textContent = 'Passwords do not match.';
            valid = false;
        }

        // Simple validation for empty fields
        const fields = ['first-name', 'last-name', 'username', 'address', 'email', 'mobile'];
        fields.forEach(function(field) {
            if (!document.getElementById(field).value) {
                errorMessage.textContent = 'Please fill in all fields.';
                valid = false;
            }
        });

        // Regex for basic email validation
        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            errorMessage.textContent = 'Please enter a valid email address.';
            valid = false;
        }

        if (!valid) {
            event.preventDefault(); // Prevent form submission if invalid
        }
    });

    // Show/Hide Passwords
    document.querySelectorAll('.toggle-password').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            this.textContent = type === 'password' ? 'Show' : 'Hide';
        });
    });
});
