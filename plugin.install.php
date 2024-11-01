<?php
/**
* "Batch Links" WordPress Plugin
*
* This is plugin installation routine. It has been put in a separate file
* in order to make the core functionality lighter and not burden it with
* the admin functionality when not necessary.
*
* @version $Id: plugin.install.php 36395 2008-03-27 10:17:41Z Mrasnika $
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @link http://kaloyan.info/blog/wp-batch-links
*/

/////////////////////////////////////////////////////////////////////////////

/**
* @internal prevent from direct calls
*/
if (!defined('ABSPATH')) {
	exit ;
	}

/////////////////////////////////////////////////////////////////////////////

	// settings ...
	//
	$blinks_settings = array(
		);

	update_option(
		'blinks_settings',
		array_merge(
			(array) get_option('blinks_settings'),
			$blinks_settings
			)
		);
	
	// version
	//
	update_option(
		'blinks_version',
		wp_blinks::version()
		);

/////////////////////////////////////////////////////////////////////////////

?>