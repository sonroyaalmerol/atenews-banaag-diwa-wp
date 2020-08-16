<?php

namespace Inc\CPT;

class ShortStory {
  public function register() {
    add_action('init', function() {
      register_post_type('short_story_sub', array(
        'labels' => array(
          'name' => __('Short Story Submissions'),
          'singular_name' => __('Short Story Submission'),
          'edit_item' => 'View Submission'
        ),
        'public' => true,
        'show_ui' => true,
        'rewrite' => array(
          'slug' => 'short_story_subs'
        ),
        'supports' => array(
          'title'
        ),
        'menu_icon' => 'data:image/svg+xml;base64,' . base64_encode('
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
            viewBox="0 0 88 88" style="enable-background:new 0 0 88 88;" xml:space="preserve">
            <style type="text/css">
              .st0{fill:#9EA3A8;}
              .st1{enable-background:new    ;}
            </style>
            <g id="Layer_2_1_">
            <g id="Layer_2-2">
              <path class="st0" d="M75,13v62H13V13H75 M88,0H0v88h88V0z"/>
            </g>
            <g id="Layer_3">
              <g class="st1">
                <path class="st0" d="M21.4,54.8l1.4-0.3c1-0.2,1.3-0.4,1.3-2.4v-17c0-2-0.3-2.2-1.3-2.4l-1.4-0.3v-1.3h10.9c6,0,9.4,1.7,9.4,5.9
                  c0,3.3-2.3,5-6.5,5.7V43c5.1,0.5,7.7,2.3,7.7,6.2c0,4.6-3.5,7-9.7,7H21.4V54.8z M32,42.2c3.1,0,4.5-1.4,4.5-4.5
                  c0-3.9-1.4-4.8-4.5-4.8c-1,0-2,0.1-2.5,0.2v9.1H32z M37.4,49.4c0-4-1.4-5.3-5-5.3h-2.9v7.8c0,2.2,0.4,2.6,3.3,2.6
                  C35.6,54.4,37.4,53.3,37.4,49.4z"/>
                <path class="st0" d="M42.3,54.8l1.4-0.3c1-0.2,1.3-0.4,1.3-2.4v-17c0-2-0.3-2.2-1.3-2.4l-1.4-0.3v-1.3H54
                  c7.3,0,12.6,3.3,12.6,12.1c0,8.4-5.1,12.8-12.8,12.8H42.3V54.8z M61,44.1c0-8.2-2.3-11.2-7.6-11.2c-1.5,0-2.5,0.1-3,0.3V51
                  c0,2.9,0.3,3.4,3.1,3.4C59.1,54.4,61,50.7,61,44.1z"/>
              </g>
            </g>
          </g>
        </svg>'),
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
      }, 'short_story_sub', 'normal', 'high');

      add_meta_box('doc_submission', __('Document Submission'), function ($post) {
        $value = get_post_meta($post->ID, '_doc_submission', true);
        ?>
          <button class="download-all-submission download-button-submission" name="download_button" id="download_button" type="button" onclick="window.open('<?php echo wp_get_attachment_url($value) ?>', '_blank');"><span>Download document </span></button>
        <?php
      }, 'short_story_sub', 'normal');
    });

    /**
     * Removes the "Trash" link on the individual post's "actions" row on the posts
     * edit page.
     */
    $reject_delete = 'reject' == get_option('banaag_diwa_submissions_delete');
    add_filter( 'post_row_actions', function ( $actions, $post ) {
      $reject_delete = 'reject' == get_option('banaag_diwa_submissions_delete');
      if( $post->post_type === 'short_story_sub' ) {
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
        if( get_post_type($post_id) === 'short_story_sub' ) {
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
      'short_story_sub' === $current_screen->post_type ) :
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

    add_action( 'before_delete_post', function( $id ) {
      if (get_post_type($id) === 'short_story_sub') {
        $attachments = get_attached_media( '', $id );
        foreach ($attachments as $attachment) {
          wp_delete_attachment( $attachment->ID, 'true' );
        }
      }
    } );

    add_action('trashed_post', function ($post_id) {
      if (get_post_type($post_id) === 'short_story_sub') {
        // Force delete
        wp_delete_post( $post_id, true );
      }
    });
  }
}

		