1. Create a BE User "_cli_dkd_news2direct", which have the permission to select and modify tables of direct_mail, tt_news and your newsletter recipients table (tt_address or fe_user)

2. after installing the dkd_news2directmail extension, you'll find a new module (web > News 2 Directmail). Upon opening this module you'll have to choose a SysFolder containing your directmail.

3. Next you can put in some configuration parameters:
	- recipient list -> you can only put ONE recipient ID, which you have created with directmail extension (see Directmail manual how to do this)
	- time interval -> is the time interval between the news2directmail cli script compiling the news and directmail cli script start sending the newsletter
	- Domain of internal link -> because the base URL of the link is not known in the cli mode, you have to set it here.
	
4. Next you have to call the cli script from the command line. the command is:
	
	/absolute_path_to_your_site/typo3conf/ext/dkd_news2directmail/cli/cli_dkdnews2directmail.php xxx
	
	where xxx is the page ID, which contains tt_news extension displaying the news detail.
	

To test if your page is correctly configured to show tt_news, try to open it with browser
	www.example.com/index.php?id=xxx&tx_ttnews[tt_news]=yyy
where xxx is the page ID and yyy is the tt_news uid.