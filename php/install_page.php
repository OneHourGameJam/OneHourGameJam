<?php

$dbConfigPath = "config/dbconfig.php";

function RunInstallPage(&$dictionary) {
    global $dbConfigPath;

    // Safety check, make sure that this is a fresh installation
    if (file_exists($dbConfigPath)) {
        $dictionary['error_message'] = "Database has been configured already! Please delete the dbconfig.php file to start the installation process.";
        $dictionary['template_file'] = 'install_fail.html';
        return;
    }

    // Check to see if this is a setup post request
    if(isset($_POST["action"])) {
        if ($_POST["action"] == "setup") {
            // Run the database setup, and then set the template file based on if there was an error or not.
            $errorMessage = RunSetupAction();
            $dictionary['error_message'] = $errorMessage;

            if ($errorMessage == "") {
                $dictionary['template_file'] = 'install_success.html';
                return;
            } else {
                $dictionary['template_file'] = 'install_fail.html';
                return;
            }
        }
    }
    
    // Assume that if we got here we want to display the form.
    $dictionary['template_file'] = 'install_form.html';
}

// Runs the setup and returns any errors encountered
function RunSetupAction() {
    global $dbConfigPath;

    $dbHost = $_POST["dbHost"];
    $dbUser = $_POST["dbUsername"];
    $dbPassword = $_POST["dbPassword"];
    $dbName = $_POST["dbName"];

    // Check connection
    $conn = @mysqli_connect($dbHost, $dbUser, $dbPassword);

    if (!$conn) {
        return "Connection failed: " . mysqli_connect_error();
    }

    // Create the database if needed
    $escapedDBName = mysqli_real_escape_string($conn, $dbName);
    $sql = "CREATE DATABASE IF NOT EXISTS $escapedDBName;";
    if (@mysqli_query($conn, $sql) == FALSE) {
        return "Unable to create/verify database: " . mysqli_error($conn);
    }
    if (mysqli_select_db($conn, $dbName) == FALSE) {
        return "Unable to select database: " . mysqli_error($conn);
    }

    // Run the installation script
    $sql = file_get_contents("SQL/1hgj.sql");
    if(mysqli_multi_query($conn, $sql)) {
        do {
            mysqli_next_result($conn);
        }
        while(mysqli_more_results($conn));
    }

    if(mysqli_errno($conn)) {
        return "Unable to run installation script: " . mysqli_error($conn);
    }
    
    
    // Save credentials in the config file
    $safeHost = addslashes($dbHost);
    $safeUser = addslashes($dbUser);
    $safePassword = addslashes($dbPassword);
    $safeName = addslashes($dbName);

    $configData = "<?php
\$dbAddress = \"$safeHost\";
\$dbUsername = \"$safeUser\";
\$dbPassword = \"$safePassword\";
\$dbDatabaseName = \"$safeName\";
?>";
    
    $result = file_put_contents($dbConfigPath, $configData);
    if ($result == FALSE) {
        return "Unable to write config file. Please manually fill it out (database has been initialized)";
    }
    
    return "";
}