<?php

class AssetsViewModel{
    public array $LIST = Array();
}

class AssetViewModel{
    public string $id;
    public string $author_user_id;
    public string $title;
    public string $description;
    public string $type;
    public string $content;
    public string $author_username;
    public string $author_display_name;
    public int $is_audio;
    public int $is_image;
    public int $is_text;
    public int $is_link;
    public int $is_file;
    public int $is_other;
}

?>