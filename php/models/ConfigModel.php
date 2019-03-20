<?php

class ConfigModel{
	public $Key;
	public $Value;
	public $Category;
	public $Description;
	public $Disabled;
	public $Editable;
	public $Required;
	public $Type;
	public $Options;
	public $AddedToDictionary;
}

class ConfigData{
    public $ConfigModels;

    function __construct() {
        $this->ConfigModels = $this->LoadConfig();
    }

    function LoadConfig(){
        global $dbConn;
        AddActionLog("LoadConfig");
        StartTimer("LoadConfig");

        $configModels = Array();

        $sql = " SELECT * FROM config ORDER BY config_id; ";
        $data = mysqli_query($dbConn, $sql);
        $sql = "";

        while($configData = mysqli_fetch_array($data)) {
            $key = $configData["config_key"];
            $value = $configData["config_value"];
            $category = $configData["config_category"];
            $description = $configData["config_description"];
            $type = $configData["config_type"];
            $options = json_decode($configData["config_options"], true);
            $editable = $configData["config_editable"];
            $required = $configData["config_required"];
            $addedToDictionary = $configData["config_added_to_dictionary"];

            $configEntry = new ConfigModel();
            $configEntry->Key = $key;
            $configEntry->Value = $value;
            $configEntry->Category = $category;
            $configEntry->Description = $description;
            $configEntry->Disabled = !$editable;
            $configEntry->Editable = $editable;
            $configEntry->Required = $required;
            $configEntry->Type = $type;
            $configEntry->Options = $options;
            $configEntry->AddedToDictionary = $addedToDictionary;

            $configModels[$key] = $configEntry;
        }

        $configModels = $this->VerifyConfig($configModels);

        StopTimer("LoadConfig");
        return $configModels;
    }

    function VerifyConfig($configModels) {
        AddActionLog("VerifyConfig");
        StartTimer("VerifyConfig");
    
        if (!isset($configModels["PEPPER"]->Value) || strlen($configModels["PEPPER"]->Value) < 1) {
            $configModels = $this->UpdateConfig($configModels, "PEPPER", GenerateSalt(), -1, "AUTOMATIC");
        }
    
        if (!isset($configModels["SESSION_PASSWORD_ITERATIONS"]->Value) || strlen($configModels["SESSION_PASSWORD_ITERATIONS"]->Value) < 1) {
            $sessionPasswordIterations = GenerateUserHashIterations($configModels);
            $configModels = $this->UpdateConfig($configModels, "SESSION_PASSWORD_ITERATIONS", $sessionPasswordIterations, -1, "AUTOMATIC");
        }
    
        StopTimer("VerifyConfig");
        return $configModels;
    }

    // Saves config to database, does not authorize to ensure VerifyConfig() continues to work
    function UpdateConfig($configModels, $key, $value, $userID, $userUsername) {
        global $dbConn;
        AddActionLog("UpdateConfig");
        StartTimer("UpdateConfig");
    
        if($configModels[$key]->Value != $value){
            $userIDClean = mysqli_real_escape_string($dbConn, $userID);
            $keyClean = mysqli_real_escape_string($dbConn, $key);
            $valueClean = mysqli_real_escape_string($dbConn, $value);
    
            $configModels[$key]->Value = $value;
            $sql = "
                UPDATE config
                SET config_value = '$valueClean',
                config_lastedited = Now(),
                config_lasteditedby = '$userIDClean'
                WHERE config_key = '$keyClean';
            ";
            mysqli_query($dbConn, $sql);
            $sql = "";
    
            AddToAdminLog("CONFIG_UPDATED", "Config value edited: $key = '$value'", "", $userUsername);
        }
    
        StopTimer("UpdateConfig");
        return $configModels;
    }
}

?>