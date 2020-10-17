<?php

interface IUserDisplay{
	function HasUser($userId);
	function GetUserDisplayName($userId);
	function GetUserIdentifiableName($userId);
}

interface IEntryRenderer{
    function RenderEntry($entryId, &$userData, &$jamData, &$platformData, &$platformGameData, $renderDepth);
}

?>