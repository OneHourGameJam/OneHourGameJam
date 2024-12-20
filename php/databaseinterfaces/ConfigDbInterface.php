<?php
const DB_TABLE_CONFIG = "config";

const DB_COLUMN_CONFIG_ID = "config_id";
const DB_COLUMN_CONFIG_LASTEDITED = "config_lastedited";
const DB_COLUMN_CONFIG_LASTEDITEDBY = "config_lasteditedby";
const DB_COLUMN_CONFIG_KEY = "config_key";
const DB_COLUMN_CONFIG_VALUE = "config_value";
const DB_COLUMN_CONFIG_CATEGORY = "config_category";
const DB_COLUMN_CONFIG_DESCRIPTION = "config_description";
const DB_COLUMN_CONFIG_TYPE = "config_type";
const DB_COLUMN_CONFIG_OPTIONS = "config_options";
const DB_COLUMN_CONFIG_EDITABLE = "config_editable";
const DB_COLUMN_CONFIG_REQUIRED = "config_required";
const DB_COLUMN_CONFIG_ADDED_TO_DICTIONARY = "config_added_to_dictionary";
const DB_COLUMN_CONFIG_REQUIRED_PERMISSION_READ = "config_required_permission_read";
const DB_COLUMN_CONFIG_REQUIRED_PERMISSION_WRITE = "config_required_permission_write";

class ConfigDbInterface{
    private Database $database;
    private array $publicColumns = Array(DB_COLUMN_CONFIG_ID, DB_COLUMN_CONFIG_KEY, DB_COLUMN_CONFIG_VALUE, DB_COLUMN_CONFIG_CATEGORY, DB_COLUMN_CONFIG_DESCRIPTION, DB_COLUMN_CONFIG_TYPE, DB_COLUMN_CONFIG_OPTIONS, DB_COLUMN_CONFIG_EDITABLE, DB_COLUMN_CONFIG_REQUIRED, DB_COLUMN_CONFIG_ADDED_TO_DICTIONARY, DB_COLUMN_CONFIG_REQUIRED_PERMISSION_READ, DB_COLUMN_CONFIG_REQUIRED_PERMISSION_WRITE);
    private array $privateColumns = Array(DB_COLUMN_CONFIG_LASTEDITED, DB_COLUMN_CONFIG_LASTEDITEDBY);

    function __construct(Database &$database) {
        $this->database = $database;
    }

    public function SelectAll(): mysqli_result
    {
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

    public function Update($key, $value, $userID): void
    {
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

    public function SelectPublicData(): mysqli_result
    {
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