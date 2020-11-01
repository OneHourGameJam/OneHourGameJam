<?php
namespace Plugins\AdminLog;

class AdminLogPresenter{
	
	public static function RenderAdminLog(&$adminLogData, \IUserDisplay &$userData){
		AddActionLog("RenderAdminLog");
		StartTimer("RenderAdminLog");
		$adminLogsViewModel = new AdminLogsViewModel();

		foreach($adminLogData->AdminLogModels as $i => $adminLogModel){
			$adminUsername = "";
			$subjectUsername = "";

			if($userData->HasUser($adminLogModel->AdminUserId)){
				$adminUsername = $userData->GetUserIdentifiableName($adminLogModel->AdminUserId);
			}

			if($userData->HasUser($adminLogModel->SubjectUserId)){
				$subjectUsername = $userData->GetUserIdentifiableName($adminLogModel->SubjectUserId);
			}

			$adminLogViewModel = new AdminLogViewModel();

			$adminLogViewModel->id = $adminLogModel->Id;
			$adminLogViewModel->datetime = $adminLogModel->DateTime;
			$adminLogViewModel->ip = $adminLogModel->Ip;
			$adminLogViewModel->user_agent = $adminLogModel->UserAgent;
			$adminLogViewModel->admin_username_override = $adminLogModel->AdminUsernameOverride;
			$adminLogViewModel->admin_username = $adminUsername;
			$adminLogViewModel->subject_username = $subjectUsername;
			$adminLogViewModel->admin_user_id = $adminLogModel->AdminUserId;
			$adminLogViewModel->subject_user_id = $adminLogModel->SubjectUserId;
			$adminLogViewModel->log_type = $adminLogModel->LogType;
			$adminLogViewModel->log_content = $adminLogModel->LogContent;

			$adminLogsViewModel->logs[] = $adminLogViewModel;
		}

		StopTimer("RenderAdminLog");
		return $adminLogsViewModel;
	}
}

?>