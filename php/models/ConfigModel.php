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
	public $Options = Array();
	public $AddedToDictionary;
}

class SettingEnumOptionModel{
    public $Text;
    public $Value;
}

class ConfigData{
    public $ConfigModels;

    function __construct(&$adminLogData) {
        $this->ConfigModels = $this->LoadConfig();
        $this->VerifyConfig($adminLogData);
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
            $configEntry->AddedToDictionary = $addedToDictionary;

            $configEntry->Options = Array();
            foreach($options as $i => $option){
                $settingEnumOptionModel = new SettingEnumOptionModel();
                $settingEnumOptionModel->Text = $option["TEXT"];
                $settingEnumOptionModel->Value = $option["VALUE"];
                $configEntry->Options[] = $settingEnumOptionModel;
            }

            $configModels[$key] = $configEntry;
        }

        StopTimer("LoadConfig");
        return $configModels;
    }

    function VerifyConfig(&$adminLogData) {
        AddActionLog("VerifyConfig");
        StartTimer("VerifyConfig");
    
        if (!isset($this->ConfigModels["PEPPER"]->Value) || strlen($this->ConfigModels["PEPPER"]->Value) < 1) {
            $this->UpdateConfig("PEPPER", GenerateSalt(), -1, "AUTOMATIC", $adminLogData);
        }
    
        if (!isset($this->ConfigModels["SESSION_PASSWORD_ITERATIONS"]->Value) || strlen($this->ConfigModels["SESSION_PASSWORD_ITERATIONS"]->Value) < 1) {
            $sessionPasswordIterations = GenerateUserHashIterations($this);
            $this->UpdateConfig("SESSION_PASSWORD_ITERATIONS", $sessionPasswordIterations, -1, "AUTOMATIC", $adminLogData);
        }
    
        StopTimer("VerifyConfig");
    }

    // Saves config to database, does not authorize to ensure VerifyConfig() continues to work
    function UpdateConfig($key, $value, $userID, $userUsernameOverride, &$adminLogData) {
        global $dbConn;
        AddActionLog("UpdateConfig");
        StartTimer("UpdateConfig");

        if($this->ConfigModels[$key]->Value != $value){
            $userIDClean = mysqli_real_escape_string($dbConn, $userID);
            $keyClean = mysqli_real_escape_string($dbConn, $key);
            $valueClean = mysqli_real_escape_string($dbConn, $value);
    
            $this->ConfigModels[$key]->Value = $value;
            $sql = "
                UPDATE config
                SET config_value = '$valueClean',
                config_lastedited = Now(),
                config_lasteditedby = '$userIDClean'
                WHERE config_key = '$keyClean';
            ";
            mysqli_query($dbConn, $sql);
            $sql = "";
    
            $adminLogData->AddToAdminLog("CONFIG_UPDATED", "Config value edited: $key = '$value'", "NULL", ($userID > 0) ? $userID : "NULL", ($userID > 0) ? "" : $userUsernameOverride);
        }
    
        StopTimer("UpdateConfig");
    }
}

?>