<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate
if ($name === '' || $email === '' || $subject === '' || $message === '') {
    header("Location: ../index.php?contact=error");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../index.php?contact=invalid_email");
    exit;
}

/*
 * For now, simply process the contact form
 * and redirect to home.
 *
 * You can later connect this to a database
 * or email system.
 */

$_SESSION['contact_success'] = "Your message has been sent successfully.";

header("Location: ../index.php");
exit;