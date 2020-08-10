<?php

function loadCSRFToken() {
    if (empty($_SESSION[SESSION_CSRF_TOKEN])) {
        $_SESSION[SESSION_CSRF_TOKEN] = bin2hex(openssl_random_pseudo_bytes(32));
    }
    $token = $_SESSION[SESSION_CSRF_TOKEN];
}

// Returns true if the form submission contains the correct csrf token for the current session.
function checkCSRFToken() {
    if (!isset($_POST[FORM_CSRF_TOKEN]))
        return false;
    return $_POST[FORM_CSRF_TOKEN] === $_SESSION[SESSION_CSRF_TOKEN];
}

?>