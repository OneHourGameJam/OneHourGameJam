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

    private $assetDbInterface;

    function __construct(&$assetDbInterface) {
        $this->assetDbInterface = $assetDbInterface;
        $this->AssetModels = $this->LoadAssets();
    }

//////////////////////// MODEL CONSTRUCTOR

    function LoadAssets(){
        AddActionLog("LoadAssets");
        StartTimer("LoadAssets");

        $assetModels = Array();

        $data = $this->assetDbInterface->SelectCurrentlyActiveAssets();

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
        
        $data = $this->assetDbInterface->SelectWhereAuthorUserId($authorUserId);
    
        StopTimer("GetAssetsOfUserFormatted");
        return ArrayToHTML(MySQLDataToArray($data));
    }

//////////////////////// END DATABASE ACTIONS

//////////////////////// PUBLIC DATA EXPORT

    function GetAllPublicData(){
        AddActionLog("AssetData_GetAllPublicData");
        StartTimer("AssetData_GetAllPublicData");
        $dataFromDatabase = MySQLDataToArray($this->assetDbInterface->SelectPublicData());
        
        foreach($dataFromDatabase as $i => $row){
            $dataFromDatabase[$i][DB_COLUMN_ASSET_DATETIME] = gmdate("Y-m-d H:i:s", time());
            $dataFromDatabase[$i][DB_COLUMN_ASSET_IP] = OVERRIDE_MIGRATION;
            $dataFromDatabase[$i][DB_COLUMN_ASSET_USER_AGENT] = OVERRIDE_MIGRATION;
        }

        StopTimer("AssetData_GetAllPublicData");
        return $dataFromDatabase;
    }

//////////////////////// END PUBLIC DATA EXPORT
}

?>