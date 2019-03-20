<?php

class AssetModel{
	public $Id;
	public $Author;
	public $AuthorDisplayName;
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
            SELECT a.asset_id, a.asset_author, a.asset_title, a.asset_description, a.asset_type, a.asset_content, u.user_display_name
            FROM asset a, user u
            WHERE asset_deleted != 1
            AND a.asset_author = u.user_username
        ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($asset = mysqli_fetch_array($data)){
            $id = $asset["asset_id"];
            $author = $asset["asset_author"];
            $author_display_name = $asset["user_display_name"];
            $title = $asset["asset_title"];
            $description = $asset["asset_description"];
            $type = $asset["asset_type"];
            $content = $asset["asset_content"];

            $asset = new AssetModel();
            $asset->Id = $id;
            $asset->Author = $author;
            $asset->AuthorDisplayName = $author_display_name;
            $asset->Title = $title;
            $asset->Description = $description;
            $asset->Type = $type;
            $asset->Content = $content;

            $assetModels[$id] = $asset;
        }

        StopTimer("LoadAssets");
        return $assetModels;
    }
}

?>