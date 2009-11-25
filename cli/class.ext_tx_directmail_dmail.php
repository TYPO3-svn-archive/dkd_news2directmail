<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 dkd-ivan
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

/*
 * class.ext_mod_web_dmail.php,v 1.1 2006/11/23 17:17:40 dkd-kartolo Exp
 */
require_once(t3lib_extMgm::extPath('direct_mail').'mod2/class.tx_directmail_dmail.php');
require_once (PATH_t3lib.'class.t3lib_tstemplate.php');

class ext_tx_directmail_dmail extends tx_directmail_dmail {
	
	/*
	 * what need to be initialized??
	 * 
	 * @param	int		page id of the newsletter
	 */
//TODO: init from mod_web_dmail? what else need to be initialized?
	function init($newsletter_uid){
		global $LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS, $TYPO3_DB, $MCONF;
		
		$this->include_once[]=PATH_t3lib.'class.t3lib_tcemain.php';
		
			// initialize the TS template
		$GLOBALS['TT'] = new t3lib_timeTrack;
		$this->tmpl = t3lib_div::makeInstance('t3lib_TStemplate');
		$this->tmpl->init();
		
			// initialize the page selector
		$this->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$this->sys_page->init(true);
		
		//get the id of dmail sysfolder
		$this->get_pid($newsletter_uid);
		
		//PageTS for dmail
		$temp = t3lib_BEfunc::getModTSconfig($this->id,'mod.web_modules.dmail');
		$this->params = $temp['properties'];
		
		//PageTS for news2dmail
		unset($temp);
		$temp = t3lib_BEfunc::getModTSconfig($this->id,'mod.web_modules.news2directmail');
		$this->params_news2dmail = $temp['properties'];
		
		t3lib_div::loadTCA('sys_dmail');

	}
	
	/*
	 * 
	 */
//TODO: how to render plaintext ?  
	function main(){
		global $TYPO3_DB;
		
		//get tt_news_uid
		$this->tt_news = $this->get_ttnews_uid();
		if(!empty($this->tt_news['uid'])){
			$this->perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
			
			$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
	
				//set param for cmd_send_mail
			$this->mailgroup_uid = $this->params_news2dmail['recipient_list'];
			$this->mailingMode_mailGroup = 1;
			$this->send_mail_datetime = $GLOBALS['EXEC_TIME'] + ($this->params_news2dmail['send_time'] * 60);
			$this->CMD = 'send_mail_final';
			
			//Set HTML und plain param
			$this->params['HTMLParams'] = '&no_cache=1&tx_ttnews[tt_news]='.$this->tt_news['uid'];
			$this->params['plainParams'] = '&no_cache=1&tx_ttnews[tt_news]='.$this->tt_news['uid'].'&type=99';
			
			//create dmail record
			$this->createDMail();
			
			// auto fetching
			// Read new record (necessary because TCEmain sets default field values)
	  		$dmailRec = t3lib_BEfunc::getRecord ('sys_dmail',$this->sys_dmail_uid);

	  		// Set up URLs from record data for fetch command
	  		$this->setURLs($dmailRec);
			$this->cmd_fetch($dmailRec,TRUE);
	
	  		//dmail record is ready to send
	  		$this->cmd_send_mail($dmailRec);
	  		
	  		//TODO: put dmail record uid to news
	  		$TYPO3_DB->exec_UPDATEquery(
	  			'tt_news',
	  			'uid = '.$this->tt_news['uid'],
	  			array(
	  				'tx_dkdnews2directmail_dmail_uid' => $this->sys_dmail_uid
	  			)
	  		);
		}
	}
	
