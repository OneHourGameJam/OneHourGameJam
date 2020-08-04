<?php
define("DB_TABLE_ASSET", "asset");

define("DB_COLUMN_ASSET_ID",                "asset_id");
define("DB_COLUMN_ASSET_DATETIME",          "asset_datetime");
define("DB_COLUMN_ASSET_IP",                "asset_ip");
define("DB_COLUMN_ASSET_USER_AGENT",        "asset_user_agent");
define("DB_COLUMN_ASSET_AUTHOR_USER_ID",    "asset_author_user_id");
define("DB_COLUMN_ASSET_TITLE",             "asset_title");
define("DB_COLUMN_ASSET_DESCRIPTION",       "asset_description");
define("DB_COLUMN_ASSET_TYPE",              "asset_type");
define("DB_COLUMN_ASSET_CONTENT",           "asset_content");
define("DB_COLUMN_ASSET_DELETED",           "asset_deleted");

class AssetDbInterface{
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_ASSET_ID, DB_COLUMN_ASSET_AUTHOR_USER_ID, DB_COLUMN_ASSET_TITLE, DB_COLUMN_ASSET_DESCRIPTION, DB_COLUMN_ASSET_TYPE, DB_COLUMN_ASSET_CONTENT, DB_COLUMN_ASSET_DELETED);
    private $privateColumns = Array(DB_COLUMN_ASSET_DATETIME, DB_COLUMN_ASSET_IP, DB_COLUMN_ASSET_USER_AGENT);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
    }

    public function SelectCurrentlyActiveAssets(){
        AddActionLog("AssetDbInterface_SelectCurrentlyActiveAssets");
        StartTimer("AssetDbInterface_SelectCurrentlyActiveAssets");

        $sql = "
            SELECT ".DB_COLUMN_ASSET_ID.", ".DB_COLUMN_ASSET_AUTHOR_USER_ID.", ".DB_COLUMN_ASSET_TITLE.", ".DB_COLUMN_ASSET_DESCRIPTION.", ".DB_COLUMN_ASSET_TYPE.", ".DB_COLUMN_ASSET_CONTENT."
            FROM ".DB_TABLE_ASSET."
            WHERE ".DB_COLUMN_ASSET_DELETED." != 1
        ";

        StopTimer("AssetDbInterface_SelectCurrentlyActiveAssets");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectWhereAuthorUserId($authorUserId){
        AddActionLog("AssetDbInterface_SelectWhereAuthorUserId");
        StartTimer("AssetDbInterface_SelectWhereAuthorUserId");

        $escapedAuthorUserId = mysqli_real_escape_string($this->dbConnection, $authorUserId);
        $sql = "
            SELECT ".DB_COLUMN_ASSET_ID.", ".DB_COLUMN_ASSET_DATETIME.", ".DB_COLUMN_ASSET_AUTHOR_USER_ID.", ".DB_COLUMN_ASSET_TITLE.", ".DB_COLUMN_ASSET_DESCRIPTION.", ".DB_COLUMN_ASSET_TYPE.", ".DB_COLUMN_ASSET_CONTENT.", ".DB_COLUMN_ASSET_DELETED."
            FROM ".DB_TABLE_ASSET."
            WHERE ".DB_COLUMN_ASSET_AUTHOR_USER_ID." = '$escapedAuthorUserId';";

        StopTimer("AssetDbInterface_SelectWhereAuthorUserId");
        return mysqli_query($this->dbConnection, $sql);
    }

    public function SelectPublicData(){
        AddActionLog("AssetDbInterface_SelectPublicData");
        StartTimer("AssetDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_ASSET.";
        ";

        StopTimer("AssetDbInterface_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }
}

?>