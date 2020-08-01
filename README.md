# Atenews Banaag Diwa Submissions Plugin (WordPress)

This is the repository for the Banaag Diwa Submissions. This project uses pure native WordPress modules. This is built with the Wordpress REST API as POST endpoint for the submissions.

## REST API Endpoint

The REST API Endpoint is located at __/wp-json/atenews/v1/banaag_diwa_submit__.

### Body Parameters
- **title** - (string) Title of submitted work
- **name** - (string) Name of submitter
- **email** - (string) Email Address of submitter
- **year** - (string) Year level of submitter
- **course** - (string) Course of submitter
- **type** - (int) Type of submission
  - 0 - Photo Essays
  - 1 - Short Stories
  - 2 - Poems
- **document** - (file) Document file for all types of submissions
- **images** - (file array) Array of image files for Photo Essay submissions

### Return Parameters
- **success** - (boolean) Boolean value for submission success
- **error** - (string) Description of detected error

## Contributing
Only pull requests from Atenews Web Devs are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
[MIT](https://choosealicense.com/licenses/mit/)