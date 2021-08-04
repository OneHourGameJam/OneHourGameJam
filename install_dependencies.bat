echo off

echo ~ Deleting downloaded dependencies...
if not exist "./dependencies" goto skip_delete_dependencies
    rd /s /q "./dependencies"
:skip_delete_dependencies
if not exist "./vendor" goto skip_delete_vendor
    rd /s /q "./vendor"
:skip_delete_vendor

echo ~ Downloading repositories from composer...
call composer install
call composer update

echo ~ Downloading repositories from git...
call git clone https://github.com/stuartlangridge/sorttable.git .\vendor\sorttable

echo ~ Downloading repositories from npm...
call npm i chart.js --prefix .\vendor\
call npm i sorttable --prefix .\vendor\
call npm i trumbowyg --prefix .\vendor\

echo ~ Copying dist files from dependencies...
mkdir dependencies

mkdir .\dependencies\jquery
copy .\vendor\components\jquery\jquery.min.js .\dependencies\jquery\jquery.min.js

mkdir .\dependencies\bootstrap
copy .\vendor\components\bootstrap\js\bootstrap.min.js .\dependencies\bootstrap\bootstrap.min.js
copy .\vendor\components\bootstrap\css\bootstrap.min.css .\dependencies\bootstrap\bootstrap.min.css

mkdir .\dependencies\mustache
xcopy .\vendor\mustache\mustache\src\Mustache .\dependencies\mustache /e

mkdir .\dependencies\chartjs
copy .\vendor\node_modules\chart.js\dist\chart.min.js .\dependencies\chartjs\chart.min.js

mkdir .\dependencies\sorttable
copy .\vendor\node_modules\sorttable\sorttable.js .\dependencies\sorttable\sorttable.js

mkdir .\dependencies\trumbowyg
copy .\vendor\node_modules\trumbowyg\dist\trumbowyg.min.js .\dependencies\trumbowyg\trumbowyg.min.js
copy .\vendor\node_modules\trumbowyg\dist\plugins\emoji\trumbowyg.emoji.min.js .\dependencies\trumbowyg\trumbowyg.emoji.min.js

echo ~ Removing vendor folder... 
rd /s /q "./vendor"

echo ~ Build Successful