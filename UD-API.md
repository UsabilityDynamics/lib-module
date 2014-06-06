All requests to UD server are being handled automatically.
The information below is just for familiarization.

#Get the list of available Modules
##Request
###URL
http://api.ud-dev.com/modules/v2/loadout?key={access_token}&system={slug}&version={system_version}
###Method
POST
###Params
```json
{
  "installed":{
    "usabilitydynamics/wp-property-admin-tools" : 3.6.0
  }
}
```
##Response
Example (data below is required):
```json
{
  "ok": true,
  "modules": [
    {
      "name": "usabilitydynamics/wp-property-admin-tools",
      "version": "3.6.0",
      "require": {
        "php": ">=5.3"
      },
      "dist": {
        "url": "https://github.com/UsabilityDynamics/wp-property-admin-tools/archive/3.6.0.zip"
      },
      "extra": {
        "title": {
          "en_US": "Admin Tools"
        },
        "tagline": {
          "en_US": "For developers and designers"
        },
        "description": {
          "en_US": "This plugin is intended for developers and theme designers."
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
  ]
}
```


#Validate Module
##Request
##Response