<?php

/**
 * Requirements Checker: This script will check if your system meets
 * the requirements for running Nette Framework.
 */


if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !isset($_SERVER['REMOTE_ADDR']) ||
	!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']))
{
	header('HTTP/1.1 403 Forbidden');
	echo 'Requirements Checker is available only from localhost';
	for ($i = 2e3; $i; $i--) echo substr(" \t\r\n", rand(0, 3), 1);
	exit;
}


/**
 * Check PHP configuration.
 */
foreach (array('function_exists', 'version_compare', 'extension_loaded', 'ini_get') as $function) {
	if (!function_exists($function)) {
		die("Error: function '$function' is required by Requirements Checker.");
	}
}



/**
 * Check Nette Framework requirements.
 */
$tests[] = array(
	'title' => 'Web server',
	'message' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown',
);

$tests[] = array(
	'title' => 'PHP version',
	'required' => TRUE,
	'passed' => version_compare(PHP_VERSION, '5.3.1', '>='),
	'message' => PHP_VERSION,
	'description' => 'Your PHP version is too old. Nette Framework requires at least PHP 5.3.1 or higher.',
);

$tests[] = array(
	'title' => 'Memory limit',
	'message' => ini_get('memory_limit'),
);

if (!isset($_SERVER['SERVER_SOFTWARE']) || strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== FALSE) {
	$tests['hf'] = array(
		'title' => '.htaccess file protection',
		'required' => FALSE,
		'description' => 'File protection by <code>.htaccess</code> is not present. You must be careful to put files into document_root folder.',
		'script' => '<script src="assets/denied/checker.js"></script> <script>displayResult("hf", typeof fileProtectionChecker == "undefined")</script>',
	);

	$tests['hr'] = array(
		'title' => '.htaccess mod_rewrite',
		'required' => FALSE,
		'description' => 'Mod_rewrite is probably not present. You will not be able to use Cool URL.',
		'script' => '<script src="assets/rewrite/checker"></script> <script>displayResult("hr", typeof modRewriteChecker == "boolean")</script>',
	);
}

$tests[] = array(
	'title' => 'Function ini_set()',
	'required' => FALSE,
	'passed' => function_exists('ini_set'),
	'description' => 'Function <code>ini_set()</code> is disabled. Some parts of Nette Framework may not work properly.',
);

$tests[] = array(
	'title' => 'Function error_reporting()',
	'required' => TRUE,
	'passed' => function_exists('error_reporting'),
	'description' => 'Function <code>error_reporting()</code> is disabled. Nette Framework requires this to be enabled.',
);

$tests[] = array(
	'title' => 'Function flock()',
	'required' => TRUE,
	'passed' => flock(fopen(__FILE__, 'r'), LOCK_SH),
	'description' => 'Function <code>flock()</code> is not supported on this filesystem. Nette Framework requires this to process atomic file operations.',
);

$tests[] = array(
	'title' => 'Register_globals',
	'required' => TRUE,
	'passed' => !iniFlag('register_globals'),
	'message' => 'Disabled',
	'errorMessage' => 'Enabled',
	'description' => 'Configuration directive <code>register_globals</code> is enabled. Nette Framework requires this to be disabled.',
);

$tests[] = array(
	'title' => 'Variables_order',
	'required' => TRUE,
	'passed' => strpos(ini_get('variables_order'), 'G') !== FALSE && strpos(ini_get('variables_order'), 'P') !== FALSE && strpos(ini_get('variables_order'), 'C') !== FALSE,
	'description' => 'Configuration directive <code>variables_order</code> is missing. Nette Framework requires this to be set.',
);

$tests[] = array(
	'title' => 'Session auto-start',
	'required' => FALSE,
	'passed' => session_id() === '' && !defined('SID'),
	'description' => 'Session auto-start is enabled. Nette Framework recommends not to use this directive for security reasons.',
);

$tests[] = array(
	'title' => 'PCRE with UTF-8 support',
	'required' => TRUE,
	'passed' => @preg_match('/pcre/u', 'pcre'),
	'description' => 'PCRE extension must support UTF-8.',
);

