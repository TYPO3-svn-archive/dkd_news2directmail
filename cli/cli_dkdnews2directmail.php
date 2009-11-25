<?php
/* cli_dkdnews2directmail.php,v 1.1 2006/11/23 17:17:40 dkd-kartolo Exp */
	// Defining circumstances for CLI mode:
define('TYPO3_cliMode', TRUE);


	// Defining PATH_thisScript here: Must be the ABSOLUTE path of this script in the right context:
	// This will work as long as the script is called by it's absolute path!
// define('PATH_thisScript',$_ENV['_']?$_ENV['_']:$_SERVER['_']);
define('PATH_thisScript',$_SERVER['argv'][0]);


	// Change working directory to the directory of the script.
chdir( dirname( PATH_thisScript ) );


	// Include configuration file:
require(dirname(PATH_thisScript).'/conf.php');

	// Include init file:
require(dirname(PATH_thisScript).'/'.$BACK_PATH.'init.php');

 // Define path to TSlib
define('PATH_tslib', PATH_site.'tslib/');


// HERE you run your application!
define( 'TYPO3_MODE', 'BE' );

require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_t3lib.'class.t3lib_extmgm.php');
require_once(PATH_t3lib.'class.t3lib_cs.php');

require_once(PATH_t3lib."config_default.php");
if (!defined ("TYPO3_db")) 	die ("The configuration file was not included.");


// Require DB classes
require_once(PATH_t3lib.'class.t3lib_db.php');	// The database library
$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');


// Set debug options for DB
// $GLOBALS['TYPO3_DB']->debugOutput = true;
// $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;


// Connect to the database, needed at least for t3lib_extmgm to work
$result = $GLOBALS['TYPO3_DB']->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password);
if (!$result)	{
	die("Couldn't connect to database at ".TYPO3_db_host);
}
$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);

// Application code starts here


$_EXTCONF = unserialize($_EXTCONF);

if($_EXTCONF['directmailVersion']){
	// > 2.5.x is installed
	require(dirname(PATH_thisScript).'/class.ext_tx_directmail_dmail.php');
	// Make instance:
	$SOBE = t3lib_div::makeInstance('ext_tx_directmail_dmail');
} else {
	// < 2.5.x is installed
	require(dirname(PATH_thisScript).'/class.ext_mod_web_dmail.php');
	// Make instance:
	$SOBE = t3lib_div::makeInstance('ext_mod_web_dmail');
}

// argv[1] => newsletter uid
$SOBE->init($argv[1]);

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();

?>
