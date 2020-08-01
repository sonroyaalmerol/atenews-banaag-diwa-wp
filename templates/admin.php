<div class="wrap">
  <h1>Banaag Diwa Submissions Settings</h1>
  <hr />
  <form method="post" action="options.php">
    <?php 
      settings_fields('banaag-diwa-submissions-settings');
      do_settings_sections('banaag-diwa-submissions-settings');
      $options_state = get_option('banaag_diwa_submissions_state');
      $options_deletion = get_option('banaag_diwa_submissions_delete');
    ?>
    <h2>Toggle State</h2>
    <input type="radio" id="true" name="banaag_diwa_submissions_state" value="open" <?php echo checked( 'open' == $options_state ); ?>>
    <label for="true">Open submissions</label><br>
    <input type="radio" id="false" name="banaag_diwa_submissions_state" value="close" <?php echo checked( 'close' == $options_state ); ?>>
    <label for="false">Close submissions</label><br><br>
    <h2>Allow deletion of submissions</h2>
    <input type="radio" id="true" name="banaag_diwa_submissions_delete" value="reject" <?php echo checked( 'reject' == $options_deletion ); ?>>
    <label for="true">Reject deletion</label><br>
    <input type="radio" id="false" name="banaag_diwa_submissions_delete" value="allow" <?php echo checked( 'allow' == $options_deletion ); ?>>
    <label for="false">Allow deletion</label><br>
    <?php submit_button(); ?>
  </form>
  <hr />
  <h2>REST API Schema (How to submit?)</h2>
  <b>Endpoint</b>: /wp-json/atenews/v1/banaag_diwa_submit<br/><br/>
  <b>Body parameters</b>:
  <ul>
    <li><u>title</u> - Title of submitted work</li>
    <li><u>name</u> - Name of submitter</li>
    <li><u>email</u> - Email address of submitter</li>
    <li><u>year</u> - Year level of submitter</li>
    <li><u>course</u> - Course of submitter</li>
    <li><u>type</u> - Type of submission [0 - Photo Essays, 1 - Short Stories, 2 - Poems]</li>
    <li><u>document</u> - Document file for all types of submissions</li>
    <li><u>images[]</u> - Array of image files for Photo Essay submissions</li>
  </ul>
</div>