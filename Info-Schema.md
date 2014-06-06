##Composer.json
Module must store some specific information in composer.json
Here is example of required data.

```json
{
  "name": "usabilitydynamics/wp-property-admin-tools",
  "version": "3.6.0",
  "extra": {
    "title": {
      "en_US": "Admin Tools"
    },
    "tagline": {
      "en_US": "For developers and designers."
    },
    "description": {
      "en_US": "This plugin is intended for developers and theme designers, etc."
    },
    "image": "https://example.com/image.png",
    "minimum_core": {
      "wp-property": "2.0.0",
      "wp-realty": false
    },
    "installer-name": "admin-tools",
    "text_domain": "wpp-admin-tools",
    "classmap": "lib/class-admin-tools.php",
    "bootstrap": "UsabilityDynamics\\WPP\\Admin_Tools"
  }
}
```