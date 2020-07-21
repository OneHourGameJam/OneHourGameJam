<?php

class AdminLogsViewModel{
	public $logs = Array();
}

class AdminLogViewModel{
    public $id;
    public $datetime;
    public $ip;
    public $user_agent;
    public $admin_username_override;
    public $admin_username;
    public $subject_username;
    public $admin_user_id;
    public $subject_user_id;
    public $log_type;
    public $log_content;
}

?>