<?php

if(IsAdmin()){
    $assetID = $_POST["asset_id"];
    DeleteAsset($assetID);
}
$page = "assets";

?>