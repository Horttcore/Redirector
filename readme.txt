=== Redirector ===
Contributors: Horttcore
Donate link: http://www.horttcore.de
Tags: redirect, page
Requires at least: 3.3
Tested up to: 3.6
Stable tag: 3.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Redirect posts / pages / custom post types

== Description ==

With this Plugin you can redirect a page in your WordPress installation to any other URL, to a page in your WordPress installation, to the first child page or to https.

With Version

== Installation ==

*   Put the plugin file in your plugin directory and activate it in your WP backend.
*   Go to edit a page
*   Scroll down to 'Redirector' meta box
*   Select a WordPress page of you installation or enter a valid URL or select the 'first child page'

== Screenshots ==

1. Screenshot of the Meta box in the content
2. Screenshot of the Meta box in the sidebar

== Frequently Asked Questions ==

= There is no redirect box when I create a new page! =
You have to save it once to get access to the redirecor settings. Sorry for that!

= Are there any filters I can use? =
Sure here is a list
1. redirector_dropdown
1. redirector_url
1. redirector_redirect

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

= How can I use the Redirector plugin for other post types
See this gist https://gist.github.com/Horttcore/6412688

= I've found a bug, what to do? =
*   Please give me a shout over github ( https://github.com/Horttcore/Redirector )

== Upgrade Notice ==

With the update to version 3+ I've changed the database structure.
If you visit the backend all old entries should be replaced with the new structure.

== Changelog ==

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
