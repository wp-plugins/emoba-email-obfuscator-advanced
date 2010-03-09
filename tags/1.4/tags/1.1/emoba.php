<?php
/*
Plugin Name: emObA - Email Obfuscator Advanced
Description: Scans pages, posts, comments for email addresses and creates mailto links which are difficult for 'bot harvesters to find. Typing A@B.C results in a "A-B-C" link;  href="mailto:" links are preserved but obfuscated; the special occurrence "[Name] A@B.C"  is recognized and results in a link on "Name".  Without JavaScript, hovering  pops up the email with graphic glyphs for "@" and ".".  (Based on eMob Email Obfuscator 1.1 by Billy Halsey.)
Version: 1.1
License: GPL
Author: Kim Kirkpatrick
Author URI:http://kirknet/wpplugins
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
Place the css in the head:
****/
function emoba_includes(){
  wp_enqueue_style( 'emoba_style', plugin_dir_url(__FILE__) . 'emoba_style.css');
}
add_action('init','emoba_includes');


/****
Option:
If CLICKPOP is true, hovering over the link "addr" changes it to "Click to email addr". (This switch has no effect if JavaScript is off.)
****/
define ("CLICKPOP", false);


/****
Here we designate the graphic glyphs used for at/dot separators in the displayed email addresses.
You may want to change the alts or i18n them.

If text rather than glyph is desired for the separators, replace with your versions of define( 'SEP_AT', ' [at] ' ); and define( 'SEP_DOT', ' [dot] ' );
****/
define( 'SEP_AT',  '<img src="' . plugin_dir_url(__FILE__) . 'at-glyph.gif"  class="emoba-glyph" alt="at"  height="8" />' );
define( 'SEP_DOT', '<img src="' . plugin_dir_url(__FILE__) . 'dot-glyph.gif" class="emoba-glyph" alt="dot" height="9" />' );


/****
This replaces "." with SEP_DOT and "@" with SEP_AT in $email
****/
function emoba_glyph_email($email) {
  $email = str_replace('.', SEP_DOT, $email);
  $email = str_replace('@', SEP_AT, $email);
  return '<span class="emoba-em">' . $email . '</span>';
}

/****
This replaces "." and "@" with "-" in $email
****/
function emoba_dash_email($email) {
  $email = str_replace('.', '-', $email);
  $email = str_replace('@', '-', $email);
  return  $email ;
}


/****
This is the email address seen when JavaScript is not available
****/
function emoba_readable_mail($email="", $name="(Hover)" ) {

  $email = str_replace('.',
        '</span><span class="emoba-symbol">' . SEP_DOT . '</span><span class="emoba-em">',
         $email);
  $email = str_replace('@',
        '</span><span class="emoba-symbol">' . SEP_AT . '</span><span class="emoba-em">',
         $email);
  $addr = '<span class="emoba-pop">' . $name . '<span>&nbsp;&nbsp;<span class="emoba-em">';
  $addr .= $email . '</span>&nbsp;&nbsp;</span></span>';
  return $addr;
}


/****
This is the RE expression for detecting email addresses. (The result found is returned as email=>result.)

****/
define( "ADDR_PATTERN",
        "(?P<email>[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})" );


