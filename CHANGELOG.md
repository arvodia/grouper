CHANGELOG
=========

1.2.1
-----
* Clean .rej files after patch

1.2.0
-----
* Added patcher task

1.1.9
-----
* Added option to run global tasks without running packager tasks 

1.1.8
-----
* Added option to run global tasks without running packager tasks 

1.1.7
-----
* Uninstall files when uninstalling a package instead of disabling a group 

1.1.5
-----
* Code correction in polyfills

1.1.4
-----
* Remove the polyfills from the composer file and make an inclusion in the activate() function of the plugin

1.1.3
-----
* Added the automatic file inclusion mechanism. This is useful because PHP functions cannot be loaded automatically by PHP.
* Added Polyfills : str_starts_with, str_ends_with

1.1.2
-----
* Added matthiasmullie/minify in the composing autoload

1.1.1
-----
* Added matthiasmullie/minify in the composing dependencies

1.1.0
-----
* Code correction
* Rename method isPackagesUpdated() to isPackagesChecked()
* Run groups task after POST_INSTALL_CMD

1.0.5
-----
* Adding option Run unstall group tasks and these packages for the task command
* add several files, they will then be attached in 1 minified output file.
* Groups Tasks They are only executed at the end of post update if one of these package has been updated
* Styling terminal
* The deletion of file which has more one attaches files minifies
* Adding text command
* Works on the minifying function of the class task

1.0.4
-----
* Adding an example file
* Add more functions for class Grouper and FileSystems
* Disable POST_PACKAGE_UNINSTALL Events
* Adding the uninstall option for group tasks
* Now uninstall tasks are performed during group deactivation or held during uninstall packages
* Adding option Run group tasks and these packages for the task command
* Function improvement nameValidator()
* Adding create and delete action groups manager
* Adding Verbose and Very Verbose for the groups command
* Print grouper version
* Code reduction

1.0.1
-----
* Adding Task Class
* Adding FileSystems Class
* Adding Plugin Event
* Adding the Tasks Type
* Adding the Task Command : add and reset tasks
* Adding the Task Parameter for Group and Package
