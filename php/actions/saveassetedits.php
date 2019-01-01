<?php

if(IsAdmin()){
    $assetID = $_POST["asset_id"];
    $author = $_POST["author"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $type = $_POST["type"];
    
    AddAsset($assetID, $author, $title, $description, $type);
}
$page = "assets";

?>