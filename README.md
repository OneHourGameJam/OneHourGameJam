# OneHourGameJam
Source code required to run your very own One hour game jam event.

# Requirements
Requires a web server with PHP 5.4 or later.

# Installing
What you need is a web server capable of running PHP, for example Apache. 

## 1: Apache

For windows: https://www.apachefriends.org/download.html
For linux: https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu
For mac: http://jason.pureconcepts.net/2012/10/install-apache-php-mysql-mac-os-x/

If these guides are out of date at the time you are reading this, simply google "Install apache on <OS>" where OS is the operating system you wish to use.

## 2: Deploying

Once you have your web server set up, copy the contents of this repository to it.

It is possible that you will need to change the permissions on the /data and /config dirrectories, so that your scripts will be able to write to them. This depends to how your web server is set up. If needed, change them to 777 and it should work. Tutorial: http://www.linux.org/threads/file-permissions-chmod.4094/. It is however likely that you will not need to do this. 

## 3. First startup

Open the page in your browser and register a user with the username "admin". That user will be the first site admin. It can schedule jams.

You will also likely want to delete the example jam. To do this, delete the file data/jams/jam_1.json

# Common tasks

Not everything is done in the best way, not everything has a pretty interface. It was much more important to add the functionality quickly and then add interfaces as needed. Interfaces for these tasks will be made soon<TM>, for now, follow the guide below.

## Registering

Done on the website

## Adding more admins

Done manually in php/global.php, add the admin to the list of admins there. 

## Adding jams

Done on the website - can be scheduled for the future in which case the theme is only revealed once the set time passes.

## Removing or editing jams and entries

Done manually by editing the files in data/jams. To delete the jam, simply delete the corresponding json file. To edit a jam or an entry, edit the file itself.

## Removing a user

Manually remove them from data/users.json

## Remotely log out a user or all users

Manually remove their entry from data/sessions.json; or delete the file to log out all users.

# Migrating to a new server

The site uses a site-wide pepper (for password and session id salting*). This pepper is generated the first time you launch the website, so it's unique for each distribution. It's saved in config/config.txt. Changing this will invalidate all session IDs and passwords. When migrating, it is important to preserve this pepper value. 

The simplest way to migrate is to simply copy the files from one server and paste them onto the new server. You might need to ensure file permissions are set correctly on the new server though (See "Deploying")

*The site also uses per-user salts, not just the site-wide pepper. 

# Solving performance issues

In order to ensure security of passwords and sessions, the site salts and peppers them, then hashes them with SHA256 between 10k and 20k times, by default. If the site takes too long to load for users, you might want to change these values to something lower. They can be found in php/authentication.php -> RegisterUser() and HashPassword()
