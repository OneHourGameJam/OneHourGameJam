<?php

function loadCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
    $token = $_SESSION['csrf_token'];
}

// Returns true if the form submission contains the correct csrf token for the current session.
function checkCSRFToken() {
    if (!isset($_POST['csrf_token']))
        return false;
    return $_POST['csrf_token'] === $_SESSION['csrf_token'];
}

?>