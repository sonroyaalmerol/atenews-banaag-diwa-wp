<?php 
/**
 * @package  BanaagDiwaSubmissions
 */
namespace Inc\Base;

/**
* 
*/
class Enqueue {
	public function register() {
		add_action('admin_enqueue_scripts', array($this, 'enqueue'));
	}
	
	function enqueue() {
		// enqueue all our scripts
		wp_enqueue_style('banaag_diwa_submissions_style', PLUGIN_URL . 'assets/submissions_style.css');
		wp_enqueue_script('banaag_diwa_submissions_script', PLUGIN_URL . 'assets/submissions_script.js');
	}
}