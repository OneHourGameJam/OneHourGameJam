<?php

class AdminVotePresenter{

	public static function RenderAdminVote(&$adminVoteData, $userId){
		AddActionLog("RenderAdminVote");
        StartTimer("RenderAdminVote");

        $adminVoteViewModel = new AdminVoteViewModel();

        $adminVoteViewModel->votes_for = 0;
        $adminVoteViewModel->votes_neutral = 0;
        $adminVoteViewModel->votes_against = 0;
        $adminVoteViewModel->votes_vetos = 0;
        foreach($adminVoteData->AdminVoteModels as $j => $adminVoteModel){
            if($adminVoteModel->SubjectUserId == $userId){
                switch($adminVoteModel->VoteType){
                    case ADMINVOTE_FOR:
                        $adminVoteViewModel->votes_for += 1;
                        break;
                    case ADMINVOTE_NEUTRAL:
                        $adminVoteViewModel->votes_neutral += 1;
                        break;
                    case ADMINVOTE_AGAINST:
                        $adminVoteViewModel->votes_against += 1;
                        break;
                    case ADMINVOTE_SPONSOR:
                        $adminVoteViewModel->votes_for += 1;
                        $adminVoteViewModel->is_sponsored = 1;
                        break;
                    case ADMINVOTE_VETO:
                        $adminVoteViewModel->votes_vetos += 1;
                        $adminVoteViewModel->is_vetoed = 1;
                        break;
                }
            }
        }
        
        foreach($adminVoteData->LoggedInUserAdminVotes as $j => $adminVoteModel){
            if($adminVoteModel->SubjectUserId == $userId){
                $adminVoteViewModel->vote_type = $adminVoteModel->VoteType;

                switch($adminVoteModel->VoteType){
                    case ADMINVOTE_FOR:
                        $adminVoteViewModel->vote_type_for = 1;
                        break;
                    case ADMINVOTE_NEUTRAL:
                        $adminVoteViewModel->vote_type_neutral = 1;
                        break;
                    case ADMINVOTE_AGAINST:
                        $adminVoteViewModel->vote_type_against = 1;
                        break;
                    case ADMINVOTE_SPONSOR:
                        $adminVoteViewModel->vote_type_sponsor = 1;
                        break;
                    case ADMINVOTE_VETO:
                        $adminVoteViewModel->vote_type_veto = 1;
                        break;
                }
            }
        }
        StopTimer("RenderAdminVote");
    }
}

?>