# OneHourGameJam
Source code required to run your very own One hour game jam event.

* Requirements
* Installing
* Common Tasks
* Contributing

# Requirements
Requires a web server with PHP 5.4 or later and MySQL.

# Installing
You can either install with vagrant or you can manually install (if you want to install on a production server)

## With Vagrant:

```
vagrant up
```

You can now find your local version of 1hgj at: http://192.168.48.49

This will setup a scotchbox vagrant box with 1hgj fully installed. You only need to set up the admin user (see below in "First Startup"). You will be able to develop as you would normally.

## Manually:
What you need is a web server capable of running PHP, for example Apache.

### 1: Apache

For windows: https://www.apachefriends.org/download.html
For linux: https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu
For mac: http://jason.pureconcepts.net/2012/10/install-apache-php-mysql-mac-os-x/

If these guides are out of date at the time you are reading this, simply google "Install apache on %OS%" where %OS% is the operating system you wish to use.

Also install MySQL (available in XAMPP), connect to it (via PHPMYADMIN or MySQL Workbench) and execute the contents of the provided sql file in SQL/1hgj.sql. This will create the database structure

Open config/dbconfig.php and edit the database connection details (you can copy and edit the example found at config/dbconfig_example.php).

### 2: Deploying

Once you have your web server set up, copy the contents of this repository to it.

It is possible that you will need to change the permissions on the /data and /config dirrectories, so that your scripts will be able to write to them. This depends to how your web server is set up. If needed, change them to 777 and it should work. Tutorial: http://www.linux.org/threads/file-permissions-chmod.4094/. It is however likely that you will not need to do this.

## First startup

Open the page in your browser and register a user. This first user will be a site administrator.

# Common tasks

This project is in-development, so some administrative tasks do not yet have a pretty interface. It was much more important to add the functionality quickly and then add interfaces as needed. Interfaces for these tasks will be made soon<TM>, for now, follow the guide below.

## Registering

Done on the website. The first registered account will have admin rights.

## Adding more admins

Adding or removing administrators can be done via the "Manage users" administrative menu.

## Adding jams

Done on the website - can be scheduled for the future in which case the theme is only revealed once the set time passes.

## Removing or editing jams and entries

Editing jams can be done via the website's "edit content" menu. Deleting is done by directly editing the database. Set the jam_deleted volumn to 1.

## Removing a user

Manually remove them from the database table 'user'

## Remotely log out a user or all users

Manually remove their entry from data/sessions.json; or delete the file to log out all users.

# Migrating to a new server

The site uses a site-wide pepper (for password and session id salting*). This pepper is generated the first time you launch the website, so it's unique for each deployment. It's saved in config/config.txt. Changing this will invalidate all session IDs and passwords. When migrating, it is important to preserve this pepper value.

The simplest way to migrate is to simply copy the files from one server and paste them onto the new server. You might need to ensure file permissions are set correctly on the new server though (See "Deploying")

*The site also uses per-user salts, not just the site-wide pepper.

# Solving performance issues

In order to ensure security of passwords and sessions, the site salts and peppers them, then hashes them with SHA256 between 10k and 20k times, by default. If the site takes too long to load for users, you might want to change these values to something lower. They can be found in php/authentication.php -> RegisterUser(), HashPassword() and EditUserPassword().

It's also possible for the number of sessions to build up. If data/sessions.json becomes too large it may cause performance issues, consider deleting data/sessions.json if this happen. Note that this logs out all users.

# Contributing

You're welcome to contribute to this project whether you know how to code or not. 

If you know how to code, please search the issues by technology you know:
* [HTML / CSS](https://github.com/OneHourGameJam/OneHourGameJam/issues?q=is%3Aissue+is%3Aopen+label%3AHTML%2FCSS)
* [PHP](https://github.com/OneHourGameJam/OneHourGameJam/issues?q=is%3Aissue+is%3Aopen+label%3APHP)
* [JavaScript](https://github.com/OneHourGameJam/OneHourGameJam/issues?q=is%3Aissue+is%3Aopen+label%3AJavaScript)
* [MySQL / MariaDB](https://github.com/OneHourGameJam/OneHourGameJam/issues?q=is%3Aissue+is%3Aopen+label%3AMySQL%2FMariaDB)

Issues found in the [Good First Issue](https://github.com/OneHourGameJam/OneHourGameJam/issues?q=is%3Aissue+is%3Aopen+label%3A%22Good+First+Issue%22) label are probably a good place to start. The issues there tend to require less familiarity with how the project works in-depth.

If you don't know how to code, that's okay. You can still offer a lot of value by reading and commenting on [Issues](https://github.com/OneHourGameJam/OneHourGameJam/issues), identifying issues which are out of date or no longer relevant, and reporting new issues - either bugs or suggestions. 

