<?php
const DB_TABLE_POLLOPTION = "poll_option";

const DB_COLUMN_POLLOPTION_ID = "option_id";
const DB_COLUMN_POLLOPTION_POLL_ID = "option_poll_id";
const DB_COLUMN_POLLOPTION_POLL_TEXT = "option_poll_text";

class PollOptionDbInterface{
    private Database $database;
    private array $publicColumns = Array(DB_COLUMN_POLLOPTION_ID, DB_COLUMN_POLLOPTION_POLL_ID, DB_COLUMN_POLLOPTION_POLL_TEXT);
    private array $privateColumns = Array();

    function __construct(Database &$database) {
        $this->database = $database;
    }

    public function SelectPublicData(): mysqli_result
    {
        AddActionLog("PollOptionDbInterface_SelectPublicData");
        StartTimer("PollOptionDbInterface_SelectPublicData");

        $sql = "
            SELECT ".implode(",", $this->publicColumns)."
            FROM ".DB_TABLE_POLLOPTION.";
        ";

        StopTimer("PollOptionDbInterface_SelectPublicData");
        return $this->database->Execute($sql);
    }
}

?>