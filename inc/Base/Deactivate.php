<?php
/**
 * @package  BanaagDiwaSubmissions
 */
namespace Inc\Base;

class Deactivate {
	public static function deactivate() {
		delete_option('banaag_diwa_submissions_state');
		flush_rewrite_rules();
	}
}