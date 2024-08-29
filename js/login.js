document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const errorMessage = document.getElementById('error-message');

    form.addEventListener('submit', function(event) {
        let valid = true;
        errorMessage.textContent = ''; // Clear previous errors

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        // Simple validation for empty fields
        if (!email || !password) {
            errorMessage.textContent = 'Please fill in all fields.';
            valid = false;
        }

        // Regex for basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            errorMessage.textContent = 'Please enter a valid email address.';
            valid = false;
        }

        if (!valid) {
            event.preventDefault(); // Prevent form submission if invalid
        }
    });

    // Show/Hide Password
    document.getElementById('toggle-password').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.textContent = type === 'password' ? 'Show' : 'Hide';
    });
});
