=== Nooz ===
Contributors: mightydigital, farinspace
Tags: news, media, press, press release, press coverage, news coverage, media coverage, corporate, business
Requires at least: 6.0
Tested up to: 6.6.2
Stable tag: 1.7.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simplified press release and media coverage management for websites.

== Description ==

Nooz simplifies management of your press releases and press coverage content. It adds custom post types along with carefully crafted settings, giving you the flexibility needed to manage your corporate news section.

The plugin exposes the **[nooz]** shortcode allowing you to insert press release and press coverage lists on any of your pages.

== Installation ==

**To install the plugin (recommended):**

1. Login to your WordPress installation
2. Go to the Plugins page and click the "Add New" button
3. Perform a search for "Nooz"
4. Locate the Nooz plugin by Mighty Digital
5. Click the "Install Now" button
6. Click the "Activate" button

**If you have downloaded the latest plugin ZIP file:**

1. Login to your WordPress installation
2. Go to the Plugins page and click the "Add New" button
3. Click the "Upload Plugin" button
4. Click "Choose File" and select the Nooz plugin ZIP file
5. Click the "Install Now" button
6. Click the "Activate Plugin" button

**If you have the latest plugin folder (unzipped/uncompressed files):**

1. Use SFTP (or other) to upload the Nooz plugin folder to the `/wp-content/plugins/` directory (on your web host)
2. Login to your WordPress installation
3. Go to the Plugins page and click the "Activate" link

== Screenshots ==

1. Support for press release and press coverage post types
2. Add new press release
3. Add new press coverage
4. Shortcode settings
5. Press release settings
6. Press coverage settings

== Frequently Asked Questions ==

= Is it compatible with my theme/template? =

Press releases and press coverage are created as post types and behave as such. This means that you can use all of the standard WordPress functionality to include posts into your templates. Additionally, the **[nooz]** shortcode allows you to insert press release and press coverage lists on any of your pages (see Nooz Settings > Help > Shortcode Usage).

= Is the plugin available in my language? =

It might be, check the "nooz/languages" folder to see if a translation is already available for your locale. You can use the included POT file to create a new translation for your region. Read more about WordPress <a href="https://developer.wordpress.org/plugins/internationalization/localization/">Localization</a>.

= Is WordPress Multisite Supported? =

We have not done much testing with the plugin on WordPress Multisite. We are doing our best to develop within the WordPress API.

== Changelog ==

= 1.7.2 =
* minor css fixes
* improved js minify, js file name adjustments
* tested plugin with wordpress 6.6.2

= 1.7.1 =
* improved checks for adding new submenus
* tested plugin with wordpress 6.6.1
* updated plugin minimum wordpress version to 6.0

= 1.7.0 =
* improved template loading filters
* improved output escaping in plugin-theme and plugin-core files
* updated WordPress minimum supported version from 4.9 to 5.2
* fixed issue with meta box not properly hidding postbox-header
* fixed edge-case warnings by improving code with a explicit post type check
* fixed issue where the second $post parameter of the "get_the_excerpt" filter may not always be used
* fixed security issue which could allow high privilege users (such as admin) to perform Cross Site Scripting (XSS)
* tested with WordPress 6.1.1

= 1.6.0 =
* added "meta" data value for use by custom post types
* added check for duplicate plugin activation
* improved shortcode post type (e.g. category) pretty URLs
* improved managed post type handling
* updated create_cpt init action priority from 10 to 50, allowing detection of user-defined post types using init action
* updated shortcode help
* fixed rewrite rules flush operation/timing

= 1.5.1 =
* fixed issue with backward compatibility for display="group"
* updated readme.txt FAQs
* updated screenshots
