<?php

namespace Inc\CPT;

class Poem {
  public function register() {
    add_action('init', function() {
      register_post_type('poem_sub', array(
        'labels' => array(
          'name' => __('Poem Submissions'),
          'singular_name' => __('Poem Submission'),
          'edit_item' => 'View Submission'
        ),
        'public' => true,
        'show_ui' => true,
        'rewrite' => array(
          'slug' => 'poem_subs'
        ),
        'supports' => array(
          'title'
        ),
        'menu_icon' => 'dashicons-feedback',
        'show_in_rest' => true,
        'capability_type' => 'post',
        'capabilities' => array(
          'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
        ),
        'map_meta_cap' => true,
      ));
    });

    add_action('add_meta_boxes', function() {
      add_meta_box('submitter_info', __('Personal Information'), function ($post) {
        $name = get_post_meta($post->ID, '_submitter_name', true);
        $email = get_post_meta($post->ID, '_submitter_email', true);
        $year = get_post_meta($post->ID, '_submitter_year', true);
        $course = get_post_meta($post->ID, '_submitter_course', true);
        ?>
        <p><b>Name</b>: <?php echo $name ?></p>
        <p><b>Email Address</b>: <?php echo $email ?></p>
        <p><b>Year Level</b>: <?php echo $year ?></p>
        <p><b>Course</b>: <?php echo $course ?></p>
        <?php
      }, 'poem_sub', 'normal', 'high');

      add_meta_box('doc_submission', __('Document Submission'), function ($post) {
        $value = get_post_meta($post->ID, '_doc_submission', true);
        ?>
          <button class="download-all-submission download-button-submission" name="download_button" id="download_button" type="button" onclick="window.open('<?php echo wp_get_attachment_url($value) ?>', '_blank');"><span>Download document </span></button>
        <?php
      }, 'poem_sub', 'normal');
    });

    /**
     * Removes the "Trash" link on the individual post's "actions" row on the posts
     * edit page.
     */
    $reject_delete = 'reject' == get_option('banaag_diwa_submissions_delete');
    add_filter( 'post_row_actions', function ( $actions, $post ) {
      $reject_delete = 'reject' == get_option('banaag_diwa_submissions_delete');
      if( $post->post_type === 'poem_sub' ) {
        unset($actions['clone']);
        if ($reject_delete) {
          unset($actions['trash']);
        }
        unset($actions['view']);
        unset($actions['edit']);
        unset($actions['inline hide-if-no-js']);
      }
      return $actions;
    }, 10, 2 );

    if ($reject_delete) {
      add_action('wp_trash_post', function ($post_id) {
        if( get_post_type($post_id) === 'poem_sub' ) {
          wp_die('The post you were trying to delete is protected.');
        }
      });
    }

    /**
     * Removes the "Delete" link on the individual term's "actions" row on the terms
     * edit page.
     */

    add_action( 'admin_head', function () {
        $current_screen = get_current_screen();

        // Hides the "Move to Trash" link on the post edit page.
        if ( 'post' === $current_screen->base &&
        'poem_sub' === $current_screen->post_type ) :
        ?>
          <style>
            #postbox-container-1 { 
              display: none; 
            }
            #edit-slug-box {
              display: none;
            }
            input[name=post_title] {
              pointer-events: none;
            }
        </style>
        <?php
        endif;
    } );
  }
}

		