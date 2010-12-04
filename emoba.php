<?php
/*
Plugin Name: emObA
Description: emObA (email Obfuscator Advanced) -- Scans pages, posts, comments for email addresses and creates mailto links which are difficult for 'bot harvesters to find. Typing A@B.C results in a "A@B.C" link, with grahic representations of "@"and "."; html anchor links with href="mailto:" are obfuscated; the special occurrence "[EMAIL Name | A@B.C]"  is recognized and results in an obfuscated link on "Name".  Without JavaScript, hovering pops up the email with graphic glyphs for "@" and ".".  (Based on eMob Email Obfuscator 1.1 by Billy Halsey.)
Version: 2.0
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

// Prevent direct call to this php file
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }


define('EMOBA_PLUGIN_URL', plugin_dir_url(__FILE__));

/****
Place the css and js in the head:
****/
function emoba_includes(){
  wp_enqueue_style( 'emoba_style', plugin_dir_url(__FILE__) . 'emoba_style.css');
  wp_enqueue_script('emoba_script', plugin_dir_url(__FILE__) . 'emoba_script.js');
}
add_action('init','emoba_includes');


/**** OPTIONS
If CLICKPOP is true, hovering over the link "addr" changes it to "Click to email addr".

If GLYPHS is true, glyphs will be used, text otherwise, for replacing @ and . in displayed emails.

If BARE_TO_LINK is true, bare emails (a@b.c) will be converted to a link.  If false, the email will appear in the glyph form, but there will be no link.

If LEGACY is true, the old "simple" form `[Name] A@B.C` will be converted to an email link. This can be turned off to avoid problems with WordPress shortcuts, in which case the email will be treated as bare, preceded by [Name].
Regardless of the value of LEGACY, the new form `[EMAIL Name | A@B.C]` will be properly converted.
****/

// Check do we have options in DB. Write defaults if not.

$emoba_options = get_option('emoba');

if($emoba_options == false)	{

	// Create array of default settings
	$emoba_options = array(
		'clickpop'		=>	0,
		'glyphs'			=>	1,
		'baretolink'	=>	1,
		'legacy'			=>	0,
		'at-char'			=>	'&copy;',
		'dot-char'		=>	'&bull;'
	);
	update_option('emoba',$emoba_options);
}

/** Use this to aid in version upgrade with added options -- none right now
}else{
	// Add missing options to DB
	if(isset($emoba_options[''])==false) // Do we have it in DB?	{
		// Setup & add options to DB
		$emoba_options[''] = ;
	}
	// Add new fields to DB
	update_option('emoba',$emoba_options);
}
**/

