<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect user to login page with a custom message
if (isset($_GET['message']) && $_GET['message'] == 'awaiting_approval') {
    header('Location: login.php?message=awaiting_approval');
} else {
    header('Location: login.php');
}
exit();
