<?php

include_once("php/global.php");
include_once("php/install_page.php");

$templateBasePath = "template/";

$dictionary = array();

$dictionary['page'] = array(
	"template_path" => $templateBasePath
);

RunInstallPage($dictionary);
$pageTemplateFile = $dictionary["template_file"];
$mustache->setPartials(Array(
	"footer" => file_get_contents($templateBasePath."footer.html"),
	"message" => file_get_contents($templateBasePath."message.html"),
	"page" => file_get_contents($templateBasePath.$pageTemplateFile),
));

print $mustache->render(file_get_contents($templateBasePath."install.html"), $dictionary);
?>