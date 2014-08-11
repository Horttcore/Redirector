=== Redirector ===
Contributors: Horttcore
Donate link: http://www.horttcore.de
Tags: redirect, page
Requires at least: 3.3
Tested up to: 3.6
Stable tag: 3.0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0

Redirect posts / pages / custom post types

== Description ==

Redirect posts, pages or any custom post type to a different url.

== Installation ==

* Put the plugin file in your plugin directory and activate it in your WP backend.
* Go to edit a page
* Scroll down to 'Redirector' meta box
* Select a WordPress page of you installation or enter a valid URL or select the 'first child page'

== Screenshots ==

1. Meta box - No redirection
2. Meta box - Select post ( any post type )
3. Modal box - Select post ( any post type ) with most recent posts
4. Modal box - Search post ( any post type )
5. Meta box - Selected post ( any post type )
6. Meta box - Custom URL
7. Meta box - First child element
8. Meta box - SSL

== Frequently Asked Questions ==

= Where can I get support or report bugs? =

Please use the [github](https://github.com/Horttcore/Redirector) to report bugs or add feature requests!

= How can I activate Redirector for other post types beside pages? =

Simple add the post type support for it via `add_post_type_support( 'post', 'redirector' )`

== Hooks ==

= Actions =

* `redirector_uninstall` - Runs on plugin deinstall
* `redirector_metabox_begin` - Begin redirector meta box
* `redirector_metabox_end` - End redirector meta box
* `redirector-modal-search-begin` Begin redirector modal
* `redirector-modal-search-end` End redirector modal

= Filters =

* `redirector-redirect-url` - Alter the query string that is appended to redirect url
* `redirector-status-code` - Redirect status code; default 301
* `redirector-recent-posts` - Alter redirector recent posts query
* `redirector-search-query` - Alter redirector search query
* `redirector-meta` - Alter Redirector meta save

= Update Notice =

In version 3+ I've changed the database handling of storing the post meta data.
This results in that you have the visit the backend once after updating the plugin.
The function maybe_update() will handle the transition from the old structure to the newer one.
There shouldn't be any problem updating the plugin, if so please contact me!

== Changelog ==

= 3.0.1 =

* Fix: Checked checkbox for no redirection
* Fix: Prevent empty redirects
* Fix: Compability with Cachify

= 3.0.0 =

* Added: Search post object for post redirect type
* Added: Redirect preview
* Added action: `redirector-update`
* Added action: `redirector-modal-search-begin`
* Added action: `redirector-modal-search-end`
* Added filter: `redirector-recent-posts`
* Added filter: `redirector-redirect-url`
* Added filter: `redirector-status-code`
* Added filter: `redirector-meta`
* Enhancement: New Redirector UI
* Removed Filter: `redirector_redirect`
* Removed Filter: `redirector_status`
* Removed Filter: `redirector_types`

= 2.0.3 =

* Added more arguements to the hooks
* Added README.md

= 2.0.2 =

* Filter 'redirector_status' added
* Uninstall routine activated
* Code readability increased

= 2.0.1 =

* Enhancement: Cleanup
* Fix: Minor typos
* Fix: Metabox is displayed correctly on new post

= 2.0 =

* Core function rewritten
* Updated JS/CSS
* Fully extendable with filters and actions
* Custom Post Type Support
* New redirect Method (HTTPS)
* Moved files into folders

= 1.2 =

* Moved the code into class

= 1.1 =

* Proper multilanguage support
* CSS moved to redirector.css
* JS moved to redirector.js

…
