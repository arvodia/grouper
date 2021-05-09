![EURL ARVODIA Logo](https://raw.githubusercontent.com/arvodia/src/main/img/arvodia-logo.png)
# Composer package manager by group
Grouper is a composer plug-in that allows you to place the packages in a group, to install or uninstall the packages of a group with a single command.

## Feature

  - Interactive commands
  - Interactive initialization of the grouper.json file
  - Interactive group manager : activate, deactivate, add and remove
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
## Commands
  - `composer grouper:group`
    - Group Manager command : activate, deactivate, add and remove
  - `composer grouper:groups`
    - Shows information about all available groups.
  - `composer grouper:init`
    - Creates a basic grouper.json file in composer working directory.
	
## Configuration
Configuration are placed in a grouper.json, but do not change this file manually, rather use commands grouper for the management of you groups.

## uninstall
The configuration file grouper.json, it will be deleted if you will uninstall grouper package.


## Git clone
```
$ git clone https://github.com/arvodia/grouper.git
```

## Contact
[arvodia@hotmail.com](mailto:arvodia@hotmail.com) - EURL ARVODIA

## License
MIT License
