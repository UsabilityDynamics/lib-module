```php
  /**
   * Init our manager.
   *
   */
  $mm = new UsabilityDynamics\Module\Bootstrap( array(
    'api_key' => '{hash}', // required.
    'check'   => true  // boolean. optional. Check or not for available modules to install.
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