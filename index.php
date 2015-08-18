<?

define('MEDIA_ROOT', '/tmp/media/'); // with trailing slash
define('PASSWORD', 'asdfasdf'); // pw as cleartext - NOT RECOMMENDE, use the hased one down here
$pw_hash = null; // sha512 hased pw - dont need the clear one above

// to disable logging, set $logfile to null
$logfile = '/tmp/photobackup.log';


function errorHandler($code, $text, $file, $line) {
    if (!(error_reporting() & $code))
        return;

	debug('ERR:'.$code .' '.$text.' ['.$file.':'.$line.']');
	if ($code ==  E_USER_ERROR) {
		header('HTTP/1.1 500 ERROR: internal server error');
		exit(1);
	}
    return true; // no php error handling
}
set_error_handler("errorHandler");


if (empty($pw_hash)) {
	$pw_hash = hash('sha512', PASSWORD);
}

function debug($msg) {
	if ($GLOBALS['logfile'])
		file_put_contents($GLOBALS['logfile'], date('d.M.Y H:i:s') . ': ' .$msg."\n", FILE_APPEND);
}

// try to make the media_root
@mkdir(MEDIA_ROOT, 0700, true);

if (!file_exists(MEDIA_ROOT) || !is_writable(MEDIA_ROOT)) {
	debug('MEDIA ROOT not writable: '.MEDIA_ROOT);
	header('HTTP/1.1 500 ERROR: MEDIA ROOT not exist or not writable!');
    die();	
}

$url = @$_GET['url'] ?: '/';
debug('call '.$_SERVER['REQUEST_METHOD'].' '.$url);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$params = &$_POST;
	
	if ($params['password'] != $pw_hash) {
		debug('password not match: '.$params['password']);
		header('HTTP/1.1 403 ERROR: wrong password!');
        die();
	}

	if ($url == '/test') {
		$trg_path = MEDIA_ROOT . '.test_file_to_write';
		// save testfile
		if (!file_put_contents($trg_path, 'test')) {
			debug('could not write file to '. MEDIA_ROOT);
			header('HTTP/1.1 500 ERROR: could not write file to '. MEDIA_ROOT);
	        die();	
		}
		// remove created testfile
		@unlink($trg_path);
	} elseif ($url == '/') { // upload
		if (!isset($_FILES['upfile']) || empty($_FILES['upfile'])) {
			debug('no upfile: ' . print_r($_FILES, true));
			header('HTTP/1.1 401 ERROR: no file in the request!');
	        die();
		}

		$f = &$_FILES['upfile'];
		$trg_path = MEDIA_ROOT . $f['name'];
		if (file_exists($trg_path)) {
			debug('file exists: ' . $trg_path);
			header('HTTP/1.1 500 file '.$f['name']. ' already exists');
			die();
		}

		if (empty($params['filesize']) || !is_numeric($params['filesize'])) {
			debug('missing filesize');
			header('HTTP/1.1 400 ERROR: missing file size in the request!');
	        die();
		} elseif ($params['filesize'] != $f['size']) {
			debug('filesize not match: '.$params['filesize']. ' - '. $f['size']);
			header('HTTP/1.1 411 ERROR: file sizes do not match!');
	        die();
		}

		debug('could not move uploaded file: '.$f['tmp_name']. ' to '. $trg_path);
		// save uploaded file
		if (!move_uploaded_file($f['tmp_name'], $trg_path)) {
			debug('could not move uploaded file: '.$f['tmp_name']. ' to '. $trg_path);
			header('HTTP/1.1 500 ERROR: could not move uploaded file!');
	        die();	
		}
		debug('SUCCESSFULLY move uploaded file: '.$f['tmp_name']. ' to '. $trg_path);
	} else {
		debug('wrong path: '.$url);
		header('HTTP/1.1 403 ERROR: path not found: ' .$url);
        die();
	}

	
	header('HTTP/1.1 200 OK');
    die();
} else { // show infopage
	die(file_get_contents('index.tpl'));
}
	
?>