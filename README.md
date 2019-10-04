# OneHourGameJam
Source code required to run your very own One hour game jam event.

# Content
* Requirements
* Installing
* Common Tasks
* Migrating to a new server
* Contributing
* Overview of site structure, order of operations

# Requirements
Requires a web server with PHP 5.6 or later and MySQL or MariaDB. Older versions work on PHP 5.4.

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

If you prefer to follow a video tutorial, please watch the following:

[![OneHourGameJam Local Copy Setup](http://img.youtube.com/vi/NiaaSXDoVf0/0.jpg)](http://www.youtube.com/watch?v=NiaaSXDoVf0 "OneHourGameJam Local Copy Setup")

### 1: Download XAMPP

* Go to [Apache](https://www.apachefriends.org/download.html) and download a version for your operating system (For PHP 5.6, not a VM version)
* Install (On Windows this must be to `C:/XAMPP`)

### 2: Start Server

* Start XAMPP Control Panel
* Find Apache and MySQL services in the panel and Start them (If it fails to start, shut down Skype, the two programs conflict)

### 3: Set up web server content

* Check out this repository to `C://XAMPP/htdocs/onehourgamejam` (Windows) or `/Applications/XAMPP/htdocs/onehourgamejam` (Mac)

### 4: Set up Database

* Go to http://localhost/phpmyadmin
* Click the `+` in the left column to create a database. Name it `onehourgamejam` or something and choose `utf8mb4_bin` as the character set
* Select the database in the list on the left
* Click the `SQL` tab
* Open the file `SQL/versions/09_1hgj.sql` and copy its contents into the field in the SQL tab in phpmyadmin, Click Go
* Rename the file `config/dbconfig_example.php` to `config/dbconfig.php`
* Open the file and set the content to: (If you used a different database name in step 4.2, replace "onehourgamejam" with that)
```
$dbAddress = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbDatabaseName = "onehourgamejam";
```

### 5: Open the site

* Go to http://localhost/onehourgamejam

## First startup

Open the page in your browser and register a user. This first user will be a site administrator.

## Subsequent startups

You only need to do steps `2: Start Web Server` and `### 5: Open the site`

# Common tasks

This project is in-development, so some administrative tasks do not yet have a pretty interface. It was much more important to add the functionality quickly and then add interfaces as needed. Interfaces for these tasks will be made soon<TM>, for now, follow the guide below.

## Registering

Done on the website. The first registered account will have admin rights.

## Adding more admins

Adding or removing administrators can be done via the "Manage users" administrative menu.

## Adding jams

Done on the website - can be scheduled for the future in which case the theme is only revealed once the set time passes. An automatic jam scheduler feature also exists, set up in site configuration.

## Removing or editing jams and entries

Editing jams can be done via the website's "edit content" menu.

## Removing a user

Manually remove them from the database table 'user'

## Remotely log out a user or all users

Manually remove the user's entries from the database table 'session'

# Migrating to a new server

The site uses a site-wide pepper (for password and session id salting*). This pepper is generated the first time you launch the website, so it's unique for each deployment. It's saved in the config table. Changing this will invalidate all session IDs and passwords. When migrating, it is important to preserve this pepper value and the session password iterations value.

*The site also uses per-user salts, not just the site-wide pepper.

# Contributing

You're welcome to contribute to this project whether you know how to code or not. 

If you know how to code, please search the issues by technology you know:
* [HTML / CSS](https://github.com/OneHourGameJam/OneHourGameJam/issues?q=is%3Aissue+is%3Aopen+label%3AHTML%2FCSS)
* [PHP](https://github.com/OneHourGameJam/OneHourGameJam/issues?q=is%3Aissue+is%3Aopen+label%3APHP)
* [JavaScript](https://github.com/OneHourGameJam/OneHourGameJam/issues?q=is%3Aissue+is%3Aopen+label%3AJavaScript)
* [MySQL / MariaDB](https://github.com/OneHourGameJam/OneHourGameJam/issues?q=is%3Aissue+is%3Aopen+label%3AMySQL%2FMariaDB)

Issues found in the [Good First Issue](https://github.com/OneHourGameJam/OneHourGameJam/issues?q=is%3Aissue+is%3Aopen+label%3A%22Good+First+Issue%22) label are probably a good place to start. The issues there tend to require less familiarity with how the project works in-depth.

If you don't know how to code, that's okay. You can still offer a lot of value by reading and commenting on [Issues](https://github.com/OneHourGameJam/OneHourGameJam/issues), identifying issues which are out of date or no longer relevant, and reporting new issues - either bugs or suggestions. 

If you need help, please join us on Discord, the URL to which can be found on https://onehourgamejam.com/

# Overview of site structure, order of operations

- The entry point for every page load (except the API) is through index.php. From there it passes through stages:
  - **Site code aggregation** (Giving access to all php files which are needed)
    - php/site.php *includes all other required php files*
    - php/global.php *defintes globally accessible variables*
    - php/dependencies.php *definies which pages exist, a user's required authorization level to access them (guest, user, admin), and which text rendering needs to happen for that page (so we don't render all jams when viewing themes*
  - **Data retrieval** (Data is copied from the database into PHP arrays)
    - php/init.php *retrieves almost all data from the database and puts it into "Data" objects (For example `$userData = new UserData();`, irrespective of if it's needed by the page to be rendered of not*
    - php/models.php *Each Data object contains a list of Models. Each model corresponds to an entry. For example the `UserData` data object contains a list called `$UserModels` of `UserModel` objects, where each `UserModel` corresponds to a site user.*
  - **Site Actions**
    - php/actions.php *If there is a pending site action (login, game submission, etc.) then we perform it at this stage.*
    - php/actions/... *The associated actions.php file is loaded and the action is performed (Site actions are configured in php/models/SiteActionModel.php)*
    - *After the action is performed, the page is redirected to the action's result's redirect URL, as configured in SiteActionModel.php. This page load stops here, and after the redirect happens, the process begins again from the top, this time without a pending site action.*
  - **Text Rendering**
    - php/init.php -> php/dependencies.php *Dependencies are retrieved from the list `$pageSettings`. These determine which bits of the rendered dictionary will be rendered, and to what level of detail (`Render Depth`). For example the main page needs each jam to also render the games in that jam, so the "main" page defines `"RenderJams" => RENDER_DEPTH_JAMS_GAMES,`, but the jams list page doesn't need games rendered, so it defines `"RenderAllJams" => RENDER_DEPTH_JAMS` which means that the games within each jam will not be rendered. The difference between `RenderJams` and `RenderAllJams` is not important for this example*
    - php/presenter or RenderXYZ() functions *All the required text rendering for a page happens next. Data objects, Models and render depth information is supplied as requires. The goal is to move all RenderXYZ() functions (like `RenderUsers(..)`) to Presenters, like `AdminLogPresenter`*
    - php/page.php RenderPageSpecific(..) *Some pages have something special about them, this is handled in a catch-all `RenderPageSpecific(..)` method.*
  - **Templates**
    - index.php *All rendered text can now be found in `$dictionary`. Some general purpose templates (called partials) are loaded along with the page template to be rendered. The fully rendered page is then printed.*
    - template/... *These mustache + html template files contain the structure and design of the page. See Mustache documentation for more detail on the {{XYZ}} tags, but they correspond to the structure of the contents of `$dictionary`, which was rendered into by the Text Rendering step.*
