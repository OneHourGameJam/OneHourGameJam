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
    private $database;
    private $publicColumns = Array(DB_COLUMN_ASSET_ID, DB_COLUMN_ASSET_AUTHOR_USER_ID, DB_COLUMN_ASSET_TITLE, DB_COLUMN_ASSET_DESCRIPTION, DB_COLUMN_ASSET_TYPE, DB_COLUMN_ASSET_CONTENT, DB_COLUMN_ASSET_DELETED);
    private $privateColumns = Array(DB_COLUMN_ASSET_DATETIME, DB_COLUMN_ASSET_IP, DB_COLUMN_ASSET_USER_AGENT);

    function __construct(&$database) {
        $this->database = $database;
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
        return $this->database->Execute($sql);;
    }

    public function SelectWhereAuthorUserId($authorUserId){
        AddActionLog("AssetDbInterface_SelectWhereAuthorUserId");
        StartTimer("AssetDbInterface_SelectWhereAuthorUserId");

        $escapedAuthorUserId = $this->database->EscapeString($authorUserId);
        $sql = "
            SELECT ".DB_COLUMN_ASSET_ID.", ".DB_COLUMN_ASSET_DATETIME.", ".DB_COLUMN_ASSET_AUTHOR_USER_ID.", ".DB_COLUMN_ASSET_TITLE.", ".DB_COLUMN_ASSET_DESCRIPTION.", ".DB_COLUMN_ASSET_TYPE.", ".DB_COLUMN_ASSET_CONTENT.", ".DB_COLUMN_ASSET_DELETED."
            FROM ".DB_TABLE_ASSET."
            WHERE ".DB_COLUMN_ASSET_AUTHOR_USER_ID." = '$escapedAuthorUserId';";

        StopTimer("AssetDbInterface_SelectWhereAuthorUserId");
        return $this->database->Execute($sql);;
    }

    public function SelectSingleAsset($assetId){
        AddActionLog("AssetDbInterface_SelectSingleAsset");
        StartTimer("AssetDbInterface_SelectSingleAsset");

        $escapedAssetId = $this->database->EscapeString($assetId);
        $sql = "
            SELECT ".DB_COLUMN_ASSET_ID.", ".DB_COLUMN_ASSET_AUTHOR_USER_ID.", ".DB_COLUMN_ASSET_TITLE."
            FROM ".DB_TABLE_ASSET." a
            WHERE ".DB_COLUMN_ASSET_DELETED." != 1
              AND ".DB_COLUMN_ASSET_ID." = $escapedAssetId;
        ";
        StopTimer("AssetDbInterface_SelectSingleAsset");
        return $this->database->Execute($sql);;
    }

    public function Insert($ip, $userAgent, $authorUserId, $title, $description, $type, $assetURL){
        AddActionLog("AssetDbInterface_Insert");
        StartTimer("AssetDbInterface_Insert");

		$escapedIp = $this->database->EscapeString($ip);
		$escapedUserAgent = $this->database->EscapeString($userAgent);
		$escapedAuthorUserId = $this->database->EscapeString($authorUserId);
		$escapedTitle = $this->database->EscapeString($title);
		$escapedDescription = $this->database->EscapeString($description);
		$escapedType = $this->database->EscapeString($type);
		$escapedContent = $this->database->EscapeString($assetURL);
        
		$sql = "
            INSERT INTO ".DB_TABLE_ASSET."
            (".DB_COLUMN_ASSET_ID.",
            ".DB_COLUMN_ASSET_DATETIME.",
            ".DB_COLUMN_ASSET_IP.",
            ".DB_COLUMN_ASSET_USER_AGENT.",
            ".DB_COLUMN_ASSET_AUTHOR_USER_ID.",
            ".DB_COLUMN_ASSET_TITLE.",
            ".DB_COLUMN_ASSET_DESCRIPTION.",
            ".DB_COLUMN_ASSET_TYPE.",
            ".DB_COLUMN_ASSET_CONTENT.",
            ".DB_COLUMN_ASSET_DELETED.")
            VALUES
            (null,
            Now(),
            '$escapedIp',
            '$escapedUserAgent',
            $escapedAuthorUserId,
            '$escapedTitle',
            '$escapedDescription',
            '$escapedType',
            '$escapedContent',
            0);
        ";
        $this->database->Execute($sql);;

        StopTimer("AssetDbInterface_Insert");
    }

    public function Update($assetId, $authorUserId, $title, $description, $type, $content){
        AddActionLog("AssetDbInterface_Update");
        StartTimer("AssetDbInterface_Update");

		$escapedAssetId = $this->database->EscapeString($assetId);
		$escapedAuthorUserId = $this->database->EscapeString($authorUserId);
		$escapedTitle = $this->database->EscapeString($title);
		$escapedDescription = $this->database->EscapeString($description);
		$escapedType = $this->database->EscapeString($type);
		$escapedContent = $this->database->EscapeString($content);
        
		$sql = "
            UPDATE ".DB_TABLE_ASSET."
            SET
                ".DB_COLUMN_ASSET_AUTHOR_USER_ID." = $escapedAuthorUserId,
                ".DB_COLUMN_ASSET_TITLE." = '$escapedTitle',
                ".DB_COLUMN_ASSET_DESCRIPTION." = '$escapedDescription',
                ".DB_COLUMN_ASSET_TYPE." = '$escapedType',
                ".DB_COLUMN_ASSET_CONTENT." = '$escapedContent'
            WHERE ".DB_COLUMN_ASSET_ID." = $escapedAssetId;
        ";
        $this->database->Execute($sql);;

        StopTimer("AssetDbInterface_Update");
    }

    public function SoftDelete($assetId){
        AddActionLog("AssetDbInterface_SoftDelete");
        StartTimer("AssetDbInterface_SoftDelete");

		$escapedAssetId = $this->database->EscapeString($assetId);
        
        $sql = "
            UPDATE ".DB_TABLE_ASSET."
            SET
                ".DB_COLUMN_ASSET_DELETED." = 1
            WHERE ".DB_COLUMN_ASSET_ID." = $escapedAssetId;
        ";
        $this->database->Execute($sql);;

        StopTimer("AssetDbInterface_SoftDelete");
    }

    public function SelectPublicData(){
        AddActionLog("AssetDbInterface_SelectPublicData");
        StartTimer("AssetDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_ASSET.";
        ";

        StopTimer("AssetDbInterface_SelectPublicData");
        return $this->database->Execute($sql);;
    }
}

?>