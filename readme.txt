=== WP-BlackCheck ===
Contributors: Stargazer
Donate link: http://my.stargazer.at/wishlist/
Tags: comments,spam,blacklist
Requires at least: 2.9
Tested up to: 4.6
Stable tag: trunk

WP-BlackCheck is an Anti-Spam Solution for Wordpress blocking spam using local detection and a centralized Blacklist Service.

== Description ==
= An easy and effective solution against Comment Spam =
WP-BlackCheck combines various ideas to protect your blog from spam.

= Fearures =
* Easy Installation
* Block known spammers
* Block Trackback/Pingback spam
* No need to adjust default templates
* Spam counter on the dashboard
* Statistics

= Languages =
* Albanian (thanks to Taulant Ceka - http://sem.al )
* English
* German
* Slovakian (thanks to Black Cat Artworks - http://blackcatartworks.eu/ )

If you want to see this plugin in your language, feel free to contact me or use [transifex.net](https://www.transifex.net/projects/p/wpbc/resource/plugin/) to contribute.

= Links =
* [Blog](http://my.stargazer.at/ "Authors blog")
* [Bugtracker](http://bugs.stargazer.at/ "Bugtracker")
* [Twitter](http://www.twitter.com/ToeiRei)

= History =
Blocking spammers via htaccess or other server settings would do the job, but that would require
to keep all the files in sync. So I decided to write a centralized solution that enables
innocent users to remove their IP from the blacklist via removal request. As a blacklist gets queried
way too often, I added some local detection of spammers to reduce load at my side.
By now many additional features have been added, making this plugin a full grown anti-spam solution.

== Installation ==
1. Upload the contents of the wp-blackcheck directory into your Wordpress plugin directory or use the plugin installer.
2. Activate the Plugin
3. Configure the plugin to your needs
4. Optionally report Spam

== Screenshots ==

1. The Admin-Page

== Frequently Asked Questions ==
= What happens if a blocked IP hits my site? =
The Plugin does not prevent reading of your blog. But if a blocked IP tries to post a comment
WP-BlackCheck will block it, displaying a link to the removal request form.

= What happens if I am blacklisted? =
I'd assume that spammers do not authenticate against your blog. The plugin will block your comments if you are not logged in.

= I am blacklisted! What now? =
Just follow the link to http://www.stargazer.at/blacklist/ and follow the instructions on the page.

= Why should I report Spam? =
Spammers usually hit quite a few servers with their IPs. It's pretty uncommon that you are the only one who has been hit from that IP. Sharing that info prevents Spam on other blogs.

= WP-BlackCheck is not available in (insert language here) =
The plugin comes with a file named wp-blackcheck.pot which contains the messages printed. Feel free to translate it into your language and send the .mo and .po file back to me, so I can include it into the next release.

== Changelog ==
= 2.7.2 =
* Bugfix release - fixed forgotten closing bracket in precheck.

= 2.7.1 =
* Do not mess with Akismet (who uses that function anyways?)
* Unhide config options for faster setup
* Viktoria Rei Bauer taking over

= 2.7.0 =
* Implement redirection of spammers (i.e. send them to the fbi website)
* Implement comment hash checking
* relocate some functions to functions.inc.php (where they should be)
* Increase HTTP Timeout for Web-Requests to avoid some server problems

= 2.6.2 =
* fix wpbc_get_comments_approved

= 2.6.1 =
* Add counter for reported IPs
* Fix bar graph text issue
* New Webservice URL

= 2.6.0 =
* Improve trackback/pingback checks
* Remove obsolete trap field
* Fix update notifications

= 2.5.1 =
* Fix typo in function name
* adjust speed check

= 2.5.0 =
* Add Icon to reporting
* Adjust speedcheck to more realistic values
* Add another Spam trap (dummy field)
* Relocate 'Report Spam' as it belongs to 'tools'
* Update translations
* Add statistics
* Fixed bug with the blocked spam counter
* Fixed bug with header checks
* Reorder spam checks to be more effective
* Some cosmetic changes

= 2.4.0 =
* Update notification optional
* E-Mail notification
* Known problems -> FAQ

= 2.3.0 =
* Honor previously approved comment settings from WordPress (whitelist)
* Replace comment time with type rate (keystrokes per second via comment length)
* Encrypt timestamp to make it less obvious

= 2.2.2 =
* Another overlooked function.

= 2.2.1 =
* Fix reporting function

= 2.2.0 =
* Prefix ALL functions with wpbc_ to avoid clashes with other plugins
* Update Notifications
* Checking for PHP/WP Requirements
* Detect Akismet to work with it
* Purge old comments
* Make sure we meet the requirements

= 2.1.1 =
* Fixed notification bug

= 2.1.0 =
* Add config option for speedlimit
* Add 'reset to defaults' function
* Add Settings to plugin-page
* Add warning for outdated settings
* Support wp_remote_post()
* Split off pre-checks
* Use WP-CSS stuff for warnings/alerts
* Language fixes

= 2.0.0 =
* Localization
* Rewrite speed limit code
* Add debug code for speed limit
* Fix spam counting for queue

= 1.12 =
* Split off some functions to functions.inc.php, relocated some code to improve readability
* Corrected typo in wp_die message
* Improve trackback handling
* Improve some message texts

= 1.11 =
* Add Link-Limit
* Add explicit trackback/pingback check for proxy servers
* Add check for valid trackback/pingback URL

= 1.10.1 =
* bugfix release to fix whitespace issue and typo in request

= 1.10 =
* Code cleanup and inline documentation
* Fix direct access to admin-page
* Check permissions before doing the admin page
* Set report stacks to 100 IPs at once
* Fix issues with IPv6
* Add throttle for spammers hitting the server quite heavy
* Add option to decline bbCode and optionally report them automatically
* Add option to decline comments that come in too fast and optionally report them automatically
* Exclude pingbacks/trackbacks from our checks

= 1.9 =
* Add admin-page
* Add reporting in chunks
* Make statistics optional

= 1.8 =
* Remove multiple Spam-comment per IP check
* Fix Spam deletion
* Prepare limit for reporting in chunks

= 1.7 =
* Tighten Security
* Add statistics

= 1.6 =
* Integrated Report Button into comments view

= 1.5 =
* Corrected messages
* Fixed comment IP querying

= 1.4 =
* Changed Spam count before reporting
* Empty quarantine now supported

= 1.3 =
* If someone hits us 3 times, it's most likely NOT an accident

= 1.2 =
* Remove reported Spam to prevent double reports

= 1.1 =
* Add reporting

= 1.0 =
* Initial release
* Simple check against the centralized blacklist