$reflection = new ReflectionFunction('iniFlag');
$tests[] = array(
	'title' => 'Reflection phpDoc',
	'required' => TRUE,
	'passed' => strpos($reflection->getDocComment(), 'Gets') !== FALSE,
	'description' => 'Reflection phpDoc are not available (probably due to an eAccelerator bug). You cannot use @annotations.',
);

$tests[] = array(
	'title' => 'ICONV extension',
	'required' => TRUE,
	'passed' => extension_loaded('iconv') && (ICONV_IMPL !== 'unknown') && @iconv('UTF-16', 'UTF-8//IGNORE', iconv('UTF-8', 'UTF-16//IGNORE', 'test')) === 'test',
	'message' => 'Enabled and works properly',
	'errorMessage' => 'Disabled or does not work properly',
	'description' => 'ICONV extension is required and must work properly.',
);

$tests[] = array(
	'title' => 'JSON extension',
	'required' => TRUE,
	'passed' => extension_loaded('json'),
);

$tests[] = array(
	'title' => 'Fileinfo extension',
	'required' => FALSE,
	'passed' => extension_loaded('fileinfo'),
	'description' => 'Fileinfo extension is absent. You will not be able to detect content-type of uploaded files.',
);

$tests[] = array(
	'title' => 'PHP tokenizer',
	'required' => TRUE,
	'passed' => extension_loaded('tokenizer'),
	'description' => 'PHP tokenizer is required.',
);

$tests[] = array(
	'title' => 'PDO extension',
	'required' => FALSE,
	'passed' => $pdo = extension_loaded('pdo') && PDO::getAvailableDrivers(),
	'message' => $pdo ? 'Available drivers: ' . implode(' ', PDO::getAvailableDrivers()) : NULL,
	'description' => 'PDO extension or PDO drivers are absent. You will not be able to use <code>Nette\Database</code>.',
);

$tests[] = array(
	'title' => 'Multibyte String extension',
	'required' => FALSE,
	'passed' => extension_loaded('mbstring'),
	'description' => 'Multibyte String extension is absent. Some internationalization components may not work properly.',
);

$tests[] = array(
	'title' => 'Multibyte String function overloading',
	'required' => TRUE,
	'passed' => !extension_loaded('mbstring') || !(mb_get_info('func_overload') & 2),
	'message' => 'Disabled',
	'errorMessage' => 'Enabled',
	'description' => 'Multibyte String function overloading is enabled. Nette Framework requires this to be disabled. If it is enabled, some string function may not work properly.',
);

$tests[] = array(
	'title' => 'Memcache extension',
	'required' => FALSE,
	'passed' => extension_loaded('memcache'),
	'description' => 'Memcache extension is absent. You will not be able to use <code>Nette\Caching\Storages\MemcachedStorage</code>.',
);

$tests[] = array(
	'title' => 'GD extension',
	'required' => FALSE,
	'passed' => extension_loaded('gd'),
	'description' => 'GD extension is absent. You will not be able to use <code>Nette\Image</code>.',
);

$tests[] = array(
	'title' => 'Bundled GD extension',
	'required' => FALSE,
	'passed' => extension_loaded('gd') && GD_BUNDLED,
	'description' => 'Bundled GD extension is absent. You will not be able to use some functions such as <code>Nette\Image::filter()</code> or <code>Nette\Image::rotate()</code>.',
);

$tests[] = array(
	'title' => 'Fileinfo extension or mime_content_type()',
	'required' => FALSE,
	'passed' => extension_loaded('fileinfo') || function_exists('mime_content_type'),
	'description' => 'Fileinfo extension or function <code>mime_content_type()</code> are absent. You will not be able to determine mime type of uploaded files.',
);

$tests[] = array(
	'title' => 'Intl extension',
	'required' => FALSE,
	'passed' => class_exists('Transliterator', FALSE),
	'description' => 'Class Transliterator is absent, the output of Nette\Utils\Strings::webalize() and Nette\Utils\Strings::toAscii() may not be accurate for non-latin alphabets.',
);

