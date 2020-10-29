<?php

class UsersViewModel{
    public $LIST = Array();
    
    public $missing_admin_candidate_votes;
    public $missing_admin_candidate_votes_number;
    public $all_authors_count;
}

class UserViewModel{
    public $preferences_list = Array();
    public $entries = Array();

    public $id;
    public $username;
    public $display_name;
    public $twitter;
    public $twitter_text_only;
    public $twitch;
    public $email;
    public $salt;
    public $password_hash;
    public $password_iterations;
    public $admin;
    public $user_preferences;
    public $preferences;
    public $days_since_last_login;
    public $days_since_last_admin_action;
    public $is_sponsored;
    public $sponsored_by;
    public $username_alphanumeric;
    public $recent_participation;
    public $entry_count;
    public $first_jam_number;
    public $last_jam_number;
    public $is_author;
    public $admin_candidate_recent_participation_check_pass;
    public $admin_candidate_total_participation_check_pass;
    public $system_suggestsed_admin_candidate;
    public $jams_since_last_participation;
    public $activity_jam_participation;
    public $activity_jam_participation_color;
    public $activity_jam_participation_inactive;
    public $activity_jam_participation_active;
    public $activity_jam_participation_highly_active;
    public $activity_login;
    public $activity_login_color;
    public $activity_login_inactive;
    public $activity_login_active;
    public $activity_login_highly_active;
    public $activity_administration;
    public $activity_administration_color;
    public $activity_administration_inactive;
    public $activity_administration_active;
    public $activity_administration_highly_active;
    public $votes_for;
    public $votes_neutral;
    public $votes_against;
    public $votes_vetos;
    public $is_vetoed;
    public $vote_type;
    public $vote_type_for;
    public $vote_type_neutral;
    public $vote_type_against;
    public $vote_type_sponsor;
    public $vote_type_veto;
    public $is_admin_candidate;
    public $is_admin;
}

?>