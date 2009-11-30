=== emObA - Email Obfuscator Advanced ===
Author: Kim Kirkpatrick
Contributors:  kirkpatrick
Donate link: http://kirknet.org/wpplugins/
Tags: spam, email, mail, address, addresses, hide, JavaScript
Requires at least: 2.3
Tested up to: 2.8.6
Stable tag: 1.2
Version: 1.2.5

== Description ==

This plugin effectively and automatically makes it very difficult for spambots to harvest email addresses from your WordPress-powered blog. Email addresses may be placed in posts, comments, and pages, plain, as html links, or in a special "easy email" form, and they are automatically protected by emObA. All email addresses appearing on your blog will appear on the screen as active links to normal, valid, and correct email addresses (the actual email appears in the status bar when hovering), but to spambots they will have no recognizable features.  


It recognizes, and produces obfuscated active (click-to-send) email links for, 

 * standard email links (`<a href="mailto:you@example.com">Name</a>`)  

 * the special "easy to write" form  `[Name] you@example.com`  

 * a bare email address `you@example.com` (with or without "mailto:" in front of it)  

These will appear as standard email links displaying "Name". A bare email will appear as a link `you ^ example com` (punctuated with text in place of @ and .), since there is no Name.
 
This is accomplished with a combination of WordPress filter hooks and JavaScript. If the browser is JavaScript-enabled, visitors to the site will see active email address links. If JavaScript is not enabled, hovering over the "link" will bring up a popup showing the email in human-readable form, eg `you [@] example [.] com`.  The [@] and [.] are  graphic images, not text, so the parts of the address are separated by lengthy runs of html (`<img ... />`).

The email addresses occur in the HTML source only in a well-hidden encoding.  The email address is converted to hexadecimal and appears only as the value of a JavaScript variable.  That encoded email is separated in the JavaScript from the telltale `mailto:` to further confuse spambots.  The no-JavaScript popup address is encoded in the JavaScript with graphics representing `@` and `.`, so even a fairly smart spambot will not be led easily to the address.


== Installation ==

1. Place the folder contained in the zip file into your wp-content/plugins directory.   

2. From your wp-admin screen, activate the plugin emObA - Email Obfuscator Advanced.    


== Changelog ==

= 1.2.5 =
2009/12/01  Cleaned up some code, improved JavaScript.  Introduced `BARE_TO_LINK`. Changed default textifying characters from dashes to hook and space.
= 1.2 =
2009/11/19  Fixed repeat email bug: correctly treats identical repeat emails (of all types).  Now converts emails placed in text widgets (requires WP 2.3).  Fixed problem with multiple spaces in the special form  [name]   a@b.cc .  Introduced `CLICKPOP`.
= 1.1 = 
2009/11/18  Fixed problem with operation in comments.    
= 1.0 = 
2009/11/16    


== Acknowledgements ==

This is a major modification of Email Obfuscator by Billy Halsey. That plugin seems to be abandoned.


== Frequently Asked Questions ==

1. The name?  obfuscate = obscurate = obnubilate < obliterate

1. What does the constant `CLICKPOP` (defined at line 31 of emoba.php) do?

  When `CLICKPOP` is defined true, hovering over "Name" changes the link to "Click to email Name".

1. What does the constant `BARE_TO_LINK` (defined at line 38 of emoba.php) do?

  When `BARE_TO_LINK` is defined true, a bare email (`A@B.C`) will appear as a link; if false, it will appear as a glyphed address, but will not be an active link.

1. I don't like the hook and space you use in the textified emails!

  These can be edited to whatever text you want at lines 86 and 87 of emoba.php, in the function `emoba_textify_email()`.  (Just be careful not to lose the quotes.)  

1. What about styling and appearance?

 The following css is used; it appears in emoba_style.css.  You can add appearance styling, but the display: attribute values must be left as shown in order that the hover popups work, and the emoba-glyph attributes are necessary for workable appearance (the height may be adjusted):
 
 `
	.emoba-pop { }
	.emoba-pop span.emoba-hover { display: none; } 
	.emoba-pop:hover span.emoba-hover {	display: inline; }
	.emoba-glyph { border-width:0; height: 7px; }
	.emoba-em { }

 `

1. How can I deal with emails in static files (header, footer, sidebar, etc)?

  Simplest way: Put the email in a page; look at source from browser, and copy the resulting html source of that email (`<span id=emoba-nnnn">...</span>`, as shown next, and also the `<script>...</script>` below it) to the template.  (But emObA works in text widgets directly.)
 
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