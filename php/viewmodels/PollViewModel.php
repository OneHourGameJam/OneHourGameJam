<?php

class PollsViewModel{
    public $LIST = Array();
    public $ACTIVE_POLLS = Array();
}

class PollViewModel{
    public $preferences_list = Array();
    public $entries = Array();

    public $QUESTION;
    public $POLL_ID;
    public $USER_VOTED_IN_POLL;
    public $OPTIONS;
    public $IS_ACTIVE;
    public $USERS_VOTED_IN_POLL;
    public $DATE_STARTED;
    public $DATE_ENDED;
    public $TOTAL_VOTES;
    public $js_formatted_options_list;
    public $js_formatted_votes_list;
    public $js_formatted_fill_color_list;
    public $js_formatted_border_color_list;
    public $js_formatted_user_votes_percentage_list;
}

class PollOptionsViewModel{
    public $OPTION_ID;
    public $USER_VOTED;
    public $TEXT;
    public $VOTES;
    public $PERCENTAGE_OF_ALL_VOTES;
    public $PERCENTAGE_OF_ALL_VOTES_DISPLAY;
    public $PERCENTAGE_OF_USERS_VOTES;
    public $PERCENTAGE_OF_USERS_VOTES_DISPLAY;
}

?>