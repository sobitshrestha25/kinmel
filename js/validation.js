// js/validation.js - simple client side checks before a form is submitted

function validateRegisterForm() {
    const name = document.querySelector('input[name="name"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();
    const pass = document.querySelector('input[name="password"]').value;
    const confirm = document.querySelector('input[name="confirm_password"]').value;

    if (name.length < 2) {
        alert("Please enter your full name.");
        return false;
    }
    if (!/^[^@\s]+@[^@\s]+\.[a-z]{2,}$/i.test(email)) {
        alert("Please enter a valid email address.");
        return false;
    }
    if (pass.length < 6) {
        alert("Password must be at least 6 characters.");
        return false;
    }
    if (pass !== confirm) {
        alert("Passwords do not match.");
        return false;
    }
    return true;
}

function validateLoginForm() {
    const email = document.querySelector('input[name="email"]').value.trim();
    const pass = document.querySelector('input[name="password"]').value;

    if (!email || !pass) {
        alert("Please enter both email and password.");
        return false;
    }
    return true;
}

function validateProductForm() {
    const price = parseFloat(document.querySelector('input[name="price"]').value);
    const stock = parseInt(document.querySelector('input[name="stock"]').value, 10);

    if (isNaN(price) || price <= 0) {
        alert("Please enter a valid price.");
        return false;
    }
    if (isNaN(stock) || stock < 0) {
        alert("Please enter a valid stock quantity.");
        return false;
    }
    return true;
}