$tests[] = array(
	'title' => 'HTTP_HOST or SERVER_NAME',
	'required' => TRUE,
	'passed' => isset($_SERVER['HTTP_HOST']) || isset($_SERVER['SERVER_NAME']),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => 'Either <code>$_SERVER["HTTP_HOST"]</code> or <code>$_SERVER["SERVER_NAME"]</code> must be available for resolving host name.',
);

$tests[] = array(
	'title' => 'REQUEST_URI or ORIG_PATH_INFO',
	'required' => TRUE,
	'passed' => isset($_SERVER['REQUEST_URI']) || isset($_SERVER['ORIG_PATH_INFO']),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => 'Either <code>$_SERVER["REQUEST_URI"]</code> or <code>$_SERVER["ORIG_PATH_INFO"]</code> must be available for resolving request URL.',
);

$tests[] = array(
	'title' => 'SCRIPT_NAME or DOCUMENT_ROOT & SCRIPT_FILENAME',
	'required' => TRUE,
	'passed' => isset($_SERVER['SCRIPT_NAME']) || isset($_SERVER['DOCUMENT_ROOT'], $_SERVER['SCRIPT_FILENAME']),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => '<code>$_SERVER["SCRIPT_NAME"]</code> or <code>$_SERVER["DOCUMENT_ROOT"]</code> with <code>$_SERVER["SCRIPT_FILENAME"]</code> must be available for resolving script file path.',
);

$tests[] = array(
	'title' => 'REMOTE_ADDR or php_uname("n")',
	'required' => TRUE,
	'passed' => isset($_SERVER['REMOTE_ADDR']) || function_exists('php_uname'),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => '<code>$_SERVER["REMOTE_ADDR"]</code> or <code>php_uname("n")</code> must be available for detecting development / production mode.',
);



/**
 * Gets the boolean value of a configuration option.
 * @param  string  configuration option name
 * @return bool
 */
function iniFlag($var)
{
	$status = strtolower(ini_get($var));
	return $status === 'on' || $status === 'true' || $status === 'yes' || (int) $status;
}


/**
 * Redirect.
 */
$redirect = round(time(), -1);
if (!isset($_GET) || (isset($_GET['r']) && $_GET['r'] == $redirect)) {
	$redirect = NULL;
}


/**
 * Paint it.
 */
$errors = $warnings = FALSE;

foreach ($tests as $id => $requirement)
{
	$tests[$id] = $requirement = (object) $requirement;
	if (isset($requirement->passed) && !$requirement->passed) {
		if ($requirement->required) {
			$errors = TRUE;
		} else {
			$warnings = TRUE;
		}
	}
}


header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: s-maxage=0, max-age=0, must-revalidate');
header('Expires: Mon, 23 Jan 1978 10:00:00 GMT');

