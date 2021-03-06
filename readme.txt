=== emObA - Email Obfuscator Advanced ===
Author: Kim Kirkpatrick
Contributors:  kirkpatrick, Joe d'Andrea, sassymonkey, capnhairdo, luckyduck288, 
Tags: spam, email, mail, address, addresses, hide, JavaScript
Requires at least: 2.8
Tested up to: 3.1
Stable tag: 2.0
Version 2.0


== Description ==

emObA - Email Obfuscator Advanced - effectively and automatically makes it difficult for spambots to harvest email addresses from your WordPress-powered blog. Email addresses may be placed in posts, comments, and pages, as html links, in a special "easy email" form, or  as the address itself, and they will be protected by emObA automatically. All email addresses appearing on your blog will appear on the screen (if JavaScript is enabled) as active links to normal, valid, and correct email addresses, but to spambots they will have no recognizable features.  (As usual, the actual email appears in the status bar when hovering.) 

emObA recognizes, and produces obfuscated active (click-to-send) email links for, 

 * standard email links (`<a href="mailto:you@example.com">Real Name</a>`), allowing class and style attributes (but ignoring other attributes), and allowing an email Subject using the syntax `mailto:you@example.com?subject=...`.  

 * the special "easy to write" form  `[EMAIL Real Name | you@example.com]`, also allowing the `?subject=... ]` syntax.  (Earlier versions' much more fragile `[Real Name] you@example.com` remains available if LEGACY is chosen.)  

 * a bare email address `you@example.com`, with or without "mailto:" in front of it. (`?subject=` syntax not allowed here.)

These will all appear as active email links displaying "Real Name". In the cases of a bare email link (one which has no Real Name) or a link in which the Real Name is the email itself, the link will show as the email displayed in human-readable form, eg `you [@] example [.] com`, where the [@] and [.] are either text symbols or graphic images (as set in administration), hiding the email addresses from spambots. 
 
If JavaScript is not enabled, the email will appear in obfuscated but human-readable form but the link will not be active.

The email addresses occur in the HTML source only in a well-hidden encoding.  The email address is converted to hexadecimal and appears only as the value of a JavaScript variable.  That encoded email is separated in the JavaScript from the telltale `mailto:` to further confuse spambots. 

I believe any legitimate email will be recognized.  However, no attempt at validation is made -- certain illegally formed addresses will also be recognized, for example, ones containing two successive .'s. (Note: Legal characters before the @ are !#$%&'*+/=?^_{|}~- and `.)

I've designed this plug-in with "real name" emails in mind -- `<a href="mailto:you@example.com">Real Name</a>` or `[EMAIL Real Name | you@example.com]`, which display as `Real Name`.  This will follow whatever styling you apply to your text and to links.  However, if you primarily obfuscate lists of bare email addresses -- `you@example.com` -- you may not be satisfied with the appearance.  They will appear with either glyphs or specified text symbols in place of `@` and `.`.  The color and weights of the glyphs are fixed (though they do change size with surrounding text), and they don't look exactly like the font symbols they replace. And if text symbols are used, they certainly don't look exactly like `@` and `.`.  


== Installation ==

1. Place the folder contained in the zip file into your wp-content/plugins directory.   

2. From your wp-admin screen, activate the plugin emObA - Email Obfuscator Advanced.    

3. You may open emoba.php with a text editor and set a number of configuration items (documented there, and in the FAQ).  There is no administrative configuration screen at present.


== Upgrade notice ==

= 2.0 =
Now has administration page for setting parameters. Works (reasonably) with excerpts.

= 1.6.5 =
Now handles preexisting class and style attributes in email link.

= 1.6 = 
Fixed css to work with WP3.0 default theme. Added classes to enable styling of link.

= 1.3+ =
requires WP 2.8+.  
(If needed for WP2.3+, you may hard-code appropriate paths around lines 31,32 of emoba.php.)


== Changelog ==

= 2.0 =
2010/12/04 Administration page added.  Slight changes in regex -- allow longer (up to 6) top-domain name; simplify handling of case.  Removed spaces that appeared after an email at end of sentence, and placed parentheses around eaddress when no javascript (thanks, luckyduck288).  Fixed misbehavior on the_excerpt.

= 1.6.5 =
2010/11/20 Extra attributes in link broke parse in 1.6; now class and style are carried through, any others are swallowed harmlessly.  
= 1.6 =
2010/11/18 fixed css for WP3.0 default theme.  Bug fix: ordinary links and email links in same line broken (thanks, NickStrong, for pointing this out) Added class emoba-link to the anchor, and class emoba-realname to the span surrounding the link's visible text (thanks for suggestion, sassymonkey). Improved display of glyphed addresses -- prettier, scale with text (thanks, capnhairdo). 
= 1.5.1 =
2010/04/01 Bug fix. An editing slip in 1.5 broke ordinary links and [EMAIL | ].  Apologies.
= 1.5 =
2010/04/01 Using graphics in email obfuscation (`GLYPHS=true`) no longer causes xhtml validation errors. `?subject=` syntax allowed in `[EMAIL | ]` form.
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

1. What does the option `glyphs` do?

  When `glyphs` is checked, visible representations of the email address will have graphical elements in place of `@` and `.`; when false, copyright symbol and comma will appear.
  
1. What does the option `clickpop` do?

  When `clickpop` is checked, hovering over "Name" changes the link to "Click to email Name".

1. What does the option `bare has link` do?

  When `bare has link` is checked, a bare email (`A@B.C`) will appear as a link; unchecked, it will appear as a glyphed address, but will not be an active link.
  
1. What does the option `legacy` (default: unchecked) do?

  If 'legacy` is true, the old "simple" form `[Name] A@B.C` will be converted to an email link. This can be turned off to avoid problems with WordPress shortcuts, in which case the email will be treated as bare, preceded by [Name].  
  Regardless of the setting of `legacy`, the new form `[EMAIL Name | A@B.C]` will be converted.

1. I don't like the copyright symbol and centered dot you use in the textified emails!

  These can be set to whatever text you want. You may use any characters including html entities.

1. What about styling and appearance?

 The css file is emoba_style.css. You can add appearance styling to the various classes. However, the display: attribute values shown below must be unchanged in order that the hover popups work, and the emoba-glyph attributes are necessary for workable appearance (the height may be adjusted).
 
 `
	.emoba-pop span.emoba-hover { 
		display: none; 
	} 
	.emoba-pop:hover span.emoba-hover {
		display: inline; 
	}
	img.emoba-glyph {
	  border-width:0;
	  image-rendering:optimizeQuality;
	  -ms-interpolation-mode:bicubic;
	  height: 0.75em;
	  margin:0 0.15em -0.1em 0.15em;
	}
 `

1. How can I deal with emails in static files (header, footer, sidebar, etc)?

  Here is the simplest way: 
  Put the email in a page (with emoba active). Open the page in a browser, and copy the html source of that email (`<span id=emoba-nnnn">...</span>`, and also the `<script>...</script>` below it) to the template.  (Note: emObA works in text widgets directly.)
 