if ( is_admin() ) {

	add_action('admin_init', 'emoba_init' );
	add_action('admin_menu', 'emoba_add_menu');

	// Init plugin options to white list our options
	function emoba_init(){
		register_setting( 'emoba_options', 'emoba', 'emoba_validate' );
	}

	// Add menu page
	function emoba_add_menu() {
		add_options_page('emObA', 'emObA', 'manage_options', 'emoba', 'emoba_do_menu');
	}

	// Draw the menu page itself
	function emoba_do_menu() {
?>

		<div class="wrap">
			<h2>emObA &ndash; Email Obfuscator Advanced</h2>

			<form method="post" action="options.php">
				<?php settings_fields('emoba_options'); ?>
				<?php $options = get_option('emoba'); ?>
				<h3> Options</h3>
				<table class="emoba-form-table">
					<tr valign="top"><th scope="row">Glyphs</th>
						<td><input name="emoba[glyphs]" type="checkbox" value="1" <?php checked('1', $options['glyphs']); ?> /></td>
						<td style="width:80%;">If checked, glyphs will be used for replacing <code>@</code> and <code>.</code> in displayed emails. If not checked, text will be used instead.</td>
					</tr>
					<tr valign="top">
						<th scope="row">ClickPop</th>
						<td><input name="emoba[clickpop]" type="checkbox" value="1" <?php checked('1', $options['clickpop']); ?> /></td>
						<td style="width:80%;">If checked, hovering over the link <code>addr</code> changes it to <code>Click to email addr</code>.</td>
					</tr>
					<tr valign="top"><th scope="row">Bare with link</th>
						<td><input name="emoba[baretolink]" type="checkbox" value="1" <?php checked('1', $options['baretolink']); ?> /></td>
						<td style="width:80%;">If checked, a bare email (<code>a@b.c</code>) will be an active email link.  If not checked, the email will appear in the glyph form, but there will be no link.</td>
					</tr>
					<tr valign="top"><th scope="row">Legacy mode</th>
						<td><input name="emoba[legacy]" type="checkbox" value="1" <?php checked('1', $options['legacy']); ?> /></td>
						<td style="width:80%;">If <code>Legacy mode</code> is checked, the old "simple" form <code>[Name] A@B.C</code> will be converted to an email link. This can be turned off to avoid problems with WordPress shortcuts, in which case the email will be treated as bare, preceded by <code>[Name].</code> Whether or not <code>Legacy mode</code> is checked, the new form <code>[EMAIL Name | A@B.C]</code> will be properly converted.</td>
					</tr>
					<tr valign="top"><th scope="row">At-Char</th>
						<td><input type="text" name="emoba[at-char]" value="<?php echo $options['at-char']; ?>" /></td>
						<td style="width:80%;">Character to substitute for <code>@</code> when not using glyphs (default: &amp;copy;).</td>
					</tr>
					<tr valign="top"><th scope="row">Dot-Char</th>
						<td><input type="text" name="emoba[dot-char]" value="<?php echo $options['dot-char']; ?>" /></td>
						<td style="width:80%;">Character to substitute for <code>.</code> when not using glyphs (default: &amp;bull;).</td>
					</tr>
				</table>
				<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>

			<p>	<b>emObA</b> effectively and automatically makes it difficult for spambots to harvest email addresses from your Wordpress-powered blog. Email addresses may be placed in posts, comments, and pages, as html links, in a special &ldquo;easy email&rdquo; form, or as the address itself, and they will be protected by emObA automatically. All email addresses appearing on your blog will appear on the screen (if JavaScript is enabled) as active links to normal, valid, and correct email addresses, but to spambots they will have no recognizable features.  (As usual, the actual email appears in the status bar when hovering.)</p>

			<p>The email addresses occur in the HTML source only in a well-hidden encoding.  The email address is converted to hexadecimal and appears only as the value of a JavaScript variable.  That encoded email is separated in the JavaScript from the telltale <code>mailto:</code> to further confuse spambots.</p>

			<p>emObA recognizes, and produces obfuscated active (click-to-send) email links for</p>

			<ul style="list-style-type:circle;list-style-position:inside;margin-left:2em;">
				<li>standard email links (<code>&lt;a href="mailto:you@example.com"&gt;Real Name&lt;/a&gt;</code>), allowing class and style attributes (but ignoring other attributes), and allowing an email Subject using the syntax <code>mailto:you@example.com?subject=...</code>.</li>

				<li>the special "easy to write" form  <code>[EMAIL Real Name | you@example.com]</code>, also allowing the <code>?subject=... ]</code> syntax.  (The much more fragile <code>[Real Name] you@example.com</code> of earlier emObA versions remains available if <code>legacy</code> is chosen.)</li>

				<li>a bare email address <code>you@example.com</code>, with or without <code>mailto:</code> in front of it. (<code>?subject=</code> syntax not allowed here.)</li>
			</ul>

			<p>These will all appear as active email links displaying "Real Name". In the cases of a bare email link (one which has no Real Name) or a link in which the Real Name is the email itself, the link will show as the email displayed in human-readable form, eg <code>you [@] example [.] com</code>, where the [@] and [.] are either text symbols or graphic images (as set in administration), hiding the email addresses from spambots.</p>

			<p>If JavaScript is not enabled, the email will appear in obfuscated but human-readable form but the link will not be active.</p>

			<p>I believe any legitimate email will be recognized.  However, no attempt at validation is made -- certain illegally formed addresses will also be recognized, for example, ones containing two successive .'s. (Note: Legal characters before the @ are <code>!#$%&amp;'*+/=?^_{|}~- and `</code>.)</p>

			<p>I've designed this plug-in with "real name" emails in mind -- <code>&lt;a href="mailto:you@example.com"&gt;Real Name&lt;/a&gt;</code> or <code>[EMAIL Real Name | you@example.com]</code>, which display as <code>Real Name</code>.  This will follow whatever styling you apply to your text and to links.  However, if you primarily obfuscate lists of bare email addresses -- <code>you@example.com</code> -- you may not be satisfied with the appearance.  They will appear with either glyphs or specified text symbols in place of @ and . .  The color and weights of the glyphs are fixed (though they do change size with surrounding text), and they don't look exactly like the font symbols they replace. And if text symbols are used, they certainly don't look exactly like @ and ..</p>

			<p>&nbsp;</p>



		</div>


<?php
	}

	// Sanitize and validate input. Accepts an array, return a sanitized array.
	function emoba_validate($input) {
		$input['clickpop'] 		=		( $input['clickpop'] == 1 ? 1 : 0 );
		$input['glyphs'] 			=		( $input['glyphs'] == 1 ? 1 : 0 );
		$input['baretolink']	=		( $input['baretolink'] == 1 ? 1 : 0 );
		$input['legacy'] 			=		( $input['legacy'] == 1 ? 1 : 0 );
		$input['at-char']			=		wp_filter_nohtml_kses($input['at-char']);
		$input['dot-char']		=		wp_filter_nohtml_kses($input['dot-char']);

		return $input;
	}


}else{ /****** NOT ADMIN **************************************************************************/


	/****
	Here we designate the symbols used for at/dot separators in the displayed email addresses.
	You may want to change the alts or i18n them.
	****/

	if (true == $emoba_options['glyphs']) {
		define("AT_SYMBOL", "<img src=\"".EMOBA_PLUGIN_URL."at-glyph.gif\" alt=\"at\"  class=\"emoba-glyph\" />" );
		define("DOT_SYMBOL","<img src=\"".EMOBA_PLUGIN_URL."dot-glyph.gif\" alt=\"dot\" class=\"emoba-glyph\" />" );
	}else{
		define("AT_SYMBOL",  $emoba_options['at-char']);
		define("DOT_SYMBOL", $emoba_options['dot-char']);
	}


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
		$addr .= '>&nbsp;&nbsp;(';
		$addr .= $transformed_email . ')&nbsp;&nbsp;</span></span>';
		return $addr;
	}


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
	function emoba_addJScript($email, $ename, $id, $estyle=null, $eclass=null) {
		global $emoba_options;
		$link   = emoba_hexify_mailto($email);
		$clean_name = str_replace("<", "&lt;", $ename);
		$emoba_js = "<script type=\"text/javascript\">emobascript('".$link."','".$clean_name."','".$id."','".$estyle."','".$eclass."','".$emoba_options['clickpop']."'); </script>";
		return $emoba_js;
	}


	/****
	This is the RE expression for detecting email addresses.
	****/
	define( "EMAILADDR", "([^,;<>\@\][\001-\040\200-\377]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,6})" );
		// Not necessarily legal e-address: Allows \t,\n, etc and two successive .'s


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
		global $emoba_options;

	// (1) convert full email link <a  href="mailto:A@B.C?subject=sss" >Name</a>

		$content = preg_replace_callback(
			'`<a([^>]*)href="mailto:' .EMAILADDR. '([?][^"]*)?"([^>]*)>([^<]*)</a>`i',
			create_function(
				'$match',
				'$other_atts = $match[1].$match[4];
				$em_class = (preg_match(\'!class="([^"]*)"!i\',$other_atts,$match_class)==0)?null:$match_class[1]." ";
				$em_style = (preg_match(\'!style=\"([^"]*)\"!i\',$other_atts,$match_style)==0)?null:$match_style[1];
				$em_email = $match[2].$match[3];
				$em_name = emoba_symb_email($match[5]);
				$id = "emoba-" . rand(1000, 9999);
				$repaddr = "<span id=\"$id\">";
				$repaddr .= emoba_readable_email($em_email, $em_name) . "</span>";
				$repaddr .= emoba_addJScript($em_email, $em_name, $id, $em_class, $em_style);
				return $repaddr;' ),
			$content );


	//  (1a) We can now remove mailto:'s from any remaining  mailto:A@B.C
	//  (This won't affect the full links just processed, since they no longer contain the string mailto:A@B.C)

		$content = preg_replace("`mailto:".EMAILADDR."`i", '$1', $content);


	// (2) Convert the special pattern [EMAIL Name | A@B.C] to email link <a href="mailto:A@B.C >Name</a>
	//     Allows any number of spaces at each position within [EMAIL|]
		$content = preg_replace_callback(
			'`\[EMAIL([^|]+)(?:(?:[\s]|&nbsp;)*[|](?:[\s]|&nbsp;)*)'.EMAILADDR.'([?][^]]*?)?(?:[ ]|&nbsp;)*]`',
			create_function(
				'$match',
				'$em_email = $match[2].$match[3];
				$em_name = emoba_symb_email(trim($match[1]));
				$id = "emoba-" . rand(1000, 9999);
				$repaddr = "<span id=\"$id\">";
				$repaddr .= emoba_readable_email($em_email, $em_name). "</span>";
				$repaddr .= emoba_addJScript($em_email, $em_name, $id);
				return $repaddr;' ),
			$content );


	if ( 1 == $emoba_options['legacy'] ) {

	// (2') (Legacy) Convert the special pattern [Name] A@B.C to email link <a href="mailto:A@B.C >Name</a>

		$content = preg_replace_callback(
			'`\[([^]]+)\](?:[\s]|&nbsp;)*'.EMAILADDR.'`',
			create_function(
				'$match',
				'$em_email = $match[2];
				$em_name = emoba_symb_email($match[1]);
				$id = "emoba-" . rand(1000, 9999);
				$repaddr = "<span id=\"$id\">";
				$repaddr .= emoba_readable_email($em_email, $em_name). "</span>";
				$repaddr .= emoba_addJScript($em_email, $em_name, $id);
				return $repaddr;' ),
			$content );

	}


	if ( 1 == $emoba_options['baretolink'] ) {

	// (3) Convert bare email addresses A@B.C to the link <a href="mailto:A@B.C">A B C</a>

		$content = preg_replace_callback(
			'`'.EMAILADDR.'`',
			create_function(
				'$match',
				'$em_email = $match[1];
				$em_name = emoba_symb_email($em_email);
				$id = "emoba-" . rand(1000, 9999);
				$repaddr = "<span id=\"$id\">";
				$repaddr .= emoba_symb_email($em_email) . "</span>";
				$repaddr .= emoba_addJScript($em_email, $em_name, $id);
				return $repaddr;' ),
			$content );

	}else{

	// (3) Convert any remaining addresses A@B.C
		$content = preg_replace_callback(
			'`'.EMAILADDR.'`',
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


}//not admin
