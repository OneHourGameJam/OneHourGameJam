<?php

class PlatformModel{
	public $Id;
	public $Name;
	public $IconUrl;
	public $Deleted;
}

class PlatformData{
    public $PlatformModels;

    function __construct() {
        $this->PlatformModels = $this->LoadPlatforms();
    }

    function LoadPlatforms(){
        global $dbConn;
        AddActionLog("LoadPlatforms");
        StartTimer("LoadPlatforms");

        $platformModels = Array();

        $sql = "SELECT platform_id, platform_name, platform_icon_url, platform_deleted FROM platform";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($info = mysqli_fetch_array($data)){
            $platform = new PlatformModel();

            $platform->Id = intval($info["platform_id"]);
            $platform->Name = $info["platform_name"];
            $platform->IconUrl = $info["platform_icon_url"];
            $platform->Deleted = intval($info["platform_deleted"]);

            $platformModels[$platform->Id] = $platform;
        }

        StopTimer("LoadPlatforms");
        return $platformModels;
    }
}

?>