<?php
// 1. Include config to start the session
require_once 'config.php';

// 2. Destroy all session data
session_destroy();

// 3. Redirect to the index page
header('Location: index.php');
exit;
?>