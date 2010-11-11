<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_dkdnews2directmail_senddmail" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:dkd_news2directmail/locallang_db.xml:tt_news.tx_dkdnews2directmail_senddmail",		
		"config" => Array (
			"type" => "check",
		)
	)
);


t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news","tx_dkdnews2directmail_senddmail;;;;1-1-1");

//add static TS
t3lib_extMgm::addStaticFile($_EXTKEY,'static/tt_news_single_plaintext/', 'news2directmail Plaintext');

if (TYPO3_MODE=="BE")	{
		
	t3lib_extMgm::addModule("web","txdkdnews2directmailM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}
?>