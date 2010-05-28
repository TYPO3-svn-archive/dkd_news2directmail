<?php

########################################################################
# Extension Manager/Repository config file for ext: "dkd_news2directmail"
#
# Auto generated 29-09-2007 17:44
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'dkd_news2directmail',
	'description' => 'Automatically sends tt_news as a directmail',
	'category' => 'be',
	'author' => 'Ivan Kartolo',
	'author_email' => 'ivan.kartolo@dkd.de',
	'shy' => '',
	'dependencies' => 'tt_news,direct_mail',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod1,cli',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_news',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'd.k.d Internet Service GmbH',
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
			'tt_news' => '',
			'direct_mail' => '2.5.x',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:22:{s:9:"ChangeLog";s:4:"1f4b";s:10:"README.txt";s:4:"a4cb";s:21:"ext_conf_template.txt";s:4:"6e58";s:12:"ext_icon.gif";s:4:"b831";s:14:"ext_tables.php";s:4:"04a8";s:14:"ext_tables.sql";s:4:"3b01";s:16:"locallang_db.xml";s:4:"1772";s:31:"cli/class.ext_mod_web_dmail.php";s:4:"529f";s:37:"cli/class.ext_tx_directmail_dmail.php";s:4:"46cc";s:30:"cli/cli_dkdnews2directmail.php";s:4:"a97c";s:12:"cli/conf.php";s:4:"7354";s:14:"doc/manual.sxw";s:4:"4bfa";s:19:"doc/wizard_form.dat";s:4:"c93d";s:20:"doc/wizard_form.html";s:4:"a8fd";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"ea2f";s:14:"mod1/index.php";s:4:"37a2";s:18:"mod1/locallang.xml";s:4:"05a5";s:22:"mod1/locallang_mod.xml";s:4:"dcfe";s:19:"mod1/moduleicon.gif";s:4:"b831";s:45:"static/tt_news_single_plaintext/constants.txt";s:4:"25ca";s:41:"static/tt_news_single_plaintext/setup.txt";s:4:"2bdc";}',
	'suggests' => array(
	),
);

?>