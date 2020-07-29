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

class AssetModel{
	public $Id;
	public $AuthorUserId;
	public $Title;
	public $Description;
	public $Type;
	public $Content;
}

class AssetData{
    public $AssetModels;
    
    private $dbConnection;
    private $publicColumns = Array(DB_COLUMN_ASSET_ID, DB_COLUMN_ASSET_AUTHOR_USER_ID, DB_COLUMN_ASSET_TITLE, DB_COLUMN_ASSET_DESCRIPTION, DB_COLUMN_ASSET_TYPE, DB_COLUMN_ASSET_CONTENT, DB_COLUMN_ASSET_DELETED);
    private $privateColumns = Array(DB_COLUMN_ASSET_DATETIME, DB_COLUMN_ASSET_IP, DB_COLUMN_ASSET_USER_AGENT);

    function __construct(&$dbConn) {
        $this->dbConnection = $dbConn;
        $this->AssetModels = $this->LoadAssets();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadAssets(){
        global $dbConn;
        AddActionLog("LoadAssets");
        StartTimer("LoadAssets");

        $assetModels = Array();

        $data = $this->SelectCurrentlyActiveAssets();

        while($info = mysqli_fetch_array($data)){
            $id = $info[DB_COLUMN_ASSET_ID];

            $assetModel = new AssetModel();
            $assetModel->Id = $id;
            $assetModel->AuthorUserId = $info[DB_COLUMN_ASSET_AUTHOR_USER_ID];
            $assetModel->Title = $info[DB_COLUMN_ASSET_TITLE];
            $assetModel->Description = $info[DB_COLUMN_ASSET_DESCRIPTION];
            $assetModel->Type = $info[DB_COLUMN_ASSET_TYPE];
            $assetModel->Content = $info[DB_COLUMN_ASSET_CONTENT];

            $assetModels[$id] = $assetModel;
        }

        StopTimer("LoadAssets");
        return $assetModels;
    }

//////////////////////// END MODEL CONSTRUCTOR
    
//////////////////////// DATABASE ACTIONS (select, insert, update)

    function GetAssetsOfUserFormatted($authorUserId){
        AddActionLog("GetAssetsOfUserFormatted");
        StartTimer("GetAssetsOfUserFormatted");
        
        $data = $this->SelectWhereAuthorUserId($authorUserId);
    
        StopTimer("GetAssetsOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS
    
//////////////////////// FUCNTION SQL

    private function SelectCurrentlyActiveAssets(){
        AddActionLog("AssetData_SelectCurrentlyActiveAssets");
        StartTimer("AssetData_SelectCurrentlyActiveAssets");

        $sql = "
            SELECT ".DB_COLUMN_ASSET_ID.", ".DB_COLUMN_ASSET_AUTHOR_USER_ID.", ".DB_COLUMN_ASSET_TITLE.", ".DB_COLUMN_ASSET_DESCRIPTION.", ".DB_COLUMN_ASSET_TYPE.", ".DB_COLUMN_ASSET_CONTENT."
            FROM ".DB_TABLE_ASSET."
            WHERE ".DB_COLUMN_ASSET_DELETED." != 1
        ";

        StopTimer("AssetData_SelectCurrentlyActiveAssets");
        return mysqli_query($this->dbConnection, $sql);
    }

    private function SelectWhereAuthorUserId($authorUserId){
        AddActionLog("AssetData_SelectWhereAuthorUserId");
        StartTimer("AssetData_SelectWhereAuthorUserId");

        $escapedAuthorUserId = mysqli_real_escape_string($this->dbConnection, $authorUserId);
        $sql = "
            SELECT ".DB_COLUMN_ASSET_ID.", ".DB_COLUMN_ASSET_DATETIME.", ".DB_COLUMN_ASSET_AUTHOR_USER_ID.", ".DB_COLUMN_ASSET_TITLE.", ".DB_COLUMN_ASSET_DESCRIPTION.", ".DB_COLUMN_ASSET_TYPE.", ".DB_COLUMN_ASSET_CONTENT.", ".DB_COLUMN_ASSET_DELETED."
            FROM ".DB_TABLE_ASSET."
            WHERE ".DB_COLUMN_ASSET_AUTHOR_USER_ID." = '$escapedAuthorUserId';";

        StopTimer("AssetData_SelectWhereAuthorUserId");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END FUCNTION SQL

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        global $dbConn;
        
        $dataFromDatabase = MySQLDataToArray($this->SelectPublicData());
        
        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_ASSET_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_ASSET_IP] = "MIGRATION";
            $dataFromDatabase[$i][DB_COLUMN_ASSET_USER_AGENT] = "MIGRATION";
        }

        return $dataFromDatabase;
    }

    private function SelectPublicData(){
        AddActionLog("AssetData_SelectPublicData");
        StartTimer("AssetData_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_ASSET.";
        ";

        StopTimer("AssetData_SelectPublicData");
        return mysqli_query($this->dbConnection, $sql);
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>