<?php

//Edits an existing user, identified by the username.
//Valid values for isAdmin are 0 (not admin) and 1 (admin)
//Only changes whether the user is an admin, does NOT change the user's username.
function EditUser($username, $isAdmin){
	global $users, $dbConn;

	//Authorize user (is admin)
	if(IsAdmin() === false){
		AddAuthorizationWarning("Only admins can edit entries.", false);
		return;
	}

	//Validate values
	if($isAdmin == 0){
		$isAdmin = 0;
	}else if($isAdmin == 1){
		$isAdmin = 1;
	}else{
		AddDataWarning("Bad isadmin value", false);
		return;
	}

	//Check that the user exists
	if(!isset($users[$username])){
		AddDataWarning("User does not exist", false);
		return;
	}

	$usernameClean = mysqli_real_escape_string($dbConn, $username);

	$sql = "
		UPDATE user
		SET
		user_role = $isAdmin
		WHERE user_username = '$usernameClean';
	";
	mysqli_query($dbConn, $sql) ;
    $sql = "";

    AddToAdminLog("USER_EDITED", "User $username updated with values: IsAdmin: $isAdmin", $username);

	LoadUsers();
	$loggedInUser = IsLoggedIn(TRUE);
}

if(IsAdmin()){
    $username = $_POST["username"];
    $isAdmin = (isset($_POST["isadmin"])) ? intval($_POST["isadmin"]) : 0;
    if($isAdmin != 0 && $isAdmin != 1){
        die("invalid isadmin value");
    }

    EditUser($username, $isAdmin);
}
$page = "editusers";

?>