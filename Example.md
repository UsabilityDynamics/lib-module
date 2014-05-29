```/**
   * Init our manager.
   */
  $mm = new UsabilityDynamics\Module\Bootstrap( array(
    'api_key' => '{hash}',
  ) );

``/** Returns the list of installed modules */``
``$mm->get_modules();``

``/** Activates required modules */``
``$mm->activate_modules( array(``
  ``'{module-name-1}',``
  ``'{module-name-2}',``
  ``'{module-name-1}',``
``) )``
```