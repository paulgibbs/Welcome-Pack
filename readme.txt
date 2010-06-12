=== Welcome Pack ===
Contributors: DJPaul
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=P3K7Z7NHWZ5CL&lc=GB&item_name=B%2eY%2eO%2eT%2eO%2eS%20%2d%20BuddyPress%20plugins&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: buddypress,friend,group,welcome,default,message,email,customise,customize,initial,redirect,registration,start
License: General Public License version 2
Requires at least: WP 2.9.x, BuddyPress 1.2.3
Tested up to: WP 3.0, BuddyPress 1.2.4.1
Stable tag: 2.1

When a user registers on your site, Welcome Pack lets you automatically send them a friend or group invitation, a Welcome Message and can redirect them to a Start Page. You can also customise the default emails sent by BuddyPress to ensure that they match the brand and tone of your site.

== Description ==

Welcome Pack is a BuddyPress plugin that enhances the new user experience. When a user registers on your site, Welcome Pack lets you automatically send them a friend or group invitation, a Welcome Message and can redirect them to a Start Page. You can also customise the default emails sent by BuddyPress to ensure that they match the brand and tone of your site.

[vimeo http://vimeo.com/12514248]

== Installation ==

1. Install via WordPress Plugins administration page.
1. Activate the plugin on the blog running BuddyPress.
1. Visit the BuddyPress > Welcome Pack menu to configure.

== Upgrade Notice == 

= 2.1 =
Adds Start Page feature. Compatible with BuddyPress 1.2.4.1.

= 2.0.4 =
[BuddyPress 1.2.4](http://buddypress.org/2010/05/buddypress-1-2-4/) compatibility.

= 2.0.3 =
Fixed the welcome message sender receiving a copy of the message.

= 2.0.2 =
Minor localisation fixes; username and group name lists now sorted alphabetically.

= 2.0.1 =
Improves memory usage on large sites and fixed email list bug.

= 2.0 =
Added email customisation options and revamped the admin UI. Configuration page moved to underneath the "BuddyPress" menu. You will need to re-enter all of your settings.

== Change Log ==

= 2.1 =
* Adds Start Page feature; use this to redirect users to a link of your choice on the very first time they login to your site.
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
= Thanks =
* Thanks to [SlaFFik](http://cosydale.com/), Arthur Freitas and Luca Camellini for translations in v2.1.
* Added Russian language file in v2.0.2, thank you [SlaFFik](http://cosydale.com/).
* Added French and Brazilian Portuguese in v2.0.1, thank you Arthur Freitas.
* Thanks to Luca Camellini for the Italian translation in v2.0.
* Social Network Icon Pack by Rogie King is licensed under a Creative Commons Attribution-Share Alike 3.0 Unported License - komodomedia.com.
* The idea of using meta boxes on the admin page came from [Joost de Valk](http://yoast.com/), your one stop-shop for a wide range of WordPress plugins and SEO advice.
* The implementation of the above is credited to http://www.code-styling.de/english/how-to-use-wordpress-metaboxes-at-own-plugins.
* I would sincerely like to thank [Dave Carson](http://solopracticeuniversity.com/) for helping me test this throughout early development.
* Big thanks to [BeLogical](http://buddypress.org/developers/BeLogical/) for his bug reports and time in testing v1.2.
* Thanks to Jason DeVelvis for reporting bugs in v1.22.
* Thanks to [SlaFFik](http://buddypress.org/developers/slaffik/) for providing a Russian translation in v1.3.
* Thanks to [urband](http://buddypress.org/developers/urband/) for providing a Hungarian translation in v1.4.