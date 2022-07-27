=== Lion Cub ionCube License Generator for Easy Digital Downloads ===
Contributors: sagehenstudio, littlepackage
Tags: easy-digital-downloads,license,ioncube
Requires at least: 5.6
Requires PHP: 7.0
Tested up to: 6.0
Stable tag: 1.0.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easy Digital Downloads integration for ionCube. Create on-the-fly ionCube licenses upon EDD file download.

== Description ==

Lioncub is an extension for Easy Digital Downloads which creates dynamic ionCube licenses on-the-fly during download. Most of the license generator commands are generated via settings in the plugin backend UI. Shortcode tags for date `{DATE}`, time `{TIME}`, customer name `{NAME}` and email `{EMAIL}` can be used in license headers and properties if desired. More advanced commands can be added by filter hook. 

Licenses are created per download. If your download file is not a ZIP file, it will be delivered as a ZIP file bundled with the license. If your download file IS a ZIP file, the license file will be placed inside your ZIP file.

> Please note that this plugin requires you own a copy of ionCube encoder (Pro or Cerebus level) and the make_license file. You must be independently familiar with how to encode your software projects and initiate license. License creation and validation is entirely your responsibility.

** More information **

- Developers; follow or contribute to the [plugin on GitHub](https://github.com/sagehenstudio/lioncub)
- Other [WordPress plugins](https://profiles.wordpress.org/littlepackage/#content-plugins) by Sagehen Studio

== Installation ==

To get this up and running, you'll need to configure a few things inside your WordPress installation. You will want to make sure you have the Linux version of the make_license executable file from ionCube, unless you are running a Windows server. Testing has not been done on Windows servers. 

Upload the make_license file to the server where your Easy Digital Download plugin resides. Inside the wp-content/uploads/lioncub/ folder is one suggested location. The file must remain named as "make_license" (no re-naming).

= WordPress =

1. Upload the 'lion-cub' folder to your plugins directory, which usually is `/wp-content/plugins/`.
2. Activate the **Lion Cub** plugin on your Wordpress Plugins screen
3. Under Wordpress -> Settings -> Lion Cub, set your make_license absolute path or URL, and a random API key (keep it secret).
4. In your Easy Digital Downloads > Download settings pages, turn on licensing per-download using the checkbox in the file settings, where it says "Create ionCube licenses for this download." Proceed to set license settings there.

= Testing the Lion Cub API =

See README.md

= Debugging =

See README.md

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

1.0.0 Initial release

1.0.1 - 7 May 2022
* Formatting
* I'm learning GIT version control, so bear with me!

1.0.2 - 14 May 2022
* Remove wp post meta from db in uninstaller
* Correct capitalization of ionCube (no capital I)
* @todos added to header
* Correct debug logs location on ionCube settings page

1.0.3 - 27 July 2022
* Make sure WP option 'lioncub' is array when fetching
* Hook to init with priority > 100 to come in after edd_process_download(), hooked to init at 100
* Separate out admin JS into separate file instead of inlining
* PHP requirement 7.0 due to use of null coalesce operators
