=== Welcome Pack ===
Contributors: DJPaul
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=P3K7Z7NHWZ5CL&lc=GB&item_name=B%2eY%2eO%2eT%2eO%2eS%20%2d%20BuddyPress%20plugins&currency_code=GBP&bn=PP%2dDon
Tags: buddypress,friend,group,welcome,message,email,customise,customize,initial,redirect,registration,start
Requires at least: WordPress 3.2, BuddyPress 1.5.1
Tested up to: WP 3.3.1, BuddyPress 1.5.3.1
Stable tag: 3.3

Automatically send friend/group invites and a welcome message to new users, and redirect them to a custom page. Also provides email customisation options.

== Description ==

Welcome Pack is a BuddyPress plugin that enhances the new user experience. When a user registers on your site, Welcome Pack lets you automatically send them a friend or group invitation, a Welcome Message, and it can redirect them to a Start Page. You can also customise the emails sent by BuddyPress so that they match your site's brand, in plain text or rich HTML versions.

[vimeo http://vimeo.com/12514248]

== Installation ==

1. Place this plugin in the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Upgrade Notice == 

= 3.3 =
Fix; only customises the emails when you've enabled that option.

= 3.2 =
Fixes missing admin menu in multisite, links not being correctly replaced into certain emails, and wrong text for the "you've been promoted" email.

= 3.1 =
Fixes error handling when something goes wrong sending a welcome message.

= 3.0 =
Updated for BuddyPress 1.5 and WordPress 3.2. Revamped email customisations options and admin area. Contains 47% more awesome.

== Changelog ==

= 3.3 =
Fix; only customises the emails when you've enabled that option.
= 3.2 =
* Fixes missing admin menu in multisite, links not being correctly replaced into certain emails, and wrong text for the "you've been promoted" email.
= 3.1 =
* Fixes error handling when something goes wrong sending a welcome message.
= 3.0 =
* Rewrite for BuddyPress 1.5 and WordPress 3.2. Revamped email customisations options and admin area. Contains 47% more awesome.
= 2.1 =
* Adds Start Page feature; use this to redirect users to a link of your choice on the very first time they log in to your site.
= 2.0.3 =
* Fixed the welcome message sender receiving copy of the message. Removed duplicate function calls when using email customisation.
= 2.0.2 =
* Minor localisation fixes; username and group name lists now sorted alphabetically. Added Russian translation.
= 2.0.1 =
* Added Italian, French and Brazilian Portuguese translations. Fixed error with email list not working on certain sites which upgraded from previous version. Improved memory usage on configuration page for sites with several thousands users.
= 2.0 =
* Added email customisation and revamped admin page. You will need to re-enter all of your configuration settings, sorry.
= 1.64 =
* Welcome Message sender dropdown now shows >20 users, and minor tweaks to attempt to resolve error messages that some installs are experiencing.
= 1.63 =
* User registration being ignored in most cases on WP installs, and the Welcome Message sender name not appearing on the message, are both fixed. Error on updating the wpmu-edit.php page on MU is fixed, too.
= 1.61 =
* Fixes error disabling the plugin on WP and now shows >20 users and groups in the settings.
= 1.6 =
* Rewritten for BuddyPress 1.2.
= 1.5 =
* Fixes for BuddyPress 1.2.
= 1.41 =
* Fix for BuddyPress 1.1.1.
= 1.4 =
* Updated for BuddyPress 1.1.  Now requires BuddyPress 1.1+.  Added Hungarian translation courtesy of urband.
= 1.3 =
* Added Russian translation courtesy of SlaFFik. Changed the default group behaviour from "auto-join" to "auto-invite". Tested with WPMU 2.8.2.
= 1.22 =
* Fixes cookie warnings in web server logs, and fixes bug preventing only the default admin user account (ID 1) sending the Welcome Message.
= 1.21 =
* Updated for BuddyPress 1.0.2. Changed license from GPL v3. Private release.
= 1.2 =
* Fixed bug where didn't trigger on account+blog type of registrations (as opposed to account-only). Fixed bug where Site Activity widget wasn't updated for group joins. Note you need to have the fix for http://trac.buddypress.org/changeset/1477 applied to your BuddyPress install for this to work.
= 1.11 =
* Supports BP 1.0 and WPMU 2.7.1. Adds 'default message' functionality.
= 1.1 =
* You can now select multiple default groups and friends.
= 1.0.2 =
* Updated to work with BuddyPress revision 1324.
= 1.0.1 =
* Plugin doesn't work on BuddyPress RC-1. Oops! Thanks jjj.
= 1.0 =
* Initial release.

== Frequently Asked Questions ==

= I need help, or something's not working =

For help, or to report bugs, visit the [support forum](http://buddypress.org/community/groups/welcome-pack/ "support forum").