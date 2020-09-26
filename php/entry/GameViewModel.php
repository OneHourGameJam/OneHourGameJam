<?php

class GamesViewModel{
    public $LIST = Array();
    
    public $all_entries_count;
}

class GameViewModel{
    public $platforms = Array();
    public $id;
    public $jam_id;
    public $jam_number;
    public $title;
    public $description;
    public $author_user_id;
    public $screenshot_url;
    public $entry_deleted;
    public $title_url_encoded;
    public $color_background;
    public $color256_background_red;
    public $color256_background_green;
    public $color256_background_blue;
    public $color_lighter;
    public $color_border;
    public $color_text;
    public $jam_theme;
    public $author_username;
    public $author_username_url_encoded;
    public $author_display_name;
    public $has_screenshot;
    public $has_title;
    public $has_description;
}

class PlatformGameViewModel{
    public $platform_id;
    public $platform_name;
    public $platform_icon_url;
    public $url;
    public $platform_game_id;
}

?>