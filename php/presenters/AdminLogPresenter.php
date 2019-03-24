<?php

class AdminLogPresenter{
    public $AdminLogRender;

    function __construct(&$adminLogData) {
        $this->AdminLogRender = $this->RenderAdminLog($adminLogData);
    }

	function RenderAdminLog(&$adminLogData){
		AddActionLog("RenderAdminLog");
		StartTimer("RenderAdminLog");
		$render = Array();

		foreach($adminLogData->AdminLogModels as $i => $adminLogModel){
			$log = Array();
			
			$log["id"] = $adminLogModel->Id;
			$log["datetime"] = $adminLogModel->DateTime;
			$log["ip"] = $adminLogModel->Ip;
			$log["user_agent"] = $adminLogModel->UserAgent;
			$log["admin_username"] = $adminLogModel->AdminUsername;
			$log["subject_username"] = $adminLogModel->SubjectUsername;
			$log["log_type"] = $adminLogModel->LogType;
			$log["log_content"] = $adminLogModel->LogContent;

			$render[] = $log;
		}

		StopTimer("RenderAdminLog");
		return $render;
	}
}

?>