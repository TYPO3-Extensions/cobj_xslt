<?php

########################################################################
# Extension Manager/Repository config file for ext "cobj_xslt".
#
# Auto generated 04-01-2012 17:54
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'XSLT Content Object',
	'description' => 'Extends tslib_cObj with a new cObject XSLT for doing transformations on XML input with TypoScript.',
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
	'version' => '0.0.0',
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
	'_md5_values_when_last_written' => 'a:3:{s:28:"class.tx_cobj_headerdata.php";s:4:"625d";s:12:"ext_icon.gif";s:4:"bbb3";s:17:"ext_localconf.php";s:4:"58b2";}',
	'suggests' => array(
	),
);

?>