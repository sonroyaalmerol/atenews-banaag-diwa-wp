<?php

namespace Inc\API;

class Submissions {
  public function register() {
    add_action('rest_api_init', function() {
			register_rest_route('atenews/v1', 'banaag_diwa_submit', [
				'methods' => 'POST',
        'callback' => array($this, 'addSubmission')
      ]);
    });
  }

  public function addSubmission(\WP_REST_Request $request) {
    /**
     * 
     * Types:
     * 0 - Photo Essays
     * 1 - Short Stories
     * 2 - Poems
     * 
     */
    require_once \PLUGIN_PATH . 'vendor/php-zip/src/zip.php';
    $zip = new \Zip();
    $isOpen = 'open' == get_option('banaag_diwa_submissions_state');

    if (!$isOpen) {
      return rest_ensure_response(['success' => false, 'error' => 'Submissions are closed.']);
    }
    // if you sent any parameters along with the request, you can access them like so:
    $wordpress_upload_dir = wp_upload_dir();
    $title = $request->get_param('title');
    $name = $request->get_param('name');
    $email = $request->get_param('email');
    $year = $request->get_param('year');
    $course = $request->get_param('course');
    $type = $request->get_param('type');
    if (is_numeric($type)) {
      $type = $type + 0;
    } else {
      $type = null;
    }

    $post_type = '';
    switch($type) {
      case 0:
        $post_type = 'photo_essay_sub';
        break;
      case 1:
        $post_type = 'short_story_sub';
        break;
      case 2:
        $post_type = 'poem_sub';
        break;
    }

    $submission_data = array(
      'post_title' => $title,
      'post_type' => $post_type,
      'post_status' => 'publish'
    );

    $submission_id = wp_insert_post($submission_data);


    $permittedExtension = ['docx', 'doc', 'txt'];
    $permittedTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];

    $imageTypes = ['image/jpeg', 'image/pipeg', 'image/png'];

