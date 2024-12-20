<?php

class JamsViewModel{
    public array $LIST = Array();
    
    public string $next_jam_timer_code;
    public string $current_jam;
    public int $all_jams_loaded;
    public int $all_jams_count;
}

class JamViewModel{
    public array $entries = Array();
    public string $jam_id;
    public string $scheduler_user_id;
    public string $jam_number;
    public string $theme_id;
    public string $theme;
    public string $default_icon_url;
    public string $event_name;
    public string $start_time;
    public int $streamer_is_set;
    public string $streamer_user_id;
    public string $streamer_username;
    public string $streamer_user_display_name;
    public string $streamer_twitch_username;
    public string $state;
    public string $scheduler_username;
    public string $scheduler_display_name;
    public int $jam_deleted;
    public int $theme_visible;
    public string $jam_number_ordinal;
    public string $date;
    public string $time;
    public array $colors;
    public string $colors_input_string;
    public int $minutes_to_jam;
    public int $entries_count;
    public int $user_participated_in_jam;
    public int $user_is_streamer_for_jam;
    public int $in_straming_period;
    public int $first_jam;
    public int $entries_visible;
    public int $jam_started;
    public int $jam_ended;
    public int $jam_starts_soon;
    public int $time_left;
    public int $satisfaction;
    public float$satisfaction_average_score;
    public int $satisfaction_submitted_scores;
    public int $enough_scores_to_show_satisfaction;
    public int $score_minus_5;
    public int $score_minus_4;
    public int $score_minus_3;
    public int $score_minus_2;
    public int $score_minus_1;
    public int $score_0;
    public int $score_plus_1;
    public int $score_plus_2;
    public int $score_plus_3;
    public int $score_plus_4;
    public int $score_plus_5;
    public string $html_startdate;
    public int $can_user_submit_to_jam;
    public string $timer_code;
}

class JamColorViewModel{
    public int $number;
    public string $color;
    public string $color_hex;
}

?>