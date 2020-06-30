<?php

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

    function __construct() {
        $this->AssetModels = $this->LoadAssets();
    }

    function LoadAssets(){
        global $dbConn;
        AddActionLog("LoadAssets");
        StartTimer("LoadAssets");

        $assetModels = Array();

        $sql = "
            SELECT asset_id, asset_author_user_id, asset_title, asset_description, asset_type, asset_content
            FROM asset
            WHERE asset_deleted != 1
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($asset = mysqli_fetch_array($data)){
            $id = $asset["asset_id"];
            $authorUserId = $asset["asset_author_user_id"];
            $title = $asset["asset_title"];
            $description = $asset["asset_description"];
            $type = $asset["asset_type"];
            $content = $asset["asset_content"];

            $asset = new AssetModel();
            $asset->Id = $id;
            $asset->AuthorUserId = $authorUserId;
            $asset->Title = $title;
            $asset->Description = $description;
            $asset->Type = $type;
            $asset->Content = $content;

            $assetModels[$id] = $asset;
        }

        StopTimer("LoadAssets");
        return $assetModels;
    }

    function GetAssetsOfUserFormatted($userId){
        global $dbConn;
        AddActionLog("GetAssetsOfUserFormatted");
        StartTimer("GetAssetsOfUserFormatted");
        
        $escapedUserId = mysqli_real_escape_string($dbConn, $userId);
        $sql = "
            SELECT *
            FROM asset
            WHERE asset_author_user_id = $escapedUserId;
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";
    
        StopTimer("GetAssetsOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }
}

?>