    $files = $request->get_file_params();
    $headers = $request->get_headers();
    $doc = null;
    $images = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return rest_ensure_response(['success' => false, 'error' => 'Invalid email!']);
    }

    if ( !empty( $files ) && !empty( $files['document'] ) ) {
      $doc = $files['document'];
      if (!empty($files['images'])) {
        $images = $files['images'];
      }
    }

    if (!$title || !$name || !$email || !$year || !$course || $type === null) {
      return rest_ensure_response(['success' => false, 'error' => 'Title/Name/Email/Year/Course is required.']);
    }

    if ($type < 0 || $type > 2) {
      return rest_ensure_response(['success' => false, 'error' => 'Invalid submission type.']);
    }

    /**
     * 
     * Doc/Docx validation
     * 
     */

    // smoke/sanity check
    if (! $doc ) {
      return rest_ensure_response(['success' => false, 'error' => 'No file attached.']);
    }
    // confirm file uploaded via POST
    if (! is_uploaded_file( $doc['tmp_name'] ) ) {
      return rest_ensure_response(['success' => false, 'error' => 'No file uploaded.']);
    }
    // confirm no file errors
    if (! $doc['error'] === UPLOAD_ERR_OK ) {
      return rest_ensure_response(['success' => false, 'error' => 'Uploading failed.']);
    }
    // confirm extension meets requirements
    $ext = pathinfo( $doc['name'], PATHINFO_EXTENSION );
    if ( !in_array($ext, $permittedExtension) ) {
      return rest_ensure_response(['success' => false, 'error' => 'Not a doc/docx file.']);
    }
    // check type
    $mimeType = mime_content_type($doc['tmp_name']);
    if ( !in_array( $doc['type'], $permittedTypes )
        || !in_array( $mimeType, $permittedTypes ) ) {
          return rest_ensure_response(['success' => false, 'error' => 'Not a doc/docx file.', 'mime' => $mimeType]);
    }

    if ($type == 0 && count($images) == 0) {
      return rest_ensure_response(['success' => false, 'error' => 'Images are required for Photo Essays.']);
    }

    /**
     * 
     * Image validation
     * 
     */
    if ($type == 0) {
      $images_mime = [];
      if (array_key_exists('tmp_name', $images)) {
        foreach ($images['tmp_name'] as $key => $image) {
          $images_mime[$key] = mime_content_type($image);
          if (!in_array($images_mime[$key], $imageTypes)) {
            return rest_ensure_response(['success' => false, 'error' => 'Not all images are JPEG.', 'mime' => $images_mime[$key]]);
          }
        }
      }
    }
    /**
     * 
     * Uploading to database
     * 
     */

    require_once( \ABSPATH . 'wp-admin/includes/image.php' );

    $image_ids = [];
    $doc_id = null;

    $new_file_path = $wordpress_upload_dir['path'] . '/' . $doc['name'];
    $i = 1;
    while(file_exists($new_file_path)) {
      $i++;
      $new_file_path = $wordpress_upload_dir['path'] . '/' . $i . '_' . $doc['name'];
    }
    
    if (move_uploaded_file($doc['tmp_name'], $new_file_path)) {
      $doc_id = wp_insert_attachment(array(
        'guid' => $new_file_path,
        'post_mime_type' => $mimeType,
        'post_title' => preg_replace( '/\.[^.]+$/', '', $doc['name'] ),
        'post_content' => '',
        'post_status' => 'inherit'
      ), $new_file_path, $submission_id);

      // Generate and save the attachment metas into the database
      wp_update_attachment_metadata( $doc_id, wp_generate_attachment_metadata( $doc_id, $new_file_path ) );
    }

    if ($type == 0) {
      $images_file_path = [];
      if (array_key_exists('name', $images)) {
        foreach ($images['name'] as $key => $image) {
          $images_file_path[$key] = $wordpress_upload_dir['path'] . '/' . $image;
          $tmp_file_path = $images_file_path[$key];
          $i = 1;
          while(file_exists($tmp_file_path)) {
            $i++;
            $images_file_path[$key] = $wordpress_upload_dir['path'] . '/' . $i . '_' . $image;
            $tmp_file_path = $images_file_path[$key];
          }
        }
      }

      if (array_key_exists('tmp_name', $images)) {
        foreach ($images['tmp_name'] as $key => $image) {
          if (move_uploaded_file($image, $images_file_path[$key])) {
            $image_ids[$key] = wp_insert_attachment(array(
              'guid' => $images_file_path[$key],
              'post_mime_type' => $images_mime[$key],
              'post_title' => preg_replace( '/\.[^.]+$/', '', $images['name'][$key] ),
              'post_content' => '',
              'post_status' => 'inherit'
            ), $images_file_path[$key], $submission_id);
      
            // Generate and save the attachment metas into the database
            wp_update_attachment_metadata( $image_ids[$key], wp_generate_attachment_metadata( $image_ids[$key], $images_file_path[$key] ) );
            
          }
        }
      }

      $zipname_images = $wordpress_upload_dir['path'] . '/' . $title . '_' . $name . '_images.zip';
      $zip->zip_start($zipname_images);
      $zip->zip_add($images_file_path);
      $zip->zip_end();

      $zip_id = wp_insert_attachment(array(
        'guid' => $zipname_images,
        'post_mime_type' => 'application/zip',
        'post_title' => preg_replace( '/\.[^.]+$/', '', $title . '_' . $name . '_images.zip'),
        'post_content' => '',
        'post_status' => 'inherit'
      ), $zipname_images, $submission_id);

      // Generate and save the attachment metas into the database
      wp_update_attachment_metadata($zip_id, wp_generate_attachment_metadata( $zip_id, $zipname_images ));

      $all_files = $images_file_path;
      array_push($all_files, $new_file_path);
      $zipname_all = $wordpress_upload_dir['path'] . '/' . $title . '_' . $name . '.zip';
      $zip->zip_start($zipname_all);
      $zip->zip_add($all_files);
      $zip->zip_end();

      $zip_id_all = wp_insert_attachment(array(
        'guid' => $zipname_all,
        'post_mime_type' => 'application/zip',
        'post_title' => preg_replace( '/\.[^.]+$/', '', $title . '_' . $name . '.zip'),
        'post_content' => '',
        'post_status' => 'inherit'
      ), $zipname_all, $submission_id);

      // Generate and save the attachment metas into the database
      wp_update_attachment_metadata($zip_id_all, wp_generate_attachment_metadata( $zip_id_all, $zipname_all ));
      //*/
    }
    
    update_post_meta($submission_id, '_submitter_name', $name);
    update_post_meta($submission_id, '_submitter_email', $email);
    update_post_meta($submission_id, '_submitter_year', $year);
    update_post_meta($submission_id, '_submitter_course', $course);
    update_post_meta($submission_id, '_doc_submission', $doc_id);

    if ($type == 0) {
      update_post_meta($submission_id, '_images_submission', $image_ids);
      update_post_meta($submission_id, '_images_submission_zip', $zip_id);
      update_post_meta($submission_id, '_all_submission_zip', $zip_id_all);
    }

    // return any necessary data in the response here
    return rest_ensure_response( ['success' => true, 'title' => $title, 'doc_id' => $doc_id] );
  }
}