/**
 * @param array    $tests
 * @param bool     $errors
 * @param bool     $warnings
 * @param string   $redirect
 */
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="robots" content="noindex">
	<meta name="generator" content="Nette Framework">
	<?php if ($redirect): ?><meta http-equiv="Refresh" content="0; URL=?r=<?php echo $redirect ?>"><?php endif ?>

	<title>Nette Framework Requirements Checker</title>

	<style>
	html {
		font: 13px/1.5 Verdana, sans-serif;
		border-top: 5.3em solid #F4EBDB;
	}

	body {
		border-top: 1px solid #E4DED5;
		margin: 0;
		background: white;
		color: #333;
	}

	#wrapper {
		max-width: 780px;
		margin: -5.3em auto 3em;
	}

	h1 {
		font: 2.3em/1.5 sans-serif;
		margin: .5em 0 1.5em;
		background: url('assets/logo.png') right center no-repeat;
		color: #7A7772;
		text-shadow: 1px 1px 0 white;
	}

	h2 {
		font-size: 2em;
		font-weight: normal;
		color: #3484D2;
		margin: .7em 0;
	}

	p {
		margin: 1.2em 0;
	}

	.result {
		margin: 1.5em 0;
		padding: 0 1em;
		border: 2px solid white;
	}

	.passed h2 {
		color: #1A7E1E;
	}

	.failed h2 {
		color: white;
	}

	table {
		padding: 0;
		margin: 0;
		border-collapse: collapse;
		width: 100%;
	}

	table td, table th {
		text-align: left;
		padding: 10px;
		vertical-align: top;
		border-style: solid;
		border-width: 1px 0 0;
		border-color: inherit;
		background: white none no-repeat 12px 8px;
		background-color: inherit;
	}

	table th {
		font-size: 105%;
		font-weight: bold;
		padding-left: 50px;
	}

	.passed, .info {
		background-color: #E4F9E3;
		border-color: #C6E5C4;
	}

	.passed th {
		background-image: url('assets/passed.gif');
	}

	.info th {
		background-image: url('assets/info.gif');
	}

	.warning {
		background-color: #FEFAD4;
		border-color: #EEEE99;
	}

	.warning th {
		background-image: url('assets/warning.gif');
	}

	.failed {
		background-color: #F4D2D2;
		border-color: #D2B994;
	}

	div.failed {
		background-color: #CD1818;
		border-color: #CD1818;
	}

	.failed th {
		background-image: url('assets/failed.gif');
	}

	.description td {
		border-top: none !important;
		padding: 0 10px 10px 50px;
		color: #555;
	}

	.passed.description {
		display: none;
	}
	</style>

	<script>
	var displayResult = function(id, passed) {
		var tr = document.getElementById('res' + id);
		tr.className = passed ? 'passed' : 'warning';
		tr.getElementsByTagName('td').item(0).innerHTML = passed ? 'Enabled' : 'Disabled';
		if (tr = document.getElementById('desc' + id)) {
			if (passed) {
				tr.parentNode.removeChild(tr);
			} else {
				tr.className = 'warning description';
			}
		}
	}
	</script>
</head>

<body>
<div id="wrapper">
	<h1>Nette Framework Requirements Checker</h1>

	<p>This script checks if your server and PHP configuration meets the requirements
	for running <a href="https://nette.org/">Nette Framework</a>. It checks version of PHP,
	if appropriate PHP extensions have been loaded, and if PHP directives are set correctly.</p>

	<?php if ($errors): ?>
	<div class="failed result">
		<h2>Sorry, your server configuration does not satisfy the requirements of Nette Framework.</h2>
	</div>
	<?php else: ?>
	<div class="passed result">
		<h2>Congratulations! Server configuration meets the minimum requirements for Nette Framework.</h2>
		<?php if ($warnings):?><p><strong>Please see the warnings listed below.</strong></p><?php endif ?>
	</div>
	<?php endif ?>


	<h2>Details</h2>

	<table>
	<?php foreach ($tests as $id => $requirement):?>
	<?php $class = isset($requirement->passed) ? ($requirement->passed ? 'passed' : ($requirement->required ? 'failed' : 'warning')) : 'info' ?>
	<tr id="res<?php echo $id ?>" class="<?php echo $class ?>">
		<th><?php echo htmlSpecialChars($requirement->title) ?></th>

		<?php if (empty($requirement->passed) && isset($requirement->errorMessage)): ?>
			<td><?php echo htmlSpecialChars($requirement->errorMessage) ?></td>
		<?php elseif (isset($requirement->message)): ?>
			<td><?php echo htmlSpecialChars($requirement->message) ?></td>
		<?php elseif (isset($requirement->passed)): ?>
			<td><?php echo $requirement->passed ? 'Enabled' : 'Disabled' ?></td>
		<?php else: ?>
			<td>Not tested</td>
		<?php endif ?>
	</tr>

	<?php if (isset($requirement->description)): ?>
	<tr id="desc<?php echo $id ?>" class="<?php echo $class ?> description">
		<td colspan="2"><?php echo $requirement->description ?></td>
	</tr>
	<?php endif ?>

	<?php if (isset($requirement->script)): ?>
		<?php echo $requirement->script ?>
	<?php endif ?>

	<?php endforeach ?>
	</table>

	<?php if ($errors): ?><p>Please check the error messages and <a href="?">try again</a>.</p><?php endif ?>
</div>
</body>
</html>
