=== Welcome Pack ===
Contributors: Paul Gibbs
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=P3K7Z7NHWZ5CL&lc=GB&item_name=B%2eY%2eO%2eT%2eO%2eS%20%2d%20BuddyPress%20plugins&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: buddypress,friend,group,welcome,default,message,email
License: General Public License version 2
Requires at least: WP/MU 2.9, BuddyPress 1.2
Tested up to: WP/MU 2.9, BuddyPress 1.2.3
Stable tag: 2.0

When a user registers on your site, Welcome Pack lets you automatically send them a friend or group invitation, or a welcome message. You can also customise the default emails sent by BuddyPress to ensure that they match the brand and tone of your site.


== Description ==

Welcome Pack is a BuddyPress plugin that enhances the new user experience. When a user registers on your site, you may want to automatically send them a friend or group invitation, and send them a welcome message. This plugin lets you do that.

== Installation ==

1. Install via WordPress Plugins administration page.
1. Activate the plugin on the blog running BuddyPress.
1. Visit Settings > Welcome Pack to configure.

== Upgrade Notice == 
= 1.6 = 
Rewritten for BuddyPress 1.2.

== Change log ==
* 1.64 - Welcome Message sender dropdown now shows >20 users, and minor tweaks to attempt to resolve error messages that some installs are experiencing.
* 1.63 - User registration being ignored in most cases on WP installs, and the Welcome Message sender name not appearing on the message, are both fixed. Error on updating the wpmu-edit.php page on MU is fixed, too.
* 1.61 - Fixes error disabling the plugin on WP and now shows >20 users and groups in the settings.
* 1.6 - Rewritten for BuddyPress 1.2.
* 1.5 - Fixes for BuddyPress 1.2.
* 1.41 - Fix for BuddyPress 1.1.1.
* 1.4 - Updated for BuddyPress 1.1.  Now requires BuddyPress 1.1+.  Added Hungarian translation courtesy of urband.
* 1.3 - Added Russian translation courtesy of SlaFFik. Changed the default group behaviour from "auto-join" to "auto-invite". Tested with WPMU 2.8.2.
* 1.22 - Fixes cookie warnings in web server logs, and fixes bug preventing only the default admin user account (ID 1) sending the Welcome Message.
* 1.21 - Updated for BuddyPress 1.0.2. Changed license from GPL v3. Private release.
* 1.2 - Fixed bug where didn't trigger on account+blog type of registrations (as opposed to account-only). Fixed bug where Site Activity widget wasn't updated for group joins. Note you need to have the fix for http://trac.buddypress.org/changeset/1477 applied to your BuddyPress install for this to work.
* 1.11 - Supports BP 1.0 and WPMU 2.7.1. Adds 'default message' functionality.
* 1.1 - You can now select multiple default groups and friends.
* 1.0.2 - Updated to work with BuddyPress revision 1324.
* 1.0.1 - Plugin doesn't work on BuddyPress RC-1. Oops! Thanks jjj.
* 1.0 - Initial release.

== Thanks ==
* I would sincerely like to thank [Dave Carson](http://solopracticeuniversity.com/) for helping me test this throughout early development.
* Big thanks to [BeLogical](http://buddypress.org/developers/BeLogical/) for his bug reports and time in testing v1.2.
* Thanks to Jason DeVelvis for reporting bugs in version v1.22.
* Thanks to [SlaFFik](http://buddypress.org/developers/slaffik/) for providing a Russian translation in version v1.3.
* Thanks to [urband](http://buddypress.org/developers/urband/) for providing a Hungarian translation in version v1.4.