<?php

class JamsViewModel{
    public $LIST = Array();
    
    public $next_jam_timer_code;
    public $current_jam;
    public $all_jams_loaded;
    public $all_jams_count;
}

class JamViewModel{
    public $entries = Array();
    public $jam_id;
    public $scheduler_user_id;
    public $jam_number;
    public $theme_id;
    public $theme;
    public $default_icon_url;
    public $start_time;
    public $streamer_is_set;
    public $streamer_user_id;
    public $streamer_username;
    public $streamer_user_display_name;
    public $streamer_twitch_username;
    public $state;
    public $scheduler_username;
    public $scheduler_display_name;
    public $jam_deleted;
    public $theme_visible;
    public $jam_number_ordinal;
    public $date;
    public $time;
    public $colors;
    public $colors_input_string;
    public $minutes_to_jam;
    public $entries_count;
    public $user_participated_in_jam;
    public $user_is_streamer_for_jam;
    public $in_straming_period;
    public $first_jam;
    public $entries_visible;
    public $jam_started;
    public $time_left;
    public $satisfaction;
    public $satisfaction_average_score;
    public $satisfaction_submitted_scores;
    public $enough_scores_to_show_satisfaction;
    public $score_minus_5;
    public $score_minus_4;
    public $score_minus_3;
    public $score_minus_2;
    public $score_minus_1;
    public $score_0;
    public $score_plus_1;
    public $score_plus_2;
    public $score_plus_3;
    public $score_plus_4;
    public $score_plus_5;
    public $html_startdate;
    public $can_user_submit_to_jam;
    public $timer_code;
}

class JamColorViewModel{
    public $number;
    public $color;
    public $color_hex;
}

?>