		/**
	 * Creates a directmail entry in th DB.
	 * @return	[type]		...
	 */
	function createDMail()	{
		global $TCA, $TYPO3_CONF_VARS;

			//only from internal uid
		$createMailFrom_UID = $this->newsletter_uid;	// Internal page

		if ($createMailFrom_UID)	{
				// Set default values:
			$dmail = array();
			$dmail['sys_dmail']['NEW'] = array (
				'from_email'		=> $this->params['from_email'],
				'from_name'		=> $this->params['from_name'],
				'replyto_email'		=> $this->params['replyto_email'],
				'replyto_name'		=> $this->params['replyto_name'],
				'return_path'		=> $this->params['return_path'],
				'priority'		=> $this->params['priority'],
				'use_domain'		=> $this->params['use_domain'],
				'use_rdct'		=> $this->params['use_rdct'],
				'long_link_mode'	=> $this->params['long_link_mode'],
				'organisation'		=> $this->params['organisation'],
				'authcode_fieldList'	=> $this->params['authcode_fieldList']
				);

			$dmail['sys_dmail']['NEW']['sendOptions'] = $TCA['sys_dmail']['columns']['sendOptions']['config']['default'];
			$dmail['sys_dmail']['NEW']['long_link_rdct_url'] = $this->getUrlBase($this->params['use_domain']);

				// If params set, set default values:
			if (isset($this->params['sendOptions']))	$dmail['sys_dmail']['NEW']['sendOptions'] = $this->params['sendOptions'];
			if (isset($this->params['includeMedia'])) 	$dmail['sys_dmail']['NEW']['includeMedia'] = $this->params['includeMedia'];
			if (isset($this->params['flowedFormat'])) 	$dmail['sys_dmail']['NEW']['flowedFormat'] = $this->params['flowedFormat'];
			if (isset($this->params['HTMLParams']))		$dmail['sys_dmail']['NEW']['HTMLParams'] = $this->params['HTMLParams'];
			if (isset($this->params['plainParams']))	$dmail['sys_dmail']['NEW']['plainParams'] = $this->params['plainParams'];
			if (isset($this->params['direct_mail_encoding']))	$dmail['sys_dmail']['NEW']['encoding'] = $this->params['direct_mail_encoding'];

			if (t3lib_div::testInt($createMailFrom_UID))	{
				$createFromMailRec = t3lib_BEfunc::getRecord ('pages',$createMailFrom_UID);
				if (t3lib_div::inList($TYPO3_CONF_VARS['FE']['content_doktypes'],$createFromMailRec['doktype']))	{
//TODO: mail subject is news title? charset?
					$dmail['sys_dmail']['NEW']['subject'] = $this->tt_news['title'];
					$dmail['sys_dmail']['NEW']['type'] = 0;
					$dmail['sys_dmail']['NEW']['page'] = $createFromMailRec['uid'];
					$dmail['sys_dmail']['NEW']['charset'] = $this->getPageCharSet($createFromMailRec['uid']);
					$dmail['sys_dmail']['NEW']['pid'] = $this->pageinfo['uid'];
				}
			} 

			if ($dmail['sys_dmail']['NEW']['pid'] && $dmail['sys_dmail']['NEW']['sendOptions']) {
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				$tce->stripslashes_values=0;
				$tce->start($dmail,Array());
				$tce->process_datamap();
				$this->sys_dmail_uid = $tce->substNEWwithIDs['NEW'];
			} else {
				if (!$dmail['sys_dmail']['NEW']['sendOptions']) {
					
					//error??
					$this->error = 'no_valid_url';
					die('no_valid_url');
				}
			}
		}
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$row: ...
	 * @return	[type]		...
	 */
	function cmd_send_mail($row)	{
		global $LANG, $TYPO3_DB;

			// Preparing mailer
		$htmlmail = t3lib_div::makeInstance('dmailer');
		$htmlmail->start();
		$htmlmail->dmailer_prepare($row);

		$sentFlag=false;

		if ($this->mailingMode_mailGroup && $this->sys_dmail_uid && intval($this->mailgroup_uid))	{
				// Update the record:
			$result = $this->cmd_compileMailGroup(intval($this->mailgroup_uid));
			$query_info=$result['queryInfo'];

			$distributionTime = intval($this->send_mail_datetime);
			$distributionTime = $distributionTime<time() ? time() : $distributionTime;

			$updateFields = array(
				'scheduled' => $distributionTime,
				'query_info' => serialize($query_info)
				);
			$TYPO3_DB->exec_UPDATEquery(
				'sys_dmail',
				'uid='.intval($this->sys_dmail_uid),
				$updateFields
				);

			$sentFlag=true;

			$this->noView=1;
		}
				
			// Setting flags:
		if ($sentFlag && $this->CMD=='send_mail_final')	{
				// Update the record:
			$TYPO3_DB->exec_UPDATEquery(
				'sys_dmail',
				'uid='.intval($this->sys_dmail_uid),
				array('issent' => 1)
				);
		}
//		return $theOutput;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$formname: ...
	 * @return	string		urlbase
	 */
	function getUrlBase($domainUid) {
		global $TYPO3_DB;

		$domainName = '';
		$scheme = '';
		$port = '';
		if ($domainUid) {
			$res_domain = $TYPO3_DB->exec_SELECTquery(
				'domainName',
				'sys_domain',
				'uid='.intval($domainUid).
					t3lib_BEfunc::deleteClause('sys_domain')
				);
			if ($row_domain = $TYPO3_DB->sql_fetch_assoc($res_domain)) {
				$domainName = $row_domain['domainName'];
				$url_parts = parse_url(t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR'));
				$scheme = $url_parts['scheme'];
				$port = $url_parts['port'];
			}
		}

		return ($domainName ? (($scheme?$scheme:'http') . '://' . $domainName . ($port?':'.$port:'') . '/') : $this->params_news2dmail['URL'].'/').'index.php';
	} 
	
	/*[Describe function...]
	 *
	 * @param	[type]		$row: ...
	 * @return	[type]		...
	 */
	function cmd_fetch($row)        {
		global $TCA, $TYPO3_DB, $LANG;

		$theOutput = '';
		$errorMsg = '';
		$warningMsg = '';
		$content ='';
		$success = FALSE;

			// Make sure long_link_rdct_url is consistent with use_domain.
		$this->urlbase = $this->getUrlBase($row['use_domain']);
		$row['long_link_rdct_url'] = $this->urlbase;

			// Compile the mail
		$htmlmail = t3lib_div::makeInstance('dmailer');
		if($this->params['enable_jump_url']) {
			$htmlmail->jumperURL_prefix = $this->urlbase.'?id='.$row['page'].'&rid=###SYS_TABLE_NAME###_###USER_uid###&mid=###SYS_MAIL_ID###&aC=###SYS_AUTHCODE###&jumpurl=';
			$htmlmail->jumperURL_useId=1;
		}
		$htmlmail->start();
		$htmlmail->charset = $row['charset'];
		$htmlmail->useBase64();
		$htmlmail->http_username = $this->params['http_username'];
		$htmlmail->http_password = $this->params['http_password'];
		$htmlmail->includeMedia = $row['includeMedia'];

		if ($this->url_plain) {
			$content = t3lib_div::getURL($this->addUserPass($this->url_plain));
				// If we have a conversion table for certain entities and the used charset we do a search and replace.
			if ( is_file( t3lib_extMgm::extPath( 'direct_mail' ) . 'res/char-conv/' . $row['charset'].'.php' ) ) {
				require_once( t3lib_extMgm::extPath( 'direct_mail' ) . 'res/char-conv/'.$row['charset'].'.php' );
				if ( is_array( $charConv ) ) {
					foreach( $charConv AS $cvKey => $cvVal ) {
						$search[] = $cvKey;
						$replace[] = $cvVal;
					}
					$content = str_replace( $search, $replace, $content );
				}
			}
			$htmlmail->addPlain($content);
			if (!$content || !$htmlmail->theParts['plain']['content']) {
				$errorMsg .= 'dmail_no_plain_content'.chr(10);
			} elseif (!strstr(base64_decode($htmlmail->theParts['plain']['content']),'<!--DMAILER_SECTION_BOUNDARY')) {
				$warningMsg .= 'dmail_no_plain_boundaries'.chr(10);
			}
		}
		if ($this->url_html) {
			$success = $htmlmail->addHTML($this->url_html);    // Username and password is added in htmlmail object
			if (!$row['charset']) {		// If no charset was set, we have an external page.
					// Try to auto-detect the charset of the message
				$matches = array();
				$res = preg_match('/<meta[\s]+http-equiv="Content-Type"[\s]+content="text\/html;[\s]+charset=([^"]+)"/m', $htmlmail->theParts['html_content'], $matches);
				if ($res==1) {
					$htmlmail->charset = $matches[1];
				} elseif (isset($this->params['direct_mail_charset'])) {
					$htmlmail->charset = $LANG->csConvObj->parse_charset($this->params['direct_mail_charset']);
				} else {
					$htmlmail->charset = 'iso-8859-1';
				}
				$htmlmail->useBase64();   // Reset content-type headers with new charset
			}
			if ($htmlmail->extractFramesInfo()) {
				$errorMsg .= 'dmail_frames_not allowed'.chr(10);
			} elseif (!$success || !$htmlmail->theParts['html']['content']) {
				$errorMsg .= 'dmail_no_html_content'.chr(10);
			} elseif (!strstr(base64_decode($htmlmail->theParts['html']['content']),'<!--DMAILER_SECTION_BOUNDARY')) {
				$warningMsg .= 'dmail_no_html_boundaries'.chr(10);
			}
		}

		$attachmentArr = t3lib_div::trimExplode(',', $row['attachment'],1);
		if (count($attachmentArr))	{
			t3lib_div::loadTCA('sys_dmail');
			$upath = $TCA['sys_dmail']['columns']['attachment']['config']['uploadfolder'];
			while(list(,$theName)=each($attachmentArr))	{
				$theFile = PATH_site.$upath.'/'.$theName;
				if (@is_file($theFile))	{
					$htmlmail->addAttachment($theFile, $theName);
				}
			}
		}

		if (!$errorMsg) {
				// Update the record:
			$htmlmail->theParts['messageid'] = $htmlmail->messageid;
			$mailContent = serialize($htmlmail->theParts);
			$updateFields = array(
				'issent' => 0,
				'charset' => $htmlmail->charset,
				'mailContent' => $mailContent,
				'renderedSize' => strlen($mailContent),
				'long_link_rdct_url' => $this->urlbase
				);
			$TYPO3_DB->exec_UPDATEquery(
				'sys_dmail',
				'uid='.intval($this->sys_dmail_uid),
				$updateFields
				);

				// Read again:
			$res = $TYPO3_DB->exec_SELECTquery(
				'*',
				'sys_dmail',
				'pid='.intval($this->id).
					' AND uid='.intval($this->sys_dmail_uid).
					t3lib_BEfunc::deleteClause('sys_dmail')
					);
			$row = $TYPO3_DB->sql_fetch_assoc($res);

echo $warningMsg;			
		} else {
			
echo $errorMsg;
			$this->noView = 1;
		}

		return $theOutput;
	}
	
	/**
 	 * Set up URL variables for this $row.
 	 *
	 */
 	function setURLs($row)	{
		// Finding the domain to use
 		$this->urlbase = $this->getUrlBase($row['use_domain']);
 
 			// Finding the url to fetch content from
 		switch((string)$row['type'])	{
 			case 1:
 				$this->url_html = $row['HTMLParams'];
 				$this->url_plain = $row['plainParams'];
 				break;
 			default:
 				$this->url_html = $this->urlbase.'?id='.$row['page'].$row['HTMLParams'];
 				$this->url_plain = $this->urlbase.'?id='.$row['page'].$row['plainParams'];
 				break;
 		}
 
 		if (!($row['sendOptions']&1) || !$this->url_plain)	{	// plain
 			$this->url_plain='';
 		} else {
 			$urlParts = @parse_url($this->url_plain);
 			if (!$urlParts['scheme'])	{
 				$this->url_plain='http://'.$this->url_plain;
 			}
 		}
 		if (!($row['sendOptions']&2) || !$this->url_html)	{	// html
 			$this->url_html='';
 		} else {
 			$urlParts = @parse_url($this->url_html);
			if (!$urlParts['scheme'])	{
 				$this->url_html='http://'.$this->url_html;
 			}
 		}
 	}
  
	/*
	 * @param	int		newsletter uid
	 * @return 	void	put PID from newsletter page in this->id
	 */
	function get_pid($newsletter_uid){
		global $TYPO3_DB;
		
		$query = $TYPO3_DB->exec_SELECTquery(
			'pid',
			'pages',
			'uid = '.$newsletter_uid
		);
		
		$parent = $TYPO3_DB->sql_fetch_assoc($query);
		$this->newsletter_uid = $newsletter_uid;
		$this->id = $parent['pid'];
	}
	
	/*
	 * get news uid to send
	 * 
	 * return	array		the news
	 */
	function get_ttnews_uid(){
		global $TYPO3_DB;
		
		$query = $TYPO3_DB->exec_SELECTquery(
			'*',
			'tt_news',
			'tx_dkdnews2directmail_senddmail = 1 '.
				'AND tx_dkdnews2directmail_dmail_uid IS NULL'.
				t3lib_BEfunc::BEenableFields('tt_news').
				t3lib_BEfunc::deleteClause('tt_news'),
			'',
			'tstamp ASC',
			'1'
		);
		return $TYPO3_DB->sql_fetch_assoc($query);
	}
}
?>
