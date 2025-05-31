<?php
session_start();
require '../include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $db = new Database();
    $conn = $db->connect();

    $user = $db->getUserByUsername($username);

    if ($user && password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        header('Location: ../index.php');
        exit();
    } else {
        // Login failed
        echo "Invalid username or password.";
    }
}