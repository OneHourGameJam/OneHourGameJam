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

        $jams = Array();

        $sql = "SELECT jam_id, jam_username, jam_jam_number, jam_theme, jam_start_datetime, jam_colors, jam_deleted
        FROM jam ORDER BY jam_jam_number DESC";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($info = mysqli_fetch_array($data)){
            $jam = new JamModel();
            $jamID = intval($info["jam_id"]);

            $jam->Id = $jamID;
            $jam->Username = $info["jam_username"];
            $jam->JamNumber = intval($info["jam_jam_number"]);
            $jam->Theme = $info["jam_theme"];
            $jam->StartTime = $info["jam_start_datetime"];
            $jam->Colors = ParseJamColors($info["jam_colors"]);
            $jam->Deleted = $info["jam_deleted"];

            $jams[$jamID] = $jam;
        }

        StopTimer("LoadJams");
        return $jams;
    }
}

?>