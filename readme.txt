=== Plugin Name ===
Contributors: flashpixx
Tags: php, embedded, eval
Requires at least: 2.7
Tested up to: 3.4.2
Stable tag: 0.1
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WCRMFYTNCJRAU
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.en.html


This plugin runs native PHP code that can be added to post and page data.


== Description ==

With this plugin can added native PHP code within article and blog data. For logged-in user PHP error messages are shown for debugging the PHP code. Each
code block is run like a PHP function. Own functions can be added to the plugin and can be included on other calls. For article writers the execution of
PHP code can be disabled, so the administrator can create different function, that can be used by the writers. Output of the PHP code are included
within the article.

= Features =

* inline code on articles / pages
* function define and include
* only execution of defined functions
* any PHP error message is passed to the author
* disabling PHP error messages
* execution can be denied by a keyword list



== Installation ==

1.  Upload the folder to the "/wp-content/plugins/" directory
2.  Activate the plugin through the 'Plugins' menu in WordPress



== Requirements ==

* PHP 5.3.0 or newer 



== Shortcode ==

For inline code you can use:
<pre>[evalphp] your code [/evalphp]</pre>

For included code:
<pre>[evalphp include="your includes"]</pre>

The include parameter is optional and can be used on both shortcodes.



== Changelog == 

= 0.1 =

* first version with the base functions