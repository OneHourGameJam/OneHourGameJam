<?php

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];


// Returns true if the form submission contains the correct csrf token for the current session.
function confirmCSRF() {
    if (!isset($_POST['csrf_token']))
        return false;
    return $_POST['csrf_token'] === $_SESSION['csrf_token'];
}

?>