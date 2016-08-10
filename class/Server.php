<?php
/**
 * @copyright (c) 2015, Martijn van der Ven  <martijn@zegnat.net>
 * @copyright (c) 2016, Josef Kufner  <josef@kufner.cz>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 */

namespace PhotoBackup;

/**
 * Server implementation
 *
 * Configuration options (see member variables):
 *   - password
 *   - mediaRoot
 *
 */
class Server
{

	/**
	 * The password required to upload to this server.
	 *
	 * The password is currently stored as clear text here. PHP code is not normally
	 * readable by third-parties so this should be safe enough. Many applications
	 * store database credentials in this way as well. A secondary and safer way is
	 * being considered for the next version.
	 *
	 * @var string $password A string (encapsulated by quotes).
	 */
	protected $password;


	/**
	 * The directory where files should be uploaded to.
	 *
	 * This directory path is relative to this index.php file and should not end
	 * with a /. It should point to an existing directory on the server.
	 *
	 * @var string $mediaRoot A string (encapsulated by quotes).
	 */
	protected $mediaRoot;


	/**
	 * Simple entry point.
	 */
	public static function main($configuration)
	{
		// Throw exceptions on all errors
		set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
			if (error_reporting()) {
				throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
			}
		});

		$app = new self($configuration);
		return $app->handleRequest();
	}


	/**
	 * Constructor.
	 *
	 * Load configuration, otherwise do nothing.
	 */
	public function __construct($configuration)
	{
		if (empty($configuration['password']) || !is_string($configuration['password'])) {
			throw new \InvalidArgumentException('Password not set.');
		} else {
			$this->password = $configuration['password'];
		}

		if (empty($configuration['mediaRoot']) || !is_string($configuration['mediaRoot'])) {
			throw new \InvalidArgumentException('Media root not set.');
		} else {
			$this->mediaRoot = $configuration['mediaRoot'];
		}
	}


	/**
	 * Request handler.
	 *
	 * This is original unmodified (except the options checks)
	 * implementation from old index.php.
	 */
	public function handleRequest()
	{
		/**
		 * Find out if the client is requesting the test page.
		 */
		$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$testing = substr($request, -5) === '/test';

		/**
		 * If no data has been POSTed to the server and the client did not request the
		 * test page, exit imidiately.
		 */
		if (empty($_POST) && !$testing) {
			return;
		}

		/**
		 * Exit with HTTP code 403 if no password has been set on the server, or if the
		 * client did not submit a password, or the submitted password did not match
		 * this server's password.
		 */
		if (
			!isset($_POST['password']) ||
			$_POST['password'] !== hash('sha512', $this->password)
		) {
			$this->httpResponse(403);
			return;
		}

		/**
		 * If we were only supposed to test the server, end here.
		 */
		if ($testing) {
			return;
		}

		/**
		 * If the client did not submit a filesize, exit with HTTP code 400.
		 */
		if (!isset($_POST['filesize'])) {
			$this->httpResponse(400);
			return;
		}

		/**
		 * If the client did not upload a file, or something went wrong in the upload
		 * process, exit with HTTP code 401.
		 */
		if (
			!isset($_FILES['upfile']) ||
			$_FILES['upfile']['error'] !== UPLOAD_ERR_OK ||
			!is_uploaded_file($_FILES['upfile']['tmp_name'])
		) {
			$this->httpResponse(401);
			return;
		}

		/**
		 * If the client submitted filesize did not match the uploaded file's size, exit
		 * with HTTP code 411.
		 */
		if (intval($_POST['filesize']) !== $_FILES['upfile']['size']) {
			$this->httpResponse(411);
			return;
		}

		/**
		 * Sanitize the file name to maximise server operating system compatibility and
		 * minimize possible attacks against this implementation.
		 */
		$filename = preg_replace('@\s+@', '-', $_FILES['upfile']['name']);
		$filename = preg_replace('@[^0-9a-z._-]@i', '', $filename);
		$target = $this->mediaRoot . '/' . $filename;

		/**
		 * If a file with the same name and size exists, treat the new upload as a
		 * duplicate and exit.
		 */
		if (
			file_exists($target) &&
			filesize($target) === $_POST['filesize']
		) {
			$this->httpResponse(409);
			return;
		}

		/**
		 * Move the uploaded file into the target directory. If anything did not work,
		 * exit with HTTP code 500.
		 */
		if (!move_uploaded_file($_FILES["upfile"]["tmp_name"], $target)) {
			$this->httpResponse(500);
			return;
		}

		return;
	}


	/**
	 * Send HTTP response
	 *
	 * @param $code HTTP status code to send
	 * @param $msg Optional message. If not specified a standard message
	 * 	will be used.
	 */
	protected function httpResponse($code, $msg = null)
	{
		if (isset($_SERVER['SERVER_PROTOCOL'])) {
			$protocol = $_SERVER['SERVER_PROTOCOL'];
		} else {
			$protocol = 'HTTP/1.1';
		}

		if ($msg === null) {
			switch ($code) {
				case 100: $msg = 'Continue'; break;
				case 101: $msg = 'Switching Protocols'; break;
				case 200: $msg = 'OK'; break;
				case 201: $msg = 'Created'; break;
				case 202: $msg = 'Accepted'; break;
				case 203: $msg = 'Non-Authoritative Information'; break;
				case 204: $msg = 'No Content'; break;
				case 205: $msg = 'Reset Content'; break;
				case 206: $msg = 'Partial Content'; break;
				case 300: $msg = 'Multiple Choices'; break;
				case 301: $msg = 'Moved Permanently'; break;
				case 302: $msg = 'Found'; break;
				case 303: $msg = 'See Other'; break;
				case 304: $msg = 'Not Modified'; break;
				case 305: $msg = 'Use Proxy'; break;
				case 307: $msg = 'Temporary Redirect'; break;
				case 400: $msg = 'Bad Request'; break;
				case 401: $msg = 'Unauthorized'; break;
				case 402: $msg = 'Payment Required'; break;
				case 403: $msg = 'Forbidden'; break;
				case 404: $msg = 'Not Found'; break;
				case 405: $msg = 'Method Not Allowed'; break;
				case 406: $msg = 'Not Acceptable'; break;
				case 407: $msg = 'Proxy Authentication Required'; break;
				case 408: $msg = 'Request Timeout'; break;
				case 409: $msg = 'Conflict'; break;
				case 410: $msg = 'Gone'; break;
				case 411: $msg = 'Length Required'; break;
				case 412: $msg = 'Precondition Failed'; break;
				case 413: $msg = 'Request Entity Too Large'; break;
				case 414: $msg = 'Request-URI Too Long'; break;
				case 415: $msg = 'Unsupported Media Type'; break;
				case 416: $msg = 'Requested Range Not Satisfiable'; break;
				case 417: $msg = 'Expectation Failed'; break;
				case 500: $msg = 'Internal Server Error'; break;
				case 501: $msg = 'Not Implemented'; break;
				case 502: $msg = 'Bad Gateway'; break;
				case 503: $msg = 'Service Unavailable'; break;
				case 504: $msg = 'Gateway Timeout'; break;
				case 505: $msg = 'HTTP Version Not Supported'; break;
				default: throw new \InvalidArgumentException('Invalid code');
			}
		}

		header("$protocol $code $msg");
	}

}

