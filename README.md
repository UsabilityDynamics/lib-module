Modules Manager
==========

## Description
Library handles the following:
* checks user permissions and returns the list of available modules.
* uploads specific modules from remote repository.
* upgrades existing module to the required version.
* enables/disables module initialization.

##Example

```php
  /**
   * Init our manager.
   *
   */
  $mm = new UsabilityDynamics\Module\Bootstrap( array(
    'api_key' => '{hash}', // required.
    'check'   => true  // boolean. optional. Check for available modules to install.
  ) );

  /** Returns the list of installed modules */
  $mm->getModules();

  /** Activates required modules */
  $mm->activateModules( array(
    '{module-name-1}',
    '{module-name-2}',
    '{module-name-1}',
  ) )
```