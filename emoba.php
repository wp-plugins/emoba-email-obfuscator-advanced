<?php
/*
Plugin Name: emObA
Description: emObA (email Obfuscator Advanced) -- Scans pages, posts, comments for email addresses and creates mailto links which are difficult for 'bot harvesters to find. Typing A@B.C results in a "A@B.C" link, with grahic representations of "@"and "."; html anchor links with href="mailto:" are obfuscated; the special occurrence "[EMAIL Name | A@B.C]"  is recognized and results in an obfuscated link on "Name".  Without JavaScript, hovering pops up the email with graphic glyphs for "@" and ".".  (Based on eMob Email Obfuscator 1.1 by Billy Halsey.)
Version: 1.6
License: GPL
Author: Kim Kirkpatrick
Author URI: http://kirknet/wpplugins
*/
/*  Copyright 2009  Kim Kirkpatrick  (email : kirk@kirknet.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/****
Place the css and js in the head:
****/
function emoba_includes(){
  wp_enqueue_style( 'emoba_style', plugin_dir_url(__FILE__) . 'emoba_style.css');
  wp_enqueue_script('emoba_script', plugin_dir_url(__FILE__) . 'emoba_script.js');
}
add_action('init','emoba_includes');


/**********************************/
/********* CONFIGURATION *********/

/****
If CLICKPOP is true, hovering over the link "addr" changes it to "Click to email addr".
****/
define ("CLICKPOP", false);

/****
If GLYPHS is true, glyphs will be used, text otherwise, for replacing @ and . in displayed emails.
****/
define("GLYPHS", true);

/****
If BARE_TO_LINK is true, bare emails (a@b.c) will be converted to a link.  If false, the email will appear in the glyph form, but there will be no link.
****/
define ("BARE_TO_LINK", true);

/****
If LEGACY is true, the old "simple" form `[Name] A@B.C` will be converted to an email link. This can be turned off to avoid problems with WordPress shortcuts, in which case the email will be treated as bare, preceded by [Name].
Regardless of the value of LEGACY, the new form `[EMAIL Name | A@B.C]` will be properly converted.
****/
define ("LEGACY", false);

/****
Here we designate the symbols used for at/dot separators in the displayed email addresses.
You may want to change the alts or i18n them.
****/
if (true == GLYPHS) {
	define('AT_SYMBOL', '<img src="'.plugin_dir_url(__FILE__).'at-glyph.gif"  alt="at"  class="emoba-glyph" />' );
	define('DOT_SYMBOL', '<img src="'.plugin_dir_url(__FILE__).'dot-glyph.gif" alt="dot" class="emoba-glyph" />' );
}else{
	define('AT_SYMBOL', '&copy;');
	define('DOT_SYMBOL', ',');
}

/********* /CONFIGURATION *********/
/**********************************/


/****
This replaces "@" with AT_SYMBOL and "." with DOT_SYMBOL
If $email doesn't include "@", it isn't an email; don't modify any dots in it.
****/
function emoba_symb_email($email) {
  if (!strpos($email, '@')) return $email;
  $email = str_replace('.', DOT_SYMBOL, $email); // must be first -- avoid replacing dot in IMG filename!
  $email = str_replace('@', AT_SYMBOL, $email);
  return '<span class="emoba-em">' . $email . '</span>';
}

/****
This constructs a glyphed or textified email address
****/
function emoba_readable_email($email="", $name="(Hover)" ) {
  $transformed_email = emoba_symb_email($email);
  $addr = '<span class="emoba-pop">' . $name . '<span ';
  $addr .= '>&nbsp;&nbsp;';
  $addr .= $transformed_email . '&nbsp;&nbsp;</span></span>';
  return $addr;
}


/****
This is the RE expression for detecting email addresses.
****/
define( "EMAIL", "([A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4})" );


/****
This converts the email's string of character ordinals to %-hex representation
It splits off any ?subject=yyy, replaces spaces by %20, and tacks it back onto the
encoded email.
****/
function emoba_hexify_mailto($mailto) {
	$mt = explode('?', $mailto);
		$hexified = '';
  for ($i=0; $i < strlen($mt[0]); $i++)
    $hexified .= '%' . strtoupper(base_convert(ord($mt[0][$i]), 10, 16));
  if (strlen($mt[1])>0) {
  	$mt[1] = str_replace(' ', '%20', $mt[1]);
   	$hexified .= '?'.$mt[1];
   }
   return $hexified;
}


/****
The JavaScript for creating the email link and popup
****/
function emoba_addJScript($email, $ename, $id) {
  $link   = emoba_hexify_mailto($email);
  $clean_name = str_replace("<", "&lt;", $ename);
  $emoba_js = "<script type=\"text/javascript\">emobascript('".$link."','".$clean_name."','".$id."',".((true==CLICKPOP)?1:0).");</script>";
	return $emoba_js;
}