/****
This encodes the email address. It converts string to HTML-hex representation of character ordinal
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
  $emoba_js = "
<script type=\"text/javascript\">
  var mailtostring = 'mailto:';
  var mailNode = document.getElementById('$id');
  var linkNode = document.createElement('a');
  linkNode.title = 'Send email';
  linkNode.id = '$id';
  var mailtolink = mailtostring + '$link';
  linkNode.href = mailtolink;";
if (true == CLICKPOP) {
  $emoba_js .= "
  var spanNode = document.createElement('span');
  spanNode.innerHTML = 'Click to email ';
  linkNode.appendChild(spanNode);
  linkNode.className = 'emoba-pop';";
}
$emoba_js .= "
  var tNode = document.createTextNode('$ename');
  linkNode.appendChild(tNode);
  mailNode.parentNode.replaceChild(linkNode, mailNode);
</script>
";
  return $emoba_js;
}


/****
The main function.
1. Detect and process emails, in this order:
  a. as a link <a href="mailto:A@B.C">Name</a>
  b. as a special email encoding [Name] A@B.C
  c. as a raw email A@B.C; the "Name" in this case is "A-B-C"
2. Each of these creates a random-id <span> which includes the Name and pop-up email with graphics for @ and . (in case JavaScript is off), and inserts JavaScript.
3. The JavaScript (via emoba_addJScript()) replaces the <span> with the approriate <a> link.(with the address encoded).
4. CSS (for class emoba-pop) creates the hover effect when JavaScript is turned off.
****/
function emoba_replace($content) {
// First, convert full  <a href="mailto:A@B.C >Name</a>  links
  $addr_pattern = '|<a href="mailto:' .ADDR_PATTERN. '"[^>]*>(?P<name>[^<]+)</a>|i';
  preg_match_all($addr_pattern, $content, $matches, PREG_SET_ORDER);
  foreach ( $matches as $match ) {
    $em_email = $match[email];
    $em_name = $match[name];
    $id = "emoba-" . rand(1000, 9999);
    $repaddr = "<span id=\"$id\">";
    $repaddr .= emoba_readable_mail($em_email, $em_name) . "</span>\n";
    $repaddr .= emoba_addJScript($em_email, $em_name, $id);
    $repaddrs[] = $repaddr;
    $targets[] = $match[0];
  }
  $content = str_replace($targets, $repaddrs, $content);

// Set search pattern to A@B.C
  $addr_pattern = ADDR_PATTERN;

// Remove mailto:'s from any remaining  mailto:A@B.C
//  (this won't affect the full links just processed, since they no longer contain the string linkto:A@B.C)
  $content = preg_replace("/mailto:".$addr_pattern."/i", '$1', $content);
// and convert the special pattern [Name] A@B.C to email link <a href="mailto:A@B.C >Name</a>
  preg_match_all("/\[(?P<name>[^]]+)]\s*".$addr_pattern."/i", $content, $matches, PREG_SET_ORDER);
  foreach ( $matches as $match ) {
    $em_email = $match[email];
    $em_name = $match[name];
    $id = "emoba-" . rand(1000, 9999);
    $repaddr = "<span id=\"$id\">";
    $repaddr .= emoba_readable_mail($em_email, $em_name). "</span>\n";
    $repaddr .= emoba_addJScript($em_email, $em_name, $id);
    $repaddrs[] = $repaddr;
    $targets[] = $match[0];
  }
  $content = str_replace($targets, $repaddrs, $content);

// Finally, convert any remaining addresses A@B.C to link <a href="mailto:A@B.C">A-B-C</a>
  preg_match_all('/'.$addr_pattern.'/i', $content, $matches, PREG_SET_ORDER); $j=1;
  foreach ( $matches as $match ) {
    $em_email = $match[email];
    $em_name = emoba_dash_email($em_email);
    $readable_email = emoba_glyph_email($em_email);
    $id = "emoba-" . rand(1000, 9999);
    $repaddr = "<span id=\"$id\">";
    $repaddr .= $readable_email . "</span>\n";
    $repaddr .= emoba_addJScript($em_email, $em_name, $id);
    $repaddrs[] = $repaddr;
    $targets[] = $match[0];
  }
  $content = str_replace($targets, $repaddrs, $content);

  return $content;
}


/****
Finally, link emoba_replace() into WordPress filters
****/
add_filter('the_content', 'emoba_replace');
add_filter('the_excerpt', 'emoba_replace');
add_filter('comment_text', 'emoba_replace', 1); // must get there before the comment text filters do
add_filter('author_email', 'emoba_replace');
add_filter('comment_email', 'emoba_replace');

?>