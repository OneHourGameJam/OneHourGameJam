<?php
define("DB_TABLE_PLATFORM", "platform");

define("DB_COLUMN_PLATFORM_ID",         "platform_id");
define("DB_COLUMN_PLATFORM_NAME",       "platform_name");
define("DB_COLUMN_PLATFORM_ICON_URL",   "platform_icon_url");
define("DB_COLUMN_PLATFORM_DELETED",    "platform_deleted");

class PlatformDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_PLATFORM_ID, DB_COLUMN_PLATFORM_NAME, DB_COLUMN_PLATFORM_ICON_URL, DB_COLUMN_PLATFORM_DELETED);
    private $privateColumns = Array();

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectAll(){
        AddActionLog("PlatformDbInterface_SelectAll");
        StartTimer("PlatformDbInterface_SelectAll");

        $sql = "
            SELECT ".DB_COLUMN_PLATFORM_ID.", ".DB_COLUMN_PLATFORM_NAME.", ".DB_COLUMN_PLATFORM_ICON_URL.", ".DB_COLUMN_PLATFORM_DELETED."
            FROM ".DB_TABLE_PLATFORM.";";
        
        StopTimer("PlatformDbInterface_SelectAll");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function Insert($platformName, $iconUrl){
        AddActionLog("PlatformDbInterface_Insert");
        StartTimer("PlatformDbInterface_Insert");

        $escapedPlatformName = mysqli_real_escape_string($this->dbConnection, $platformName);
        $escapedIconUrl = mysqli_real_escape_string($this->dbConnection, $iconUrl);
    
        $sql = "
            INSERT INTO platform
            (platform_id,
            platform_name,
            platform_icon_url,
            platform_deleted)
            VALUES
            (null,
            '$escapedPlatformName',
            '$escapedIconUrl',
            0);
        ";
        $data = mysqli_query($this->dbConnection, $sql);
        
        StopTimer("PlatformDbInterface_Insert");
    }

    public function Update($platformId, $platformName, $iconUrl){
        AddActionLog("PlatformDbInterface_Update");
        StartTimer("PlatformDbInterface_Update");

        $escapedPlatformId = mysqli_real_escape_string($this->dbConnection, $platformId);
        $escapedPlatformName = mysqli_real_escape_string($this->dbConnection, $platformName);
        $escapedIconUrl = mysqli_real_escape_string($this->dbConnection, $iconUrl);
    
        $sql = "
            UPDATE platform
            SET
                platform_name = '$escapedPlatformName',
                platform_icon_url = '$escapedIconUrl'
            WHERE
                platform_id = $escapedPlatformId;
        ";
        $data = mysqli_query($this->dbConnection, $sql);
        
        StopTimer("PlatformDbInterface_Update");
    }

    public function SoftDelete($platformId){
        AddActionLog("PlatformDbInterface_SoftDelete");
        StartTimer("PlatformDbInterface_SoftDelete");

        $escapedPlatformId = mysqli_real_escape_string($this->dbConnection, $platformId);
    
        $sql = " 
            UPDATE platform
            SET
                platform_deleted = 1
            WHERE
                platform_id = $escapedPlatformId;
        ";
        $data = mysqli_query($this->dbConnection, $sql);
        
        StopTimer("PlatformDbInterface_SoftDelete");
    }

    public function RestoreSoftDeleted($platformId){
        AddActionLog("PlatformDbInterface_RestoreSoftDeleted");
        StartTimer("PlatformDbInterface_RestoreSoftDeleted");

        $escapedPlatformId = mysqli_real_escape_string($this->dbConnection, $platformId);
    
        $sql = "
            UPDATE platform 
            SET
                platform_deleted = 0
            WHERE
                platform_id = $escapedPlatformId;
        ";
        $data = mysqli_query($this->dbConnection, $sql);
        
        StopTimer("PlatformDbInterface_RestoreSoftDeleted");
    }

    public function SelectPublicData(){
        AddActionLog("PlatformDbInterface_SelectPublicData");
        StartTimer("PlatformDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_PLATFORM.";
        ";

        StopTimer("PlatformDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>