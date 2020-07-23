<?php

class ThemesViewModel{
    public $suggested_themes = Array();
    public $top_themes = Array();

    public $has_own_themes;
    public $has_other_themes;
    public $themes_must_be_pruned;
    public $user_has_not_voted_for_all_themes;
    public $themes_user_has_not_voted_for;
    public $themes_user_has_not_voted_for_plural;
    public $js_formatted_themes_popularity_themes_list;
    public $js_formatted_themes_popularity_popularity_list;
    public $js_formatted_themes_popularity_fill_color_list;
    public $js_formatted_themes_popularity_border_color_list;
}

class ThemeViewModel{
    public $theme;
    public $votes_for;
    public $votes_neutral;
    public $votes_against;
    public $votes_report;
    public $votes_total;
    public $votes_popularity;
    public $votes_apathy;
    public $popularity_num;
    public $apathy_num;
    public $has_enough_votes;
    public $top_theme;
    public $keep_theme;
    public $apathy_color;
    public $popularity_color;
    public $banned;
    public $author_user_id;
    public $theme_id;
    public $ThemeSelectionProbabilityByVoteDifferenceText;
    public $UserThemeSelectionProbabilityByVoteDifferenceText;
    public $ThemeSelectionProbabilityByPopularityText;
    public $days_ago;
    public $is_own_theme;
    public $has_own_themes;
    public $has_other_themes;
    public $author_username;
    public $author_display_name;
    public $theme_button_id;
    public $theme_visible;
    public $is_old;
    public $is_recent;
    public $ideas;
    public $is_marked_for_deletion;
}

?>