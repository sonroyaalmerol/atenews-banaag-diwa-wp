<?php 
/**
 * @package  BanaagDiwaSubmissions
 */
namespace Inc\Pages;

/**
* 
*/
class Admin {
	public function register() {
		add_action('admin_menu', array($this, 'add_admin_pages'));
		add_action('admin_menu', function() {
			if(!current_user_can('editor') && !current_user_can('administrator')) {
				remove_menu_page('edit.php?post_type=photo_essay_sub');
				remove_menu_page('edit.php?post_type=poem_sub');
				remove_menu_page('edit.php?post_type=short_story_sub');
			}
		});
		add_action('admin_init', function() {
			register_setting('banaag-diwa-submissions-settings', 'banaag_diwa_submissions_state', array('default' => 'open'));
			register_setting('banaag-diwa-submissions-settings', 'banaag_diwa_submissions_delete', array('default' => 'reject'));
		});
	}

	public function add_admin_pages() {
		add_menu_page('Banaag Diwa Submissions', 'Banaag Diwa Settings', 'manage_options', 'banaag_diwa_submissions', array( $this, 'admin_index' ), '', 110);
	}

	public function admin_index() {
		require_once PLUGIN_PATH . 'templates/admin.php';
	}
}