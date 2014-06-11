Modules Manager
==========

## Description
Library handles the following:
* Checks permissions and returns the list of available modules.
* Uploads specific modules from remote repository.
* Upgrades existing modules to the required versions.
* Enables/disables modules.
* Activates enabled modules.

## TODO
* Implement User Interface.
* Add REST.
* Add Error Handler.
* Add Mocha Test.

##Get Started

### Initialization
```php
  /**
   * Init our Modules Manager.
   * Must be initialized before 'init' action.
   */
  $moduleManager = new UsabilityDynamics\Module\Bootstrap( array(
    // Required. API Key ( or Access Token ). 
    // It's related to current domain/site.
    'key' => false,
    // Required. Slug of current system ( plugin|theme ) 
    // Determines which modules can be installed for current plugin ( theme ).
    'system' => null,
    // Required. Version of current plugin|theme. 
    // Determines which modules ( and their versions ) current system's version supports.
    'version' => null,
    // Required. Path, where plugin's modules must be installed. 
    // It may be defined via UD_MODULES_DIR constant to store all modules in the same place.
    // Note, if UD_MODULES_DIR is defined, modules will be stored under system directory
    // to prevent trying to load modules of another system.
    // Example: {UD_MODULES_DIR}/wp-property/
    'path' => null,
    // Optional. Use or not use transient memory.
    'cache' => true,
    // Optional. Mode Handler can be used to do some processes automatic. 
    // see UsabilityDynamics\Module\Bootstrap::_runMode().
    'mode' => 'default',
  ) );
```  

### Get List of Modules
```php
  /** 
   * Returns the list of all modules.
   */
  $moduleManager->getModules();
  
  /**
   * Get the list of all activated modules.
   * In default mode, modules are being activated automatically
   * But you can set any custom mode, - in this case 
   * you will have to activate modules manually.
   */
  $moduleManager->getModules( 'activated' );
  
  /**
   * Get the list of all available modules
   * which can be Installed or Upgraded ( have new version)
   * based on the current system.
   */
   $moduleManager->getModules( 'available' );
  
  /**
   * Get the list of all installed ( downloaded ) modules
   * based on the current system.
   */
  $moduleManager->getModules( 'installed' );
  
  /**
   * Get specific data.
   . e.g.: get version of some installed module.
   */
  $moduleManager->getModules( 'installed.{module_name}.data.version' );
```

### Install | Upgrade Module(s)
```php
  /**
   * The method handles install and upgrade processes.
   *
   */
  $moduleManager->loadModules( array(
    'usabilitydynamics/wp-property-admin-tools',
    'usabilitydynamics/wp-property-importer',
    'usabilitydynamics/lib-term-editor',
  ) );
```

### Enable Module(s)
```php
  /**
   * Enables module(s) for current system.
   */
  $moduleManager->enableModules( array(
    'usabilitydynamics/wp-property-admin-tools',
    'usabilitydynamics/wp-property-importer',
    'usabilitydynamics/lib-term-editor',
  ) );
```

### Disable Module(s)
```php
  /**
   * Disables module(s) for current system.
   */
  $moduleManager->disableModules( array(
    'usabilitydynamics/wp-property-admin-tools',
    'usabilitydynamics/wp-property-importer',
    'usabilitydynamics/lib-term-editor',
  ) );
```

### Activate ( init ) Modules
```php
  /**
   * Activates all enabled modules.
   * 
   * The current process is automatically called
   * on Module Manager initialization if mode is 'default'.
   * In other case you have to call the method manually.
   * 
   * Method must be called before 'init' action.
   */
  $moduleManager->activateModules();
```
