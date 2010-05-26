<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Ivan Kartolo <ivan.kartolo@dkd.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Module 'News 2 Directmail' for the 'dkd_news2directmail' extension.
 *
 * @author	Ivan Kartolo <ivan.kartolo@dkd.de>
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:dkd_news2directmail/mod1/locallang.xml");
require_once (PATH_t3lib."class.t3lib_scbase.php");
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_dkdnews2directmail_module1 extends t3lib_SCbase {
	var $pageinfo;
	var $TSconfPrefix = 'mod.web_modules.news2directmail.';
	
	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
		
		$this->modList = $this->getListOfBackendModules(array('dmail'),$this->perms_clause,$BACK_PATH);
		$temp = t3lib_BEfunc::getModTSconfig($this->id,'mod.web_modules.news2directmail');
		$this->params = $temp['properties'];
		$this->implodedParams = t3lib_BEfunc::implodeTSParams($this->params);
		
		$this->updatePageTS();
		/*
		if (t3lib_div::_GP("clear_all_cache"))	{
			$this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
		}
		*/
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{

				// Draw the header.
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br />".$LANG->sL("LLL:EXT:lang/locallang_core.xml:labels.path").": ".t3lib_div::fixed_lgd_cs($this->pageinfo["_thePath"],50);

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
//			$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
//			$this->content.=$this->doc->divider(5);

			$module = $this->pageinfo['module'];

			if (!$module)	{
				$pidrec=t3lib_BEfunc::getRecord('pages',intval($this->pageinfo['pid']));
				$module=$pidrec['module'];
			}
			if ($module == 'dmail') {
				$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection, t3lib_BEfunc::getFuncMenu($this->id,'SET[dmail_mode]',$this->MOD_SETTINGS['dmail_mode'],$this->MOD_MENU['dmail_mode']).t3lib_BEfunc::cshItem($this->cshTable,'',$BACK_PATH)));

					// Render content:
				$this->moduleContent();
			} else {
				$this->content.=$this->doc->section($LANG->getLL('dmail_folders').t3lib_BEfunc::cshItem($this->cshTable,'folders',$BACK_PATH), $this->modList['list'], 1, 1, 0 , TRUE);
			}


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		$this->content .= $this->cmd_conf();
		$this->updatePageTS();
	}


	function updatePageTS()	{
		global $BE_USER;

		if ($BE_USER->doesUserHaveAccess(t3lib_BEfunc::getRecord( 'pages', $this->id), 2)) {
			$pageTS = t3lib_div::_GP('pageTS');
			if (is_array($pageTS))	{
				t3lib_BEfunc::updatePagesTSconfig($this->id,$pageTS,$this->TSconfPrefix);
				header('Location: '.t3lib_div::locationHeaderUrl(t3lib_div::getIndpEnv('REQUEST_URI')));
			}
		}
	}
	
	function cmd_conf() {
		global $TYPO3_DB, $LANG;

		$configArray = array(
			'spacer0' => $LANG->getLL('configure_news2directmail'),
			'recipient_list' => array('short', $LANG->getLL('recipient_list'), $LANG->getLL('recipient_list.description').'<br />'.$LANG->getLL('recipient_list.details')),
			'send_time' => array('short', $LANG->getLL('send_time'), $LANG->getLL('send_time.description').'<br />'.$LANG->getLL('send_time.details')),
			'URL' => array('string', $LANG->getLL('URL'), $LANG->getLL('URL.description').'<br />'.$LANG->getLL('URL.details')),
			);

		$theOutput.= $this->doc->section($LANG->getLL('configure_direct_mail_module'),str_replace('Update configuration', $LANG->getLL('configure_update_configuration'), t3lib_BEfunc::makeConfigForm($configArray,$this->implodedParams,'pageTS')),1,1,0, TRUE);
		return $theOutput;
	}
	
	/**
	 * Returns "list of backend modules". Most likely this will be obsolete soon / removed. Don't use.
	 * Usage: 0
	 *
	 * @param	array		Module names in array. Must be "addslashes()"ed
	 * @param	string		Perms clause for SQL query
	 * @param	string		Backpath
	 * @param	string		The URL/script to jump to (used in A tag)
	 * @return	array		Two keys, rows and list
	 * @internal
	 * @deprecated since TYPO3 3.6, this function will be removed in TYPO3 4.5.
	 * @obsolete
	 */
	public static function getListOfBackendModules($name, $perms_clause, $backPath = '', $script = 'index.php') {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'doktype!=255 AND module IN (\''.implode('\',\'', $name).'\') AND'.$perms_clause.t3lib_BEfunc::deleteClause('pages'));
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res))	return false;

		$out = '';
		$theRows = array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$theRows[] = $row;
			$out.='<span class="nobr"><a href="'.htmlspecialchars($script.'?id='.$row['uid']).'">'.
					t3lib_iconWorks::getIconImage('pages', $row, $backPath, 'title="'.htmlspecialchars(t3lib_BEfunc::getRecordPath($row['uid'], $perms_clause, 20)).'" align="top"').
					htmlspecialchars($row['title']).
					'</a></span><br />';
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return array('rows'=>$theRows, 'list'=>$out);
	}
	
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dkd_news2directmail/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dkd_news2directmail/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_dkdnews2directmail_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>