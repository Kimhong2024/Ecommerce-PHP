<?php
require '../include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $db = new Database();
    $conn = $db->connect();

    // Check if username or email already exists
    $sql = "SELECT * FROM users WHERE username = :username OR email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['username' => $username, 'email' => $email]);

    if ($stmt->fetch()) {
        echo "Username or email already exists.";
    } else {
        // Insert new user
        $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password]);

        echo "Registration successful. <a href='login.php'>Login here</a>.";
    }
}