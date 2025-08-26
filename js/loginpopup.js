// js/loginpopup.js

// Simulate user login status (in a real app, this would be determined by server-side logic or session data)
const isLoggedIn = false; // Set this to true if the user is logged in

const profileLink = document.getElementById('profileLink');
const loginPopup = document.getElementById('loginPopup');
const closeLoginPopup = document.getElementById('closeLoginPopup');

if (profileLink) {
    profileLink.addEventListener('click', function (event) {
        if (!isLoggedIn) {
            event.preventDefault(); // Prevent the default link behavior
            loginPopup.style.display = 'block';
        }
    });
}

if (closeLoginPopup) {
    closeLoginPopup.addEventListener('click', function () {
        loginPopup.style.display = 'none';
    });
}
