<?php

class ConfigurationViewModel{
    public array $LIST = Array();
    public array $VALUES = Array();
    public array $PRETTY_PRINT = Array();

    public int $has_custom_brand_logo;
    public int $has_custom_main_logo;
}

class SettingGroupViewModel{
    public array $ENTRIES = Array();
    public string $CATEGORY_ID;
    public string $CATEGORY_HEADER;
}


class SettingViewModel{
    public array $ENUM_OPTIONS = Array();
    public string $KEY;
    public string $VALUE;
    public string $VALUE_HTML_ENCODED;
    public string $CATEGORY;
    public string $DESCRIPTION;
    public int $DISABLED;
    public int $EDITABLE;
    public int $REQUIRED;
    public string $TYPE;
    public int $TYPE_TEXT;
    public int $TYPE_NUMBER;
    public int $TYPE_ENUM;
    public int $TYPE_TEXTAREA;
    public int $USER_HAS_READ_PERMISSION;
    public int $USER_HAS_WRITE_PERMISSION;
}

class SettingEnumOptionViewModel{
    public string $TEXT;
    public string $VALUE;
    public int $ENUM_SELECTED;
}

?>