=== emObA - Email Obfuscator Advanced ===
Author: Kim Kirkpatrick
Contributors:  kirkpatrick
Donate link: http://kirknet.org/wpplugins/
Tags: spam, email, mail, address, addresses, hide, JavaScript
Requires at least: 2.8
Tested up to: 2.9.2
Stable tag: 1.4
Version: 1.5

== Description ==

This plugin effectively and automatically makes it very difficult for spambots to harvest email addresses from your WordPress-powered blog. Email addresses may be placed in posts, comments, and pages, plain, as html links, or in a special "easy email" form, and they are automatically protected by emObA. All email addresses appearing on your blog will appear on the screen (if JavaScript is enabled) as active links to normal, valid, and correct email addresses (the actual email appears in the status bar when hovering), but to spambots they will have no recognizable features.  


It recognizes, and produces obfuscated active (click-to-send) email links for, 

 * standard email links (`<a href="mailto:you@example.com">Name</a>`), allowing (but ignoring) additional attributes both before and after the href attribute, and allowing a subject using the syntax `mailto:you@example.com?subject=...`.  

 * the special "easy to write" form  `[EMAIL Name | you@example.com]`, also allowing the `?subject=... ]` syntax.  (Earlier versions' much more fragile `[Name] you@example.com` remains available if the LEGACY flag is true.)  

 * a bare email address `you@example.com` (with or without "mailto:" in front of it; `?subject=` syntax not allowed here)  

These will all appear as active email links displaying "Name". In the cases of a bare email link (one which has no Name) or a link in which the Name is the email itself, the link will show as the email displayed in human-readable form, eg `you [@] example [.] com`, where the [@] and [.] are  text symbols or graphic images to hide them from spambots. 
 
This is accomplished with a combination of WordPress filter hooks and JavaScript. (If JavaScript is not enabled, the link will not be active.)

The email addresses occur in the HTML source only in a well-hidden encoding.  The email address is converted to hexadecimal and appears only as the value of a JavaScript variable.  That encoded email is separated in the JavaScript from the telltale `mailto:` to further confuse spambots. 


== Installation ==

1. Place the folder contained in the zip file into your wp-content/plugins directory.   

2. From your wp-admin screen, activate the plugin emObA - Email Obfuscator Advanced.    

3. You may open emoba.php with a text editor and set a number of configuration items (documented there, and in the FAQ).  There is no administrative configuration screen at present.


== Upgrade notice ==
emoBA 1.3+ requires WP 2.8+.  
(If needed for WP2.3+, you may hard-code appropriate paths around lines 31,32 of emoba.php.)


== Changelog ==

= 1.5 =
2010/03/16 Using graphics in email obfuscation (`GLYPHS=true`) no longer causes xhtml validation errors. `?subject=` syntax allowed in `[EMAIL | ]` form.
= 1.4 =
2010/03/09 Bugfix: now correctly allows extended email syntax "email?subject=yyy". Bugfix: now correctly allows extra spaces within shortcode [EMAIL | ].  Email link may exhibit email: `<a href="mailto:aa@bb.cc">aa@bb.cc</a>`; the exhibited email will be obfuscated. 
= 1.3 =
2010/01/27  Fixed problem causing link not to be displayed -- may occur under PHP 5.2.6 and older (due to the named-subpattern bug in preg_replace_callback). Added "easy to write" email tag `[EMAIL Name | A@B.C]`. Conversion of an email anchor allows (but ignores) other attributes besides href, and allows extended mailto: syntax (eg, ?subject=...). Introduced `BARE_TO_LINK` choice. Changed default textifying characters from dashes to hook and comma. Cleaned up code, JavaScript. 
= 1.2 =
2009/11/19  Fixed repeat email bug: correctly treats identical repeat emails (of all types).  Now converts emails placed in text widgets (requires WP 2.3).  Fixed problem with multiple spaces in the special form  [name]   a@b.cc .  Introduced `CLICKPOP`.
= 1.1 = 
2009/11/18  Fixed problem with operation in comments.    
= 1.0 = 
2009/11/16    


== Acknowledgements ==

This is a major modification of Email Obfuscator by Billy Halsey. That plugin seems to be abandoned.  I received great help in resolving the PHP bug from Joe D'Andrea.


== Frequently Asked Questions ==

1. The name?  obfuscate = obscurate = obnubilate < obliterate

1. What does the constant `GLYPHS` (defined at line 48 of emoba.php; default=true) do?

  When `GLYPHS` is defined true, visible representations of the email address will have graphical elements in place of `@` and `.`; when false, copyright symbol and comma will appear.
  
1. What does the constant `CLICKPOP` (defined at line 43 of emoba.php; default=false) do?

  When `CLICKPOP` is defined true, hovering over "Name" changes the link to "Click to email Name".

1. What does the constant `BARE_TO_LINK` (defined at line 53 of emoba.php; default=true) do?

  When `BARE_TO_LINK` is defined true, a bare email (`A@B.C`) will appear as a link; if false, it will appear as a glyphed address, but will not be an active link.
  
1. What does the constant `LEGACY` (defined at line 59 of emoba.php; default=false) do?

  If LEGACY is true, the old "simple" form `[Name] A@B.C` will be converted to an email link. This can be turned off to avoid problems with WordPress shortcuts, in which case the email will be treated as bare, preceded by [Name].  
  Regardless of the value of LEGACY, the new form `[EMAIL Name | A@B.C]` will be converted.

1. I don't like the copyright and comma you use in the textified emails!

  These can be edited to whatever text you want at lines 69 and 70 of emoba.php. (Just be careful not to lose the quotes.)  

1. What about styling and appearance?

 The css file is emoba_style.css. You can add appearance styling to the various classes. However, the display: attribute values must be left as shown in order that the hover popups work, and the emoba-glyph attributes are necessary for workable appearance (the height may be adjusted).
 
 `
	.emoba-pop { }
	.emoba-pop span.emoba-hover { display: none; } 
	.emoba-pop:hover span.emoba-hover {	display: inline; }
	.emoba-glyph { border-width:0; height: 7px; }
	.emoba-em { }

 `

1. How can I deal with emails in static files (header, footer, sidebar, etc)?

  Here is the simplest way: 
  Put the email in a page (with emoba active). Open the page in a browser, and copy the html source of that email (`<span id=emoba-nnnn">...</span>`, and also the `<script>...</script>` below it) to the template.  (Note: emObA works in text widgets directly.)
 
1. What is the static html created for the email "Name" `<A@B.C>`?
  `
	<span id="emoba-nnnn">
		<span class="emoba-pop">
			Name 
			<span>  
				<span class="emoba-em">
					A
					<img src="http://.../at-glyph.gif" alt="at" class="emoba-glyph" />
					B
					<img src="http://.../dot-glyph.gif" alt="dot" class="emoba-glyph" />
					C
				</span>  
			</span>
		</span>
	</span>
	<script>...
  `

1. What is the HTML generated by the JavaScript?  

  If `CLICKPOP` is false, 
  `
	<a id="emoba-nnnn" class="emoba-pop" title="Send email" href="mailto:[hexified]" >
		<span>
			Name
		</span>
	</a>
  `
  If `CLICKPOP` is true, 
  `
	<a id="emoba-nnnn" class="emoba-pop" title="Send email" href="mailto:[hexified]" >
		<span class="emoba-hover">
			Click to email
		</span>
		<span>
			Name
		</span>
	</a>
  `
  where [hexified] means the email `A@B.C` converted to %-hex characters.
 
 
  
1. I am using Simple:Press Forum, and this plugin really messes up email addresses, and I can't fix it!  What should I do??

	Here are full instructions for fixing emObA and Simple:Press to work together.  (It is not for the faint-of-heart!)
	
	1. Copy entire `function the_content()` from wp-include/post-content.php to sf-header-forum.php, around line 77 (just inside `function sf_setup_header`).  Rename the copied version `the_forum_content()` 
	
	1. Throughout the simple-forum plugin folder tree, change `the_content` to `the_forum_content`
	
	1. On the forum page template (if necessary, create one -- you can copy your default page template -- and select it for the forum page), change `the_content` to `the_forum_content`.
	
	1. In sf-hooks.php, change the line  

		`add_filter('sf_save_post_content', sf_package_links', 10);`  
	
		to  

		`add_filter('sf_show_post_content', 'sf_package_links', 10);`  

	1. At the bottom of emoba.php, after the other "`add_filter`"s, add the line  

		`add_filter('sf_show_post_content', 'emoba_replace');`