<?php
// Include auth utilities for session functions
require 'auth.php';
// Destroy session and redirect to homepage
session_destroy();
header('Location: index.php');
exit;
?>