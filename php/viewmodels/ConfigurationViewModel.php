<?php

class ConfigurationViewModel{
    public $LIST = Array();
    public $VALUES = Array();
    public $PRETTY_PRINT = Array();

    public $has_custom_brand_logo;
    public $has_custom_main_logo;
}

class SettingGroupViewModel{
    public $ENTRIES = Array();
    public $CATEGORY_ID;
    public $CATEGORY_HEADER;
}


class SettingViewModel{
    public $ENUM_OPTIONS = Array();
    public $KEY;
    public $VALUE;
    public $VALUE_HTML_ENCODED;
    public $CATEGORY;
    public $DESCRIPTION;
    public $DISABLED;
    public $EDITABLE;
    public $REQUIRED;
    public $TYPE;
    public $TYPE_TEXT;
    public $TYPE_NUMBER;
    public $TYPE_ENUM;
    public $TYPE_TEXTAREA;
}

class SettingEnumOptionViewModel{
    public $TEXT;
    public $VALUE;
    public $ENUM_SELECTED;
}

?>