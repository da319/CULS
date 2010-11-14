=== Confirm User Registration ===
Contributors: Ralf Hortt
Donate link: http://horttcore.de/#
Tags: user, registration, sign up
Requires at least: 2.5
Tested up to: 2.5.1
Stable tag: 1.2.2

Admins have to confirm each user registration.

== Description ==

Admins have to confirm each user registration.
A notification will be send when the account gets approved.

= Changelog =
**v1.2.2**
- Small Bugfix, Multilanguage Support

**v1.2.1**
- Small Bugfix
- Changing authentication subject title

**v1.2**
- Complete new interface so it looks like a normal WordPress Backend Site.
- Added authenticate accounts panel.
- Added block accounts panel.
- Added an options panel.
- User can edit the confirmation E-mail adress.
- User can edit the confirmation E-mail subject.
- User can edit the confirmation E-mail message.


**v1.1.1**
It works with WP 2.5

**v1.1**
1st release

== Installation ==

- Copy the confirm-user-registration.php into your plugin directory and activate the plugin.


== Usage ==

- Go to the Confirm User Registration menu in the user tab.
- Authenticate Users : Activate user accounts
- Block Users : Deactivate user accounts
- Option : Change some settings


== Frequently Asked Questions ==

= Any other function I could use? =
Yes you might want to use the conditional function is_authenticated() function.
It requires one parameter, a user ID, it will return TRUE or FALSE

= Will it create any new database tables? =
No, it doesnt create any new tables. The Plugin just adds a usermeta value 0/1 

= Is there any language file? =
Not yet, I hope to add one in the next version.

= I can't activate the plugin in WP-Backend. What should I do? =
Sometimes there is bug that the plugin won't work if the confirm-user-registration.php is in a subfolder in WP-Plugins.
Try to put the file in ./wp-plugins/ instead of ./wp-plugins/confirm-user-registration/

== Screenshots ==
none