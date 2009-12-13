<?php
/*
Plugin Name: emObA
Description: emObA - Email Obfuscator Advanced -- Scans pages, posts, comments for email addresses and creates mailto links which are difficult for 'bot harvesters to find. Typing A@B.C results in a "A-B-C" link;  href="mailto:" links are preserved but obfuscated; the special occurrence "[EMAIL Name A@B.C]"  is recognized and results in a link on "Name".  Without JavaScript, hovering  pops up the email with graphic glyphs for "@" and ".".  (Based on eMob Email Obfuscator 1.1 by Billy Halsey.)
Version: 1.2.5
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
If CLICKPOP is true, hovering over the link "addr" changes it to "Click to email addr".  (This switch has no effect if JavaScript is off.)
****/
define ("CLICKPOP", true);


/****
If BARE_TO_LINK is true, bare emails (a@b.c) will be converted to a link with visible "name" a ^ b c.  If false, there will be no link, but the email will appear in the glyph form.
(A problem with CDATA commenting being removed by WordPress (space forced into <! [CDATA and conversion of close to ]]&gt;) prevent the link from containing the glyph name.  The only workaraound I know of requires hacking WordPress.)
****/
define ("BARE_TO_LINK", true);


/****
Place the css in the head:
****/
function emoba_includes(){
  wp_enqueue_style( 'emoba_style', plugin_dir_url(__FILE__) . 'emoba_style.css');
}
add_action('init','emoba_includes');


/****
Here we designate the graphic glyphs used for at/dot separators in the displayed email addresses.
You may want to change the alts or i18n them.

If text rather than glyph is desired for the separators,
replace with your versions of define( 'SEP_AT', ...) and define( 'SEP_DOT', ... );
****/
define( 'SEP_AT',  '<img src="' . plugin_dir_url(__FILE__) . 'at-glyph.gif"  alt="at"  class="emoba-glyph" />' );
define( 'SEP_DOT', '<img src="' . plugin_dir_url(__FILE__) . 'dot-glyph.gif" alt="dot" class="emoba-glyph" />' );


/****
This replaces "@" with SEP_AT and "." with SEP_DOT in $email
****/
function emoba_glyph_email($email) {
  $email = str_replace('.', SEP_DOT, $email);
  $email = str_replace('@', SEP_AT , $email);
  return '<span class="emoba-em">' . $email . '</span>';
}


/****
This constructs a glyphed email address for use when JavaScript is not available
****/
function emoba_readable_email($email="", $name="(Hover)" ) {
  $glyph_email = emoba_glyph_email($email);
  $addr = '<span class="emoba-pop">' . $name . '<span>&nbsp;&nbsp;';
  $addr .= $glyph_email . '&nbsp;&nbsp;</span></span>';
  return $addr;
}


/****
This replaces "@" and "." with " ^ " and " " in $email; used for bare (nameless) emails
****/
function emoba_textify_email($email) {
  $email = str_replace('@', ' ^ ', $email);
  $email = str_replace('.', ' ', $email);
  return  $email ;
}


/****
This is the RE expression for detecting email addresses. (The result found is returned as $match[email].)
****/
define( "EMAIL",
        "(?P<email>[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4})" );


/****
This converts the email's string of character ordinals to %-hex representation
****/
function emoba_hexify_mailto($mailto) {
  $hexified = '';
  for ($i=0; $i < strlen($mailto); $i++) {
    $hexified .= '%' . strtoupper(base_convert(ord($mailto[$i]), 10, 16));
  }
  return $hexified;
}


/****
The JavaScript for creating the email link and popup
****/
function emoba_addJScript($email, $ename, $id) {
  $link   = emoba_hexify_mailto($email);
  $emoba_js = <<<AJS
<script type="text/javascript">
  var mailtostring = 'mailto:';
  var mailNode = document.getElementById('$id');
  var linkNode = document.createElement('a');
  linkNode.title = 'Send email';
  linkNode.id = '$id';
  var mailtolink = mailtostring + '$link';
  linkNode.href = mailtolink;
AJS;
if (true == CLICKPOP) {
  $emoba_js .= <<<AJS

  var spanNode = document.createElement('span');
  spanNode.className = 'emoba-hover';
  spanNode.innerHTML = 'Click to email ';
  linkNode.appendChild(spanNode);
  linkNode.className = 'emoba-pop';
AJS;
}
$emoba_js .= <<<AJS

  var tNode = document.createElement('span');
  tNode.innerHTML = '$ename';
  linkNode.appendChild(tNode);
  mailNode.parentNode.replaceChild(linkNode, mailNode);
</script>
AJS;
  return $emoba_js;
}


