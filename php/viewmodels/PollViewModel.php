<?php

class PollsViewModel{
    public array $LIST = Array();
    public array $ACTIVE_POLLS = Array();
}

class PollViewModel{
    public array $preferences_list = Array();
    public array $entries = Array();

    public string $QUESTION;
    public string $POLL_ID;
    public string $USER_VOTED_IN_POLL;
    public array $OPTIONS;
    public int $IS_ACTIVE;
    public int $USERS_VOTED_IN_POLL;
    public string $DATE_STARTED;
    public string $DATE_ENDED;
    public int $TOTAL_VOTES;
    public string $js_formatted_options_list;
    public string $js_formatted_votes_list;
    public string $js_formatted_fill_color_list;
    public string $js_formatted_border_color_list;
    public string $js_formatted_user_votes_percentage_list;
}

class PollOptionsViewModel{
    public string $OPTION_ID;
    public string $USER_VOTED;
    public string $TEXT;
    public int $VOTES;
    public float $PERCENTAGE_OF_ALL_VOTES;
    public float $PERCENTAGE_OF_ALL_VOTES_DISPLAY;
    public float $PERCENTAGE_OF_USERS_VOTES;
    public float $PERCENTAGE_OF_USERS_VOTES_DISPLAY;
}

?>