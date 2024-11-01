<?php
/**
* "Batch Links" WordPress Plugin
*
* This is the core functionality for the plugin. All the stuff not related to 
* its main function to place the batch links is moved to external files: in this 
* way operations like installing the plugin, or its admin pages will not weight on 
* the frontend performance.
*
* @version $Id: plugin.php 36395 2008-03-27 10:17:41Z Mrasnika $
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

/**
* Location of the plugin files
*/
define('BL_HOME', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/////////////////////////////////////////////////////////////////////////////

/**
* @internal prevent more than one include
*/
if (class_exists('wp_blinks')) {

	/**
	* Initialize the cache
	* @see wp_blinks
	*/	
	if (!isset($wp_blinks)) {
		$wp_blinks = new wp_blinks;
		}

	return;
	}

/////////////////////////////////////////////////////////////////////////////

/**
* "Batch Links" WordPress Plugin
*/
Class wp_blinks {

	/**
	* Constructor
	*
	* Attaches the required plugin hooks
	* @access public
	*/
	function wp_blinks() {

		// attach to admin menu
		//
		if (is_admin()) {
			add_action('admin_menu',
				array(&$this, '_menu')
				);
			}

		// attach the handler
		//
		if(!is_feed()) {
			add_filter('the_content',
				array($this, 'blinks'), 2
				);
			add_filter('the_excerpt',
				array($this, 'blinks'), 2
				);
			}

		// attach to plugin installation
		//
		add_action(
			'activate_' . str_replace(
				DIRECTORY_SEPARATOR, '/',
				str_replace(
					realpath(ABSPATH . PLUGINDIR) . DIRECTORY_SEPARATOR,
						'', __FILE__
					)
				),
			array(&$this, '_install')
			);
		}

	/**
	* Performs the routines required at plugin installation
	* @access public
	*/
	function _install() {
		require(
			BL_HOME . 'plugin.install.php'
			);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
	
	/**
	* Attach the menu page to the `Options` tab
	* @access private
	*/
	function _menu() {
		
		add_submenu_page(
			'options-general.php',
			'Batch Links',
			'BL',
			'manage_options',
			'batch-links',
			array($this, 'menu')
			);
		}

	/**
	* Handles and renders the menu page
	* @access public
	*/
	function menu() {
		require(
			BL_HOME . 'plugin.menu.php'
			);
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Do the batch links for this post
	*
	* @param string $content
	* @return string
	*/
	function blinks($content) {
	
		// is this a post ?
		//
		global $post;
		if (!is_object($post)) {
			return $content;
			}
	
		// got the links ?
		//
		static $links;
		if (!isset($links)) {
			$links = (array) get_option('blinks_settings');
			rsort($links);
				// ^
				// starting from the longest
				// strings to the shorter ones
			}

		// inject the links
		//
		foreach($links as $l){

			$preg_text = preg_quote($l['text'], '~');
			$preg_text = preg_replace(
				'~\s+~Uis', '\s+(?:<br\/?>\s*)?', $preg_text
				);
				// ^
				// various whitespace ?
			
			// compose pattern
			//
			//$find = '~' . $preg_text . '~i';
			$find = '~(?:>|\s|;|^)(' . $preg_text . ')(?:>|\s|;|$)~i';

			// do the search
			//
			$matches = array();
			preg_match_all($find, $content, $matches, PREG_OFFSET_CAPTURE);
			$matchData = $matches[1];

			// skip those scenarios
			//
			$skip_patterns = array(
				'~<h[1-6][^>]*>[^<]*' . $preg_text . '[^<]*<\/h[1-6]>~i',
				'~<a[^>]+>[^<]*' . $preg_text . '[^<]*<\/a>~i',
				'~href=("|\')[^"\']+' . $preg_text . '[^"\']+("|\')~i',
				'~src=("|\')[^"\']*' . $preg_text . '[^"\']*("|\')~i',
				'~alt=("|\')[^"\']*' . $preg_text . '[^"\']*("|\')~i',
				'~title=("|\')[^"\']*' . $preg_text . '[^"\']*("|\')~i',
				'~content=("|\')[^"\']*' . $preg_text . '[^"\']*("|\')~i',
				'~<script[^>]*>[^<]*' . $preg_text . '[^<]*<\/script>~i',

				'~<style[^>]*>[^<]*' . $preg_text . '[^<]*<\/style>~i',
				'~<textarea[^>]*>[^<]*' . $preg_text . '[^<]*<\/textarea>~i',
				'~<select[^>]*>[^<]*' . $preg_text . '[^<]*<\/select>~i',
				);

			foreach($skip_patterns as $skip_pattern){

				$results = array();
				preg_match_all($skip_pattern, $content, $results, PREG_OFFSET_CAPTURE);
				$skip = $results[0];
		
				if(count($skip) == 0) {
					continue;
					}
					// ^ 
					// no skip scenarios
		
				foreach($skip as $s){

					// match boundaries
					//
					$offsetMin = $s[1];
					$offsetMax = $s[1] + strlen($s[0]);

					foreach($matchData as $index => $data){
						if($data[1] >= $offsetMin && $data[1] <= $offsetMax){
							unset($matchData[$index]);
							}
							// ^
							// the match is within the boundaries
							// of a skip scenario, so we skip it
						}
					}
				}

			// match found!
			//
			if ($found = array_pop($matchData)) {
				$replacement = '<a href="' . $l['url'] . '">' . $found[0] . '</a>';
				$content = substr($content, 0, $found[1])
					. $replacement
					. substr($content, $found[1] + strlen($found[0]));
				}
			}

		return $content;
		}

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
	
	/**
	* Get the version of the plugin
	* @access public
	*/
	Function version() {
		if (preg_match("~Version\:\s*(.*)\s*~i", file_get_contents(__FILE__), $R)) {
			return trim($R[1]);
			}
		return '$Rev: 36395 $';
		}
	
	//--end-of-class--
	}

/////////////////////////////////////////////////////////////////////////////

/*
Plugin Name: Batch Links
Plugin URI: http://kaloyan.info/blog/wp-batch-links
Description: Adds links to posts in a SEO manner, so you can use your WordPress-powered blog to boost the SEO rankings of other websites (<a href="options-general.php?page=batch-links">click here to open the configuration page</a>).
Version: 0.1
Author: Kaloyan K. Tsvetkov
Author URI: http://kaloyan.info/
*/

?>