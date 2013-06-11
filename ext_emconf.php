<?php

########################################################################
# Extension Manager/Repository config file for ext "cobj_xslt".
#
# Auto generated 19-11-2012 23:03
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
	'shy' => 0,
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
	'author_company' => 'Academy of Sciences and Literature | Mainz',
	'version' => '1.2.0',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-6.1.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'cobj_xpath' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:9:{s:13:"ChangeLog.txt";s:4:"e3c5";s:22:"class.tx_cobj_xslt.php";s:4:"eef1";s:16:"ext_autoload.php";s:4:"6f59";s:12:"ext_icon.gif";s:4:"08dd";s:17:"ext_localconf.php";s:4:"90d0";s:43:"Classes/ViewHelpers/TransformViewHelper.php";s:4:"3d40";s:14:"doc/manual.sxw";s:4:"34b7";s:14:"doc/README.txt";s:4:"92ca";s:13:"res/t3rss.xsl";s:4:"2530";}',
	'suggests' => array(
	),
);

?>