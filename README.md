![EURL ARVODIA Logo](https://raw.githubusercontent.com/arvodia/src/main/img/arvodia-logo.png)
# Composer plugin, package and tasks manager by group
Grouper is a composer plug-in that allows you to place the packages in a group, to install or uninstall the packages of a group with a single command.

## Contents
 - [Feature](#feature)
 - [Installation](#installation)
 - [Commands](#commands)
 - [Tasks](#tasks)
   - [Tasks type](#tasks-type)
   - [Packages Tasks](#packages-tasks)
   - [Groups Tasks](#groups-tasks)
 - [Configuration](#configuration)
 - [Uninstall](#uninstall)
 - [Git clone](#git-clone)
 - [Contact](#contact)
 - [License](#license)

## Feature
  - Grouping packages in a single name
  - Activate a group install all these packages and run task
  - Tasks to be performed for groups and packages
  - File mapping and css, js minification
  - Interactive commands
  - Interactive initialization of the grouper.json file
  - Interactive group ans task manager
  - Stylized display messages
  
## Installation
Integrate grouper into your project with composer.
````
composer require arvodia/grouper
````
if you want to use minification tasks add:
````
composer require matthiasmullie/minify
````
initialize grouper
````
composer grouper:init
````
## Commands
  - `composer grouper:group`
    - Group Manager command : activate, deactivate, add and remove
  - `composer grouper:groups`
    - Shows information about all available groups.
  - `composer grouper:init`
    - Creates a basic grouper.json file in composer working directory.
  - `composer grouper:task`
    - Add or delete the task setting for the group or package.
	
## Tasks
Tasks can be added to group packages or on the group itself.
### Tasks type
There are three types of tasks :
  - file-mapping
  - css-minifying
  - js-minifying
  
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
  - post-package-uninstall
  
After uninstallation the added file or folder will be deleted.
### Groups Tasks
they are executed only at the end of the activation of a group

	
## Configuration
Configuration are placed in a grouper.json, but do not change this file manually, rather use commands grouper for the management of you groups.

## Uninstall
The configuration file grouper.json, it will be deleted if you will uninstall grouper package.


## Git clone
```
$ git clone https://github.com/arvodia/grouper.git
```

## Contact
[arvodia@hotmail.com](mailto:arvodia@hotmail.com) - EURL ARVODIA

## License
MIT License
