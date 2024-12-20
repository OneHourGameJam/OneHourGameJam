<?php

class ThemesViewModel{
    public array $suggested_themes = Array();
    public array $top_themes = Array();

    public int $has_own_themes;
    public int $has_other_themes;
    public int $themes_must_be_pruned;
    public int $user_has_not_voted_for_all_themes;
    public int $themes_user_has_not_voted_for;
    public int $themes_user_has_not_voted_for_plural;
    public string $js_formatted_themes_popularity_themes_list;
    public string $js_formatted_themes_popularity_popularity_list;
    public string $js_formatted_themes_popularity_fill_color_list;
    public string $js_formatted_themes_popularity_border_color_list;
}

class ThemeViewModel{
    public string $theme;
    public int $votes_for;
    public int $votes_neutral;
    public int $votes_against;
    public int $votes_report;
    public int $votes_total;
    public int $votes_popularity;
    public int $votes_apathy;
    public int $popularity_num;
    public int $apathy_num;
    public int $has_enough_votes;
    public int $top_theme;
    public int $keep_theme;
    public string $apathy_color;
    public string $popularity_color;
    public int$banned;
    public string $author_user_id;
    public string $theme_id;
    public int $ThemeSelectionProbabilityByVoteDifferenceText;
    public int $UserThemeSelectionProbabilityByVoteDifferenceText;
    public int $ThemeSelectionProbabilityByPopularityText;
    public int $days_ago;
    public int $is_own_theme;
    public int $has_own_themes;
    public int $has_other_themes;
    public string $author_username;
    public string $author_display_name;
    public string $theme_button_id;
    public int $theme_visible;
    public int $is_old;
    public int $is_recent;
    public string $ideas;
    public int $is_marked_for_deletion;
}

class ThemeSmallViewModel{
    public string $theme;
    public int $votes_for;
    public int $votes_neutral;
    public int $votes_against;
    public int $votes_report;
    public int $votes_total;
    public int $votes_popularity;
    public int $votes_apathy;
    public int $popularity_num;
    public int $apathy_num;
    public int $has_enough_votes;
    public string $apathy_color;
    public string $popularity_color;
    public int $banned;
    public string $author_user_id;
    public string $theme_id;
    public int $days_ago;
    public string $author_username;
    public string $author_display_name;
}

?>