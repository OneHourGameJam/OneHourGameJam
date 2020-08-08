<?php
define("DB_TABLE_POLLOPTION", "poll_option");

define("DB_COLUMN_POLLOPTION_ID",        "option_id");
define("DB_COLUMN_POLLOPTION_POLL_ID",   "option_poll_id");
define("DB_COLUMN_POLLOPTION_POLL_TEXT", "option_poll_text");

class PollOptionDbInterface{
    private $database;
    private $publicColumns = Array(DB_COLUMN_POLLOPTION_ID, DB_COLUMN_POLLOPTION_POLL_ID, DB_COLUMN_POLLOPTION_POLL_TEXT);
    private $privateColumns = Array();

    function __construct(&$database) {
        $this->database = $database;
    }

    public function SelectPublicData(){
        AddActionLog("PollOptionDbInterface_SelectPublicData");
        StartTimer("PollOptionDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumnsPollOption)."
            FROM ".DB_TABLE_POLLOPTION.";
        ";

        StopTimer("PollOptionDbInterface_SelectPublicData");
        return $this->database->Execute($sql);
    }
}

?>