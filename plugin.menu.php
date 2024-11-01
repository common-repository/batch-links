<?php
/**
* "Batch Links" WordPress Plugin
*
* This is the menu page, which shows up under the "Options" tab in the WordPress 
* admin area. It has been put in a separate file in order to make the core 
* functionality lighter and not burden it with the admin functionality when not 
* necessary.
*
* @version $Id: plugin.menu.php 36395 2008-03-27 10:17:41Z Mrasnika $
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @link http://kaloyan.info/blog/wp-batch-links
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @internal prevent from direct calls
*/
if (!defined('ABSPATH')) {
	exit;
	}

/////////////////////////////////////////////////////////////////////////////

	// sanitize referrer
	//
	$_SERVER['HTTP_REFERER'] = preg_replace(
		'~&saved=.*$~Uis','', $_SERVER['HTTP_REFERER']
		);
	
	// information updated ?
	//
	if ($_POST['submit']) {
		
		$_ = $_POST['blinks_settings'];

		$L = array();
		foreach ($_ as $l) {

			$l['text'] = stripCSlashes(trim($l['text']));
			$l['url'] = stripCSlashes(trim($l['url']));

			if (!$l['text'] || !$l['url']) {
				continue;
				}

			$L[] = $l;
			}
		$_ = $L;
		
		// save
		//
		update_option(
			'blinks_settings',
			$_
			);

		die("<script>document.location.href = '{$_SERVER['HTTP_REFERER']}&saved=settings:" . time() . "';</script>");
		}

	// operation report detected
	//
	if (@$_GET['saved']) {
		
		list($saved, $ts) = explode(':', $_GET['saved']);
		if (time() - $ts < 10) {
			echo '<div class="updated"><p>';

			switch ($saved) {
				case 'settings' :
					echo 'Settings saved.';
					break;
				}

			echo '</p></div>';
			}
		}

	// read the settings
	//
	$blinks_settings = (array) get_option('blinks_settings');

?>
<div class="wrap">
	<h2>Batch Links</h2>
	<p></p>
	<form method="post">
	<fieldset class="options">

<?php $i=0; foreach($blinks_settings as $l) { ?>

	<b>Link #<?php printf('%03d', $i + 1); ?></b>
	
	Text:
	<input name="blinks_settings[<?php echo $i; ?>][text]" value="<?php echo $l['text']; ?>" />

	URL:
	<input name="blinks_settings[<?php echo $i; ?>][url]" size="52" value="<?php echo $l['url']; ?>" />
	
	<a href="<?php echo $l['url']; ?>" target="_new">visit</a>
	<br/>

<?php $i++; } ?>

	<b>Link #<?php printf('%03d', $i + 1); ?></b>
	
	Text:
	<input name="blinks_settings[<?php echo $i; ?>][text]" value="" />

	URL:
	<input name="blinks_settings[<?php echo $i; ?>][url]" size="52" value="" /><br/>


		<p class="submit" style="text-align:left;"><input type="submit" name="submit" value="Save &raquo;" /></p>
	</fieldset>
	</form>
</div>

<div class="wrap">
	<h2>About</h2>
	<p style="padding-right:50px;">
		You can find more information about this plugin and how to use it at its <a
		href="http://kaloyan.info/blog/wp-batch-links">homepage</a>.
		There you can post any questions or requests that you have, or you can visit the
		"Batch Links" page at the <a
		href="http://wordpress.org/extend/plugins/batch-links/">official WordPress plugin repository</a>.
	</p>
	<p><strong>
		If you are glad with the good work I've done and you want to buy me a beer or a coffee, <a
		href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&currency_code=USD&amount=1&item_name=I%20am%20buying%20Kaloyan%20a%20beer%20or%20a%20coffee%20for%20the%20good%20work%20on%20the%20%22Batch%20Links%22%20WordPress%20plugin&business=kaloyan@kaloyan.info&return=http://kaloyan.info/blog/wp-batch-links/%3Fthankyou=paypal">click
		here</a>! </strong><br/>
	<small>(when clicking on the link you will be redirected to Paypal)</small>
	</p>
	
</div>