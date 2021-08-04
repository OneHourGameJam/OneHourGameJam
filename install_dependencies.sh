#!/bin/bash
echo Deleting downloaded dependencies...
if test -f "./dependencies"
	rm -rf "./dependencies"
fi
if test -f "./vendor"
	rm -rf ./vendor
fi

echo Downloading repositories from composer...

composer install
composer update


echo Downloading repositories from git...
git clone https://github.com/stuartlangridge/sorttable.git ./vendor/sorttable

echo Downloading repositories from npm...
npm i chart.js sorttable trumbowyg --prefix ./vendor

echo Copying dist files from dependencies...
mkdir -p ./dependencies/jquery

cp ./vendor/components/jquery/jquery.min.js ./dependencies/jquery/jquery.min.js

mkdir ./dependencies/bootstrap
cp ./vendor/components/bootstrap/js/bootstrap.min.js ./dependencies/bootstrap/bootstrap.min.js
cp ./vendor/components/bootstrap/css/bootstrap.min.css ./dependencies/bootstrap/bootstrap.min.css

mkdir ./dependencies/mustache
cp -R ./vendor/mustache/mustache/src/Mustache ./dependencies/mustache

mkdir ./dependencies/chartjs
cp ./vendor/node_modules/chart.js/dist/chart.min.js ./dependencies/chartjs/chart.min.js

mkdir ./dependencies/sorttable
cp ./vendor/node_modules/sorttable/sorttable.js ./dependencies/sorttable/sorttable.js

mkdir ./dependencies/trumbowyg
cp ./vendor/node_modules/trumbowyg/dist/trumbowyg.min.js ./dependencies/trumbowyg/trumbowyg.min.js
cp ./vendor/node_modules/trumbowyg/dist/plugins/emoji/trumbowyg.emoji.min.js ./dependencies/trumbowyg/trumbowyg.emoji.min.js

echo Removing vendor folder... 
rm -rf ./vendor

echo Build Successful