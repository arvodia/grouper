![EURL ARVODIA Logo](https://raw.githubusercontent.com/arvodia/src/main/img/arvodia-logo.png)
# Composer plugin, package and tasks manager by group
Grouper is a composer plug-in that allows you to place the packages in a group, to install or uninstall the packages of a group with a single command.

Grouper scripts are called tasks they can copy files, directories or shrink css and js files.

The advantage of grouper with other CSS and JavaScript supports, there is no need to run any additional script. resource files are automatically updated and minified after `composer update`

## Contents
 - [Feature](#feature)
 - [Installation](#installation)
 - [Example](#example)
 - [Commands](#commands)
 - [Tasks](#tasks)
   - [Tasks type](#tasks-type)
   - [Packages Tasks](#packages-tasks)
   - [Groups Tasks](#groups-tasks)
   - [Tasks Option](#tasks-option)
 - [Configuration](#configuration)
 - [Uninstall](#uninstall)
 - [Changelog](#changelog)
 - [Git clone](#git-clone)
 - [Contact](#contact)
 - [License](#license)

## Feature
  - Grouping packages in a single name
  - Activate a group install all these packages and run task
  - Tasks to be performed for groups and packages
    - File mapping and css, js minification
    - File patcher
    - File, folder Remove
  - Interactive commands
  - Interactive initialization of the grouper.json file
  - Interactive group ans task manager
  - Stylized display messages
  
## Installation
Integrate grouper into your project with composer.
````
composer require arvodia/grouper
````
initialize grouper
````
composer grouper:init
````
## Example
Managing CSS and JavaScript, in this example we will group `bootstraps`, `bootstrap-admin`, `jquery`, `popper` and `font-awesome`, in a deul group.
and also use grouper tasks to create public/src folder, copy and minify only necessary files.
You can also do a lot of other things with grouping.
### Step 1
install grouper and minify
````
composer require arvodia/grouper
````
### Step 2
add repositories to your project, dans le fichier composer. json add these lines :
````
"repositories": [
    {
        "type": "composer",
        "url": "https://asset-packagist.org"
    }
]
````
### Step 3
Copy the example [file](https://github.com/arvodia/grouper/blob/main/src/Resources/examples/grouper.json "file") to your working directory
````
cp vendor/arvodia/grouper/src/Resources/examples/grouper.json . 
````
### Step 4
and finally there is more to activate the group.
````
composer grouper:group arvodia-asset activate
````
You will find in the public/src folder all the assets you will need for your website.
### Note
Explanation of some parameters in the file grouper.json, you notice that the name of the tasks is suffixed by `-overwrite` it is to overwrite the files for the update, and the option `"uninstall": true` it is to remove add files, with the deactivation group.

## Commands
  - `composer grouper:group`
    - Group Manager command : activate, deactivate, create, delete add and remove
  - `composer grouper:groups`
    - Shows information about all available groups.
  - `composer grouper:init`
    - Creates a basic grouper.json file in composer working directory.
  - `composer grouper:task`
    - Add, run or delete the task setting for the group or package.
	
## Tasks
Tasks can be added interactively to group packages or on the group itself.
### Tasks type
There are three types of tasks :
  - file-mapping
  - file-mapping-overwrite
  - css-minifying
  - css-minifying-overwrite
  - js-minifying
  - js-minifying-overwrite
  - file-patcher
  - file-dir-remove
  
Each one of these tasks needs two parameters: `source` and `destination`
  - source group 
    - The path compared to the location of composer.json
  - source package 
    - The path relative to the location of the package itself
  - destination
    - The path compared to the location of composer.json

### Packages Tasks
They are executed if the group is activated and during the following events :
  - post-package-install
  - post-package-update
  
### Groups Tasks
They are executed only at the end of the activation of a group.
They are only executed at the end of the post update|install if one of these packages has been checked.
### Tasks Option
For instemp only one option `uninstall` if it is enabled, after disabling group, all added files or folders will be deleted. 
	
## Configuration
Configuration are placed in a grouper.json, but do not change this file manually, rather use commands grouper for the management of you groups.

## Uninstall
The configuration file grouper.json, it will be deleted if you will uninstall grouper package.

## Changelog
A changelog, the changelog, the list of changes, presented in descending order of changes, and grouped by version.
link : [CHANGELOG](https://github.com/arvodia/grouper/blob/main/CHANGELOG.md "CHANGELOG")
From version 1.0.5 the commit text represents the working version.

## Git clone
```
$ git clone https://github.com/arvodia/grouper.git
```

## Contact
[arvodia@hotmail.com](mailto:arvodia@hotmail.com) - EURL ARVODIA

## License
MIT License
