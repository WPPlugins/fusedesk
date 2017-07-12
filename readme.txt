=== FuseDesk ===
Contributors: jeremyshapiro
Tags: FuseDesk, Infusionsoft, iMember360, InfusionWP, Memberium, WisP, WishList, Infusion, Help Desk, Ticketing, Support
Requires at least: 2.0.2
Tested up to: 4.7
Stable tag: 2.4

Integrate with FuseDesk so contacts in your Infusionsoft application can easily open new support cases directly in your FuseDesk help desk application.

== Description ==

Integrate with [FuseDesk](http://fusedesk.com/?utm_campaign=WordPress-Plugin&utm_source=WordPress-Plugin-ReadMe "FuseDesk - HelpDesk for Infusionsoft")
so contacts in your Infusionsoft application can easily open new support cases directly in
your FuseDesk help desk application and view their cases. Works great with Membership sites like WishList Member,
iMember360, Memberium, WisP, and more!

== Installation ==

To install, upload fusedesk.zip to your WordPress site. Activate the plugin and click on settings.

1. From here, you'll need to enter you Infusionsoft/FuseDesk application name, and can then click to get an API Key.
1. Click Save Changes, choose a default department, and then Save Changes again.
1. Now simply use the `[fusedesk_newcase]` or `[fusedesk_mycases]` on a page!

== Frequently Asked Questions ==

= Do I need Infusionsoft to use this? =

Yes! You not only need an Infusionsoft account, you need a FuseDesk account, too.

= Do I need a FuseDesk account? =

Yes! You'll need a FuseDesk account for this plugin to work.

= Do I use my Infusionsoft API Key? =

Nope. Use a FuseDesk API Key. After you install the plugin, we'll show you how to get an API Key with a few quick clicks.


== Changelog ==

= Version 2.4 =
May 10, 2016: Removed nonces from public pages as they weren't adding any additional security and were invalid when posted form cached pages.
May 10, 2016: FuseDesk Case Tags can be applied to new cases using the new casetagids shortcode parameter
May 10, 2016: Suggested posts can be filtered by category using the new suggestioncategories shortcode parameter

= Version 2.3 =
April 6, 2016: When we search for matching posts, we now only search published posts

= Version 2.2 =
January 25, 2016: Minor bug fix in permissions for add_options_page. Users with the "manage_options" WordPress permission can now manage the plugin options.
April 6, 2016: Removed SSL version forcing on options page which fixed an issue with the SSL handshake failing. Improved error reporting of any connection issues on options page.
April 6, 2016: Tested up to WordPress 4.5 (RC)

= Version 2.1 =
January 22, 2016: Fixed two minor bugs in partner links. Added a link to log into FuseDesk.

= Version 2.0 =
January 21, 2016: Added suggested posts and case title options to new case form. We now include the URL where the request originated in the case. If there are known issues with a partner integration, we now list them if the integration is installed, known, and active.

= Version 1.5 =
January 12, 2016: Added support for Memberium and WisP installs. Tested to WordPress 4.4.1

= Version 1.4 =
September 4, 2014: Improved support for WishList installs. Tested to WordPress 4.0. Only show active reps in the options.

= Version 1.3 =
May 16, 2013: Bug fix. Nonce was broken.

= Version 1.2 =
May 10, 2013: Set the SSL Version to 3 for WP servers that had issues with the SSL handshake

= Version 1.1 =
March 20, 2013: Added on the ability to pass in a class for all input, set input styles, or tabulate the inputs!
March 20, 2013: Patched to die correctly on error, allow AJAX for non-logged in users, and post email as expected

= Version 1.0 =
March 7, 2013: Initial release! Supports creating cases and listing cases.