/****
The main function.
1. Detect and process emails, in this order:
  a. as a link <a href="mailto:A@B.C">Name</a>
  b. as a special email encoding [Name] A@B.C
  c. as a raw email A@B.C (The "Name" in this case is the email itself, textified or glyphed depending on BARE_TO_LINK.)
2. Each of these creates a random-id span which includes the Name and pop-up email with graphics for @ and . (in case JavaScript is off), and inserts JavaScript.
3. The JavaScript (via emoba_addJScript()) replaces the <span> with the approriate <a> link.(with the address encoded).
4. CSS (for classes emoba-pop, emoba-hover) creates the hover effect when JavaScript is turned off.
****/

function emoba_replace($content) {

// (1) convert full  <a href="mailto:A@B.C >Name</a>  links

  $content = preg_replace_callback(
    '!<a(.*?)href="mailto:' .EMAIL. '"[^>]*>(?P<name>[^<]+)</a>!i',
    create_function(
      '$match',
      '$em_email = $match[email];
			$em_name = $match[name];
			$id = "emoba-" . rand(1000, 9999);
			$repaddr = "<span id=\"$id\">";
			$repaddr .= emoba_readable_email($em_email, $em_name) . "</span>\n";
			$repaddr .= emoba_addJScript($em_email, $em_name, $id);
			$repaddrs[] = $repaddr;
			return $repaddr;' ),
    $content );

//  We can now remove mailto:'s from any remaining  mailto:A@B.C
//  (This won't affect the full links just processed, since they no longer contain the string linkto:A@B.C)

  $content = preg_replace("!mailto:".EMAIL."!i", '$1', $content);

// (2) Convert the special pattern [Name] A@B.C to email link <a href="mailto:A@B.C >Name</a>
// (2) Convert the special pattern [EMAIL Name A@B.C] to email link <a href="mailto:A@B.C >Name</a>

  $content = preg_replace_callback(
//    "!\[(?P<name>[^]]+)\]([\s]|&nbsp;)*".EMAIL."!", // [Name] A@B.C
    "!\[EMAIL([\s]|&nbsp;)+(?P<name>[a-zA-Z0-9]+(([\s]|&nbsp;)[a-zA-Z0-9_-]+)*)([\s]|&nbsp;)+" . EMAIL . "([\s]|&nbsp;)*]!", // [EMAIL Name A@B.C]
    create_function(
      '$match',
			'$em_email = $match[email];
			$em_name = $match[name];
			$id = "emoba-" . rand(1000, 9999);
			$repaddr = "<span id=\"$id\">";
			$repaddr .= emoba_readable_email($em_email, $em_name). "</span>\n";
			$repaddr .= emoba_addJScript($em_email, $em_name, $id);
			return $repaddr;' ),
    $content );

if (true == BARE_TO_LINK ) {

// (3) Convert any remaining addresses A@B.C to the link <a href="mailto:A@B.C">A ^ B C</a>

  $content = preg_replace_callback(
    '!'.EMAIL.'!',
    create_function(
      '$match',
			'$em_email = $match[email];
			$em_name = emoba_textify_email($em_email);
			$readable_email = emoba_glyph_email($em_email);
			$id = "emoba-" . rand(1000, 9999);
			$repaddr = "<span id=\"$id\">";
			$repaddr .= $readable_email . "</span>\n";
			$repaddr .= emoba_addJScript($em_email, $em_name, $id);
			return $repaddr;' ),
    $content );

}else{

// (3) Convert any remaining addresses A@B.C to the glyphed form A [at] B [dot] C

  $content = preg_replace_callback(
    '!'.EMAIL.'!',
    create_function(
      '$match',
			'$em_email = $match[email];
			$em_name = emoba_textify_email($em_email);
			$readable_email = emoba_glyph_email($em_email);
			$repaddr = $readable_email;
			return $repaddr;' ),
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
For use with Simple:Press Forum, only after SPF has been modified:
	1. the_content -> the_forum_content (throughout SPF files and on forum page template
	2. copy function the_content from wp-include/post-content.php to sf-header-forum.php around line 77 (just inside function sf_setup_header); rename the copied version the_forum_content()
	3. add_filter('sf_save_post_content', 'sf_package_links', 10);
   changed to
  	 add_filter('sf_show_post_content', 'sf_package_links', 10);
****/
add_filter('sf_show_post_content', 'emoba_replace'); // Priority no greater than 10 (default)
