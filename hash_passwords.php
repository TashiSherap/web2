<?php
// Function to validate admin password
function validateAdminPassword($password) {
    if (strlen($password) < 12) {
        return "Admin password must be at least 12 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Admin password must contain at least one uppercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Admin password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        return "Admin password must contain at least one special character";
    }
    return true;
}

// Function to validate regular user password
function validateUserPassword($password) {
    if (strlen($password) < 8) {
        return "User password must be at least 8 characters long";
    }
    return true;
}

// Passwords to hash
$adminPassword = 'Adminhere123!';
$userPassword = 'Userjames';

// Validate admin password
$adminValidation = validateAdminPassword($adminPassword);
if ($adminValidation !== true) {
    die("Admin Password Validation Error: $adminValidation");
}

// Validate user password
$userValidation = validateUserPassword($userPassword);
if ($userValidation !== true) {
    die("User Password Validation Error: $userValidation");
}

// Hash the passwords
$adminHashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
$userHashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

// Output the hashed passwords
echo "Admin Hashed Password: $adminHashedPassword\n";
echo "User Hashed Password: $userHashedPassword\n";
?>