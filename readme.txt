=== Welcome Pack ===
Contributors: DJPaul
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=P3K7Z7NHWZ5CL&lc=GB&item_name=DJPaul%20%2d%20wordpress%20plugins&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: buddypress,friend,group,welcome,default,message
License: General Public License version 3
Requires at least: WPMU 2.8.1, BuddyPress 1.1
Tested up to: WPMU 2.8.4a, BuddyPress 1.1
Stable tag: 1.4

Brings default friend, default group and welcome message functionality to BuddyPress.

== Description ==

Welcome Pack is a BuddyPress plugin that enhances the new user experience. A newly-registered user is sent an invite to a specified group and becomes friends with a specified member. A customisable "welcome" message can also be sent to the new user automatically.
NOTE: VERSION 1.4 REQUIRES BUDDYPRESS 1.1!

== Installation ==

1. Upload all files to a `/wp-content/plugins/welcome-pack/` directory.
1. Activate the plugin site-wide.
1. Visit BuddyPress > Welcome Pack to configure the plugin.

== Change log ==
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