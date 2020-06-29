<?php

class AdminLogPresenter{
    public $AdminLogRender;

    function __construct(&$adminLogData, &$userData) {
        $this->AdminLogRender = $this->RenderAdminLog($adminLogData, $userData);
    }

	function RenderAdminLog(&$adminLogData, &$userData){
		AddActionLog("RenderAdminLog");
		StartTimer("RenderAdminLog");
		$render = Array();

		foreach($adminLogData->AdminLogModels as $i => $adminLogModel){
			$log = Array();
			
			$adminUsername = "";
			$subjectUsername = "";

			foreach($userData->UserModels as $i => $userModel){
				if($userModel->Id == $adminLogModel->AdminUserId){
					$adminUsername = $userModel->Username;
				}
				if($userModel->Id == $adminLogModel->SubjectUserId){
					$subjectUsername = $userModel->Username;
				}
			}

			$log["id"] = $adminLogModel->Id;
			$log["datetime"] = $adminLogModel->DateTime;
			$log["ip"] = $adminLogModel->Ip;
			$log["user_agent"] = $adminLogModel->UserAgent;
			$log["admin_username_override"] = $adminLogModel->AdminUsernameOverride;
			$log["admin_username"] = $adminUsername;
			$log["subject_username"] = $subjectUsername;
			$log["admin_user_id"] = $adminLogModel->AdminUserId;
			$log["subject_user_id"] = $adminLogModel->SubjectUserId;
			$log["log_type"] = $adminLogModel->LogType;
			$log["log_content"] = $adminLogModel->LogContent;

			$render[] = $log;
		}

		StopTimer("RenderAdminLog");
		return $render;
	}
}

?>