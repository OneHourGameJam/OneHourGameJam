# OneHourGameJam
Source code required to run your very own One hour game jam event.

# Content
* Requirements
* Installing
* Common Tasks
* Migrating to a new server
* Contributing
* Overview of site structure, order of operations
* Glossary of development terms

# Requirements
Requires a web server with PHP 5.6 or later and MySQL or MariaDB.

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

### 4: Install website

* Go to https://localhost/onehourgamejam/ - this should point you to the install page
* Enter the following data into the form:
  * Host: localhost
  * Username: Root
  * leave Password blank
  * Database Name: onehourgamejam
  * tick "Initialise database"
* Press "Setup Database"
* Go to http://localhost/onehourgamejam (A message confirming the database upgrade may appear)

### 5: Open the site

* Go to http://localhost/onehourgamejam
* Create your first user (The first user created will be an administrator)
  * Press "Log in / register" in the menu
  * Enter a username and password
  * Press the "Log in / register" button

## Subsequent startups

* Only the server must be started: See **2: Start Server**

# Common tasks

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

# In-depth overview of site structure, order of operations

This section aims to describe how the site functions from a high level, intended for developers who wish to contribute to the site's development.

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

# Glossary of development terms

- **Global variable** - Any variable that's not passed into a function, and is instead accessed with a `global $varName` at the start of a function - these are being phased out in favor of providing data through function arguments, return values and properly scoped variables. Global variables are defined in [php/global.php](https://github.com/OneHourGameJam/OneHourGameJam/blob/master/php/global.php)
- **Dependencies** - Settings for a particular page, including the data rendering which is necessary for it to be displayed correctly. Defined in [php/dependencies.php](https://github.com/OneHourGameJam/OneHourGameJam/blob/master/php/dependencies.php)
- **Data (Object)** - An object which contains multiple models and sometimes related derived data (such as games for each user in addition to a list of all games). Each model in turn corresponds to a data entry - For example a UserModel corresponds to an individual user, while UserData is the representation of all users. These are defined in [php/models](https://github.com/OneHourGameJam/OneHourGameJam/tree/master/php/models) and created in [php/init.php](https://github.com/OneHourGameJam/OneHourGameJam/tree/master/php/init.php)
- **Model** - A simple data object, which corresponds to one entry, for example an individual user, an indivual jam, an individual game, etc. These are defined in [php/models](https://github.com/OneHourGameJam/OneHourGameJam/tree/master/php/models)
- **ViewModel** - A simple data object, which is created by a Presenter. ViewModels contain the rendered text used with templates to create the rendered web page. [php/viewmodels](https://github.com/OneHourGameJam/OneHourGameJam/tree/master/php/viewmodels)
- **Database Interface** - These objects are intended to abstract away SQL code from the rest of the site. They are used by Data objects to acquire the data they need, and by Site Actions to perform the required modifications the action requires. [php/databaseinterfaces](https://github.com/OneHourGameJam/OneHourGameJam/tree/master/php/databaseinterfaces)
- **Site Actions** - Any "write" operation on the site (logging in/out, submitting a game, etc.). When a site action finishes processing the page will redirect to a result page (for example after logging in, it will redirect the user to the site index, unless the login attempt fails, in which case it will redirect to the login page). The redirect is invisible to the user and will restart the page load process. This means modifications only need to be done on the databsae, and not on data objects or models as those will be discarded by the redirect. If a site action results in a message for the user (example "Logged in successfully") this is defined in SiteActionModel, returned in the corresponding site action, stored in a cookie to survive the page reload (the redirect mentioned earlier) and then displayed as part of the page reload. Site Actions are defined in [php/models/SiteActionModel.php](https://github.com/OneHourGameJam/OneHourGameJam/blob/master/php/models/SiteActionModel.php) and stored in [php/actions](https://github.com/OneHourGameJam/OneHourGameJam/tree/master/php/actions). 
- **(Text) Rendering** - Most may be used to the term rendering in relation to computer graphics. In this case it refers to converting raw data into the final texts which will be shown on the page. Rendering is performed by a Presenter, defined in [php/presenters](https://github.com/OneHourGameJam/OneHourGameJam/tree/master/php/presenters). The output of rendering are ViewModels, which are gathered into the Dictionary and used (Along with a Template) to create the rendered web page.
- **Presenters** - Objects which contain the functions necessary to do text rendering. These take Data objects and Models and output ViewModels. Whether or not they run for a particular page load is determined by Dependencies and Render depth. Presenters are defined in [php/presenters](https://github.com/OneHourGameJam/OneHourGameJam/tree/master/php/presenters)
- **Controllers** - These correspond to actions the site needs to perform on its own, e.g. automatic jam scheduling, automatic theme pruning, etc. [php/controllers](https://github.com/OneHourGameJam/OneHourGameJam/tree/master/php/controllers)
- **Render depth** - Not all pages require the same depth to rendering - There is room for optimisation: For example, the Jams page only requires each jam entry is rendered, but the index page requires each rendered jam to also have the games contained within it to be rendered. Render depth usually refers to users, jams and games. Presenters and render functions need to ensure a cycle can't happen. For example a call to RenderUsers() with a render depth of Users and Jams mustn't render for each user which jams they participated in followed by which users participated in those jams followed by which jams those users participated in, etc. Instead it should render a list of users and for each user the jams that user participated in. The jams Render depth for a specific page is defined in dependencies.
- **Page Specific** - Some pages require some special processing. This is done in a catch-all RenderPageSpecific() method. It's just a big switch-case statement with a lot of dependencies to data objects. It's a hacky solution and can be improved. This method is defined in [php/page.php](https://github.com/OneHourGameJam/OneHourGameJam/blob/master/php/page.php)
- **Dictionary** - All rendered text is stored in an array called dictionary. This is passed into mustache, along with partials and the page's template, and contains the texts which will be placed into the template. The dictionary is structured to contain some key-worded sections, most of which correspond to their specific presenter, such as `$dictionary["users"]` or `$dictionary["jams"]` and some common ones like "CONFIG" corresponding to the page config, and "page" corresponding to Page Specific content for that page. Currently the dictionary contains some arrays with keys defined as strings, however these are being phased out in favour of more predictable ViewModels. The dictionary is filled in [php/init.php](https://github.com/OneHourGameJam/OneHourGameJam/blob/master/php/init.php)
- **Templates** - An html file which uses the Mustache format. These need to be rendered by mustache (done in [index.php](https://github.com/OneHourGameJam/OneHourGameJam/blob/master/index.php)) before it will display correctly in a browser
- **Partials (templates)** - A reusable part of the template. See Mustache documentation. For a partial to be loaded for rendering, it must be listed in the setParitals() call in [index.php](https://github.com/OneHourGameJam/OneHourGameJam/blob/master/index.php)
- **Mustache** - A widely used templating solution. See [bobthecow/mustache.php](https://github.com/bobthecow/mustache.php) for documentation
- **Plugins** - A newer architecture which groups all functionality for a given feature into a single unit. Currently only notifications and the admin log exist as plugins.