/****
The main function.
1. Detect and process emails, in this order:
  a. as a link <a href="mailto:A@B.C">Name</a>
  b. as a special email encoding [Name] A@B.C
  c. as a raw email A@B.C (The "Name" in this case is the email itself.)  This will be a link if BARE_TO_LINK==true.
   If Name is an email, it will be obfuscated appropriately.
2. Each of these creates a random-id span which includes the Name and pop-up email with graphics for @ and . (in case JavaScript is off), and inserts JavaScript.
3. The JavaScript (via emoba_addJScript()) replaces the <span> with the approriate <a> link.(with the address encoded).
4. CSS (for classes emoba-pop, emoba-hover) creates the hover effect when JavaScript is turned off.
****/

function emoba_replace($content) {

// (1) convert full email link <a  href="mailto:A@B.C?subject=sss" >Name</a>

  $content = preg_replace_callback(
    '!<a(?:[\s]|&nbsp;)*href="mailto:' .EMAIL. '([?][^"]*)?"[^>]*>([^<]*)</a>!i',
    create_function(
      '$match',
      '$em_email = $match[1].$match[2];
			$em_name = emoba_symb_email($match[3]);
			$id = "emoba-" . rand(1000, 9999);
			$repaddr = "<span id=\"$id\">";
			$repaddr .= emoba_readable_email($em_email, $em_name) . "</span>\n";
			$repaddr .= emoba_addJScript($em_email, $em_name, $id);
			return $repaddr;' ),
    $content );


//  (1a) We can now remove mailto:'s from any remaining  mailto:A@B.C
//  (This won't affect the full links just processed, since they no longer contain the string mailto:A@B.C)

  $content = preg_replace("!mailto:".EMAIL."!i", '$1', $content);


// (2) Convert the special pattern [EMAIL Name | A@B.C] to email link <a href="mailto:A@B.C >Name</a>
//     Allows any number of spaces at each position within [EMAIL|]
  $content = preg_replace_callback(
    '!\[EMAIL(?:[\s]|&nbsp;)*([^|]+)(?:(?:[\s]|&nbsp;)*[|](?:[\s]|&nbsp;)*)'.EMAIL.'([?][^]]*?)?(?:[ ]|&nbsp;)*]!',
    create_function(
      '$match',
			'$em_email = $match[2].$match[3];
			$em_name = emoba_symb_email($match[1]);
			$id = "emoba-" . rand(1000, 9999);
			$repaddr = "<span id=\"$id\">";
			$repaddr .= emoba_readable_email($em_email, $em_name). "</span>\n";
			$repaddr .= emoba_addJScript($em_email, $em_name, $id);
			return $repaddr;' ),
    $content );

if ( true == LEGACY ) {

// (2') (Legacy) Convert the special pattern [Name] A@B.C to email link <a href="mailto:A@B.C >Name</a>

  $content = preg_replace_callback(
    '!\[([^]]+)\](?:[\s]|&nbsp;)*'.EMAIL.'!',
    create_function(
      '$match',
			'$em_email = $match[2];
			$em_name = emoba_symb_email($match[1]);
			$id = "emoba-" . rand(1000, 9999);
			$repaddr = "<span id=\"$id\">";
			$repaddr .= emoba_readable_email($em_email, $em_name). "</span>\n";
			$repaddr .= emoba_addJScript($em_email, $em_name, $id);
			return $repaddr;' ),
    $content );

}


if ( true == BARE_TO_LINK ) {

// (3) Convert bare email addresses A@B.C to the link <a href="mailto:A@B.C">A B C</a>

  $content = preg_replace_callback(
    '!'.EMAIL.'!',
    create_function(
      '$match',
			'$em_email = $match[1];
			$em_name = emoba_symb_email($em_email);
			$id = "emoba-" . rand(1000, 9999);
			$repaddr = "<span id=\"$id\">";
			$repaddr .= emoba_symb_email($em_email) . "</span>\n";
			$repaddr .= emoba_addJScript($em_email, $em_name, $id);
			return $repaddr;' ),
    $content );

}else{

// (3) Convert any remaining addresses A@B.C
  $content = preg_replace_callback(
    '!'.EMAIL.'!',
    create_function(
      '$match',
			'$em_email = $match[1];
		  $readable_email = emoba_symb_email($em_email);
			return $readable_email;' ),
    $content );

}

// We're through!
  return $content;
}


/****
Finally, link emoba_replace() into WordPress filters
******/
add_filter('the_content', 'emoba_replace');
add_filter('the_excerpt', 'emoba_replace');
add_filter('comment_text', 'emoba_replace', 1); // high priority, to get there before the comment text filters do
add_filter('widget_text', 'emoba_replace');
add_filter('author_email', 'emoba_replace');
add_filter('comment_email', 'emoba_replace');





/****
For use with Simple:Press Forum, only after SPF has been modified as follows (not for the faint-of-heart!):
	1. Change the_content -> the_forum_content (throughout SPF files and on forum page template)
	2. Copy entire function the_content() from wp-include/post-content.php to sf-header-forum.php (around line 77, just inside function sf_setup_header); rename the copied version the_forum_content()
	3. In sp-hooks.php, change add_filter('sf_save_post_content', 'sf_package_links', 10);
   to
  	 add_filter('sf_show_post_content', 'sf_package_links', 10);
UNCOMMENT the next two add_filter lines to use modified SPF with emObA
****/
// add_filter('sf_show_post_content', 'emoba_replace'); // Priority no greater than 10 (default)
// add_filter('the_forum_content', 'emoba_replace', 1);
