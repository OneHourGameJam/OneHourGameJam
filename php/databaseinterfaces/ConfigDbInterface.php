<?php
define("DB_TABLE_CONFIG", "config");

define("DB_COLUMN_CONFIG_ID",                           "config_id");
define("DB_COLUMN_CONFIG_LASTEDITED",                   "config_lastedited");
define("DB_COLUMN_CONFIG_LASTEDITEDBY",                 "config_lasteditedby");
define("DB_COLUMN_CONFIG_KEY",                          "config_key");
define("DB_COLUMN_CONFIG_VALUE",                        "config_value");
define("DB_COLUMN_CONFIG_CATEGORY",                     "config_category");
define("DB_COLUMN_CONFIG_DESCRIPTION",                  "config_description");
define("DB_COLUMN_CONFIG_TYPE",                         "config_type");
define("DB_COLUMN_CONFIG_OPTIONS",                      "config_options");
define("DB_COLUMN_CONFIG_EDITABLE",                     "config_editable");
define("DB_COLUMN_CONFIG_REQUIRED",                     "config_required");
define("DB_COLUMN_CONFIG_ADDED_TO_DICTIONARY",          "config_added_to_dictionary");
define("DB_COLUMN_CONFIG_REQUIRED_PERMISSION_READ",     "config_required_permission_read");
define("DB_COLUMN_CONFIG_REQUIRED_PERMISSION_WRITE",    "config_required_permission_write");

class ConfigDbInterface{
    private $database;
    private $publicColumns = Array(DB_COLUMN_CONFIG_ID, DB_COLUMN_CONFIG_KEY, DB_COLUMN_CONFIG_VALUE, DB_COLUMN_CONFIG_CATEGORY, DB_COLUMN_CONFIG_DESCRIPTION, DB_COLUMN_CONFIG_TYPE, DB_COLUMN_CONFIG_OPTIONS, DB_COLUMN_CONFIG_EDITABLE, DB_COLUMN_CONFIG_REQUIRED, DB_COLUMN_CONFIG_ADDED_TO_DICTIONARY, DB_COLUMN_CONFIG_REQUIRED_PERMISSION_READ, DB_COLUMN_CONFIG_REQUIRED_PERMISSION_WRITE);
    private $privateColumns = Array(DB_COLUMN_CONFIG_LASTEDITED, DB_COLUMN_CONFIG_LASTEDITEDBY);

    function __construct(&$database) {
        $this->database = $database;
    }

    public function SelectAll(){
        AddActionLog("ConfigDbInterface_SelectAll");
        StartTimer("ConfigDbInterface_SelectAll");

        $sql = "
            SELECT * 
            FROM ".DB_TABLE_CONFIG." 
            ORDER BY ".DB_COLUMN_CONFIG_ID.";
        ";
        
        StopTimer("ConfigDbInterface_SelectAll");
        return $this->database->Execute($sql);;
    }

    public function Update($key, $value, $userID){
        AddActionLog("ConfigDbInterface_Update");
        StartTimer("ConfigDbInterface_Update");

        $escapedUserId = $this->database->EscapeString($userID);
        $escapedKey = $this->database->EscapeString($key);
        $escapedValue = $this->database->EscapeString($value);

        $sql = "
            UPDATE ".DB_TABLE_CONFIG."
            SET 
                ".DB_COLUMN_CONFIG_VALUE." = '$escapedValue',
                ".DB_COLUMN_CONFIG_LASTEDITED." = Now(),
                ".DB_COLUMN_CONFIG_LASTEDITEDBY." = '$escapedUserId'
            WHERE ".DB_COLUMN_CONFIG_KEY." = '$escapedKey';
        ";
        $this->database->Execute($sql);;

        StopTimer("ConfigDbInterface_Update");
    }

    public function SelectPublicData(){
        AddActionLog("ConfigDbInterface_SelectPublicData");
        StartTimer("ConfigDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_CONFIG.";
        ";

        StopTimer("ConfigDbInterface_SelectPublicData");
        return $this->database->Execute($sql);;
    }
}

?>