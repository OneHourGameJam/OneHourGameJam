<?php

class JamModel{
	public $Id;
	public $Username;
	public $JamNumber;
	public $Theme;
	public $StartTime;
	public $Colors;
	public $Deleted;
}

class JamData{
    public $JamModels;

    function __construct() {
        $this->JamModels = $this->LoadJams();
    }

    function LoadJams(){
        global $dbConn;
        AddActionLog("LoadJams");
        StartTimer("LoadJams");

        $jamModels = Array();

        $sql = "SELECT jam_id, jam_username, jam_jam_number, jam_theme, jam_start_datetime, jam_colors, jam_deleted
        FROM jam ORDER BY jam_jam_number DESC";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($info = mysqli_fetch_array($data)){
            $jamModel = new JamModel();
            $jamID = intval($info["jam_id"]);

            $jamModel->Id = $jamID;
            $jamModel->Username = $info["jam_username"];
            $jamModel->JamNumber = intval($info["jam_jam_number"]);
            $jamModel->Theme = $info["jam_theme"];
            $jamModel->StartTime = $info["jam_start_datetime"];
            $jamModel->Colors = ParseJamColors($info["jam_colors"]);
            $jamModel->Deleted = $info["jam_deleted"];

            $jamModels[$jamID] = $jamModel;
        }

        StopTimer("LoadJams");
        return $jamModels;
    }

    function GroupJamsByUsername(&$gamesByUsername)
    {
        AddActionLog("GroupJamsByUsername");
        StartTimer("GroupJamsByUsername");
    
        $jamsByUsername = Array();
        foreach($gamesByUsername as $username => $gameModels){
            $jamsByUsername[$username] = Array();
            foreach($gameModels as $i => $gameModel){
                $jamsByUsername[$username][$gameModel->JamId] = $this->JamModels[$gameModel->JamId];
            }
        }
    
        StopTimer("GroupJamsByUsername");
        return $jamsByUsername;
    }

    //Adds the jam with the provided data into the database
    function AddJamToDatabase($ip, $userAgent, $username, $jamNumber, $theme, $startTime, $colors, &$adminLogData){
        global $dbConn;
        AddActionLog("AddJamToDatabase");
        StartTimer("AddJamToDatabase");
    
        $escapedIP = mysqli_real_escape_string($dbConn, $ip);
        $escapedUserAgent = mysqli_real_escape_string($dbConn, $userAgent);
        $escapedUsername = mysqli_real_escape_string($dbConn, $username);
        $escapedJamNumber = mysqli_real_escape_string($dbConn, $jamNumber);
        $escapedTheme = mysqli_real_escape_string($dbConn, $theme);
        $escapedStartTime = mysqli_real_escape_string($dbConn, $startTime);
        $escapedColors = mysqli_real_escape_string($dbConn, $colors);
    
        $sql = "
            INSERT INTO jam
            (jam_id,
            jam_datetime,
            jam_ip,
            jam_user_agent,
            jam_username,
            jam_jam_number,
            jam_theme,
            jam_start_datetime,
            jam_colors,
            jam_deleted)
            VALUES
            (null,
            Now(),
            '$escapedIP',
            '$escapedUserAgent',
            '$escapedUsername',
            '$escapedJamNumber',
            '$escapedTheme',
            '$escapedStartTime',
            '$escapedColors',
            0);";
    
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        StopTimer("AddJamToDatabase");
        $adminLogData->AddToAdminLog("JAM_ADDED", "Jam scheduled with values: JamNumber: $jamNumber, Theme: '$theme', StartTime: '$startTime', Colors: $colors", "", $username);
    }
    
    function GetJamsOfUserFormatted($username){
        global $dbConn;
        AddActionLog("GetJamsOfUserFormatted");
        StartTimer("GetJamsOfUserFormatted");
    
        $escapedUsername = mysqli_real_escape_string($dbConn, $username);
        $sql = "
            SELECT *
            FROM jam
            WHERE jam_username = '$escapedUsername';
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        StopTimer("GetJamsOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
}

?>