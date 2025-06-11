document.getElementById('registerForm').addEventListener('submit', function (e) {
    const password = document.getElementById('password').value;

    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

    if (!regex.test(password)) {
        e.preventDefault(); 
        alert("Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.");
    }
});
