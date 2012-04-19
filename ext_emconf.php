<?php

########################################################################
# Extension Manager/Repository config file for ext "cobj_xslt".
#
# Auto generated 19-04-2012 22:28
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'XSLT Content Object',
	'description' => 'Extends tslib_cObj with a new cObject XSLT for flexible XML transformations with XSL and TypoScript.',
	'category' => 'fe',
	'author' => 'Torsten Schrade',
	'author_email' => 'schradt@uni-mainz.de',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => 'Academy of Sciences and Literature | Mainz',
	'version' => '1.1.1',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:8:{s:13:"ChangeLog.txt";s:4:"4992";s:22:"class.tx_cobj_xslt.php";s:4:"1046";s:16:"ext_autoload.php";s:4:"6f59";s:12:"ext_icon.gif";s:4:"08dd";s:17:"ext_localconf.php";s:4:"90d0";s:14:"doc/README.txt";s:4:"92ca";s:14:"doc/manual.sxw";s:4:"78b2";s:13:"res/t3rss.xsl";s:4:"2530";}',
	'suggests' => array(
	),
);

?>