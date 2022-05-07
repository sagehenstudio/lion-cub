=== Lion Cub IonCube License Generator for Easy Digital Downloads ===
Contributors: sagehenstudio, littlepackage
Tags: easy-digital-downloads,license,ioncube
Requires at least: 5.6
Requires PHP: 5.6
Tested up to: 5.9
Stable tag: 1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easy Digital Downloads integration for Ioncube. Create on-the-fly IonCube licenses upon EDD file download.

== Description ==

Lioncub is an extension for Easy Digital Downloads which creates dynamic IonCube licenses on-the-fly during download. Most of the license generator commands are generated via settings in the plugin backend UI. Shortcode tags for date `{DATE}`, time `{TIME}`, customer name `{NAME}` and email `{EMAIL}` can be used in license headers and properties if desired. More advanced commands can be added by filter hook. 

Licenses are created per download. If your download file is not a ZIP file, it will be delivered as a ZIP file bundled with the license. If your download file IS a ZIP file, the license file will be placed inside your ZIP file.

> Please note that this plugin requires you own a copy of IonCube encoder (Pro or Cerebus level) and the make_license file. You must be independently familiar with how to encode your software projects and initiate license. License creation and validation is entirely your responsibility.

** More information **

- Developers; follow or contribute to the [plugin on GitHub](https://github.com/sagehenstudio/lioncub)
- Other [WordPress plugins](https://profiles.wordpress.org/littlepackage/#content-plugins) by Sagehen Studio

== Installation ==

To get this up and running, you'll need to configure a few things inside your WordPress installation. You will want to make sure you have the Linux version of the make_license executable file from IonCube, unless you are running a Windows server. Testing has not been done on Windows servers. 

Upload the make_license file to the server where your Easy Digital Download plugin resides. Inside the wp-content/uploads/lioncub/ folder is one suggested location. The file must remain named as "make_license" (no re-naming).

= WordPress =

1. Upload the contents of **lion-cub.zip** to your plugins directory, which usually is `/wp-content/plugins/`.
2. Activate the **Lion Cub** plugin on your Wordpress Plugins screen
3. Under Wordpress -> Settings -> Lion Cub, set your make_license absolute path or URL, and a random API key (keep it secret).
4. In your Easy Digital Downloads > Download settings pages, turn on licensing per-download using the checkbox in the file settings, where it says "Create Ioncube licenses for this download." Proceed to set license settings there.

= Testing the Lion Cub API =

Lion Cub uses Wordpress REST API which can be tested at the URL:

https://**www.your-website.com**/wp-json/lion-cub/make-license/?api_key=**yourAPIkey**

Make sure your API key is correct, and keep it secret. If need be the API can be changed any time on the Lion Cub settings page. The GET request uses some dummy data. You can adjust the data used by Lion Cub using the 'lioncub_filter_api_data' filter hook.

= Debugging =

For Lion Cub debug logs, turn on Easy Digital Downloads debugging in their "Misc" settings, then go to Downloads -> Tools to view logs.


== Frequently Asked Questions ==

== Screenshots ==
