<?php
// register.php
session_start();
require_once("includes/db.php"); // ensures $pdo is available

// If someone opens this file directly, redirect them to the form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit();
}

// Read POST values
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Basic validation
if ($name === '' || $email === '' || $password === '') {
    // Could redirect back with error in query string; keep simple for now:
    die("All fields are required.");
}

// Password policy already checked client-side; double-check server-side too
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
    die("Password does not meet the complexity requirements.");
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Prevent duplicate emails
    $sql = "SELECT 1 FROM app_user WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);

    if ($stmt->fetch()) {
        // email already exists
        die("Email already exists. Try logging in.");
    }

    // Insert user
    $sql = "INSERT INTO app_user (name, email, password)
            VALUES (:name, :email, :password)
            RETURNING user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => $hashedPassword
    ]);

    $newUserId = $stmt->fetchColumn();

    // Optional: automatically log in the user after registration
    // Set session and redirect to dashboard
    $_SESSION['user_id'] = $newUserId;
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;

    header("Location: dashboard.html");
    exit();

} catch (Exception $e) {
    // In production avoid echoing raw error messages. Show a friendly one instead.
    die("Error registering user: " . htmlspecialchars($e->getMessage()));
}
?>
