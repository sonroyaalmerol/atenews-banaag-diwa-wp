<?php

/**
 * Trigger this file on Plugin uninstall
 *
 * @package  BanaagDiwaSubmissions
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}

delete_option('banaag_diwa_submissions_state');

// Clear Database stored data
$submissions = get_posts(array('post_type' => 'pandemya_submissions', 'numberposts' => -1));
$submissions = array_merge($submissions, get_posts(array('post_type' => 'photo_essay_sub', 'numberposts' => -1)));
$submissions = array_merge($submissions, get_posts(array('post_type' => 'poem_sub', 'numberposts' => -1)));
$submissions = array_merge($submissions, get_posts(array('post_type' => 'short_story_sub', 'numberposts' => -1)));

foreach($submissions as $submission) {
	wp_delete_post($submission->ID, true);
}