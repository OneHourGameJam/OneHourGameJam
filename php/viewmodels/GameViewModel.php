<?php

class GamesViewModel{
    public array $LIST = Array();
    
    public int $all_entries_count;
}

class GameViewModel{
    public array $platforms = Array();
    public string $id;
    public string $jam_id;
    public string $jam_number;
    public string $title;
    public string $description;
    public string $author_user_id;
    public string $screenshot_url;
    public int $entry_deleted;
    public string $title_url_encoded;
    public string $color_background;
    public int $color256_background_red;
    public int $color256_background_green;
    public int $color256_background_blue;
    public string $color_lighter;
    public string $color_border;
    public string $color_text;
    public string $jam_theme;
    public string $author_username;
    public string $author_username_url_encoded;
    public string $author_display_name;
    public int $has_screenshot;
    public int $has_title;
    public int $has_description;
}

class PlatformGameViewModel{
    public string $platform_id;
    public string $platform_name;
    public string $platform_icon_url;
    public string $url;
    public string $platform_game_id;
}

?>