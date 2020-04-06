#!/usr/bin/env bash

WWW_ROOT=/var/www/public

DB_USER=root
DB_PASS=root
DB_NAME=scotchbox
DB_HOST=localhost

# clean /var/www
sudo rm -Rf $WWW_ROOT

# symlink /var/www => /vagrant
ln -s /vagrant $WWW_ROOT


# Edit database config
DBCONFIG_PATH=$WWW_ROOT/config/dbconfig.php
cp $WWW_ROOT/config/dbconfig_example.php $DBCONFIG_PATH

sed -i "s/<DB_Address>/$DB_HOST/g" $DBCONFIG_PATH
sed -i "s/<DB_Username>/$DB_USER/g" $DBCONFIG_PATH
sed -i "s/<DB_Password>/$DB_PASS/g" $DBCONFIG_PATH
sed -i "s/<DB_DatabaseName>/$DB_NAME/g" $DBCONFIG_PATH


# Run apache2 as vagrant
echo "export APACHE_RUN_USER=vagrant" >> /etc/apache2/envvars
echo "export APACHE_RUN_GROUP=vagrant" >> /etc/apache2/envvars
service apache2 restart


# Init our database
mysql -u root --password=root scotchbox < $WWW_ROOT/SQL/versions/09_1hgj.sql

#ignore mysql errors
true
