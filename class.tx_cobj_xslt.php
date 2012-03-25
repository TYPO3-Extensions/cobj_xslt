<?php
/***************************************************************
 *  Copyright notice
 *
 *  Copyright (c) 2012 Torsten Schrade <schradt@uni-mainz.de>
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

if (!defined ('TYPO3_MODE'))
	die ('Access denied.');

/**
 * Extends tslib_cObj with an XSLT cobject
 * 
 * @author Torsten Schrade
 * @package TYPO3
 * @subpackage tx_cobj_xslt
 * @access public
 */
class tx_cobj_xslt {

	/**
	 * Rendering function for the XSLT content object
	 * 
	 * @param 	string	XSLT
	 * @param	array	TypoScript configuration of the cObj
	 * @param	string	Key in the TypoScript array passed to this function
	 * @param	object	Reference to the parent class
	 * 
	 * @return	void
	 * 
	 */
	public function cObjGetSingleExt($name, $conf, $TSkey, &$oCObj) {

		$content = '';
		
		// fetch xml data
		if (is_array($conf['source.']) || isset($conf['source'])) {
						
			// get XML by url
			if (isset($conf['source.']['url']) && t3lib_div::isValidUrl($conf['source.']['url'])) {
				
				$xmlsource = t3lib_div::getURL($conf['source.']['url'], 0, FALSE);
				if (!$xmlsource) $GLOBALS['TT']->setTSlogMessage('XML could not be fetched from URL.', 3);
				
			// get XML with stdWrap
			} else {
				if ($conf['source.']['url']) unset($conf['source.']['url']);
				$xmlsource = $oCObj->stdWrap($conf['source'], $conf['source.']);				
			}		
		} else {
			$GLOBALS['TT']->setTSlogMessage('Source for XML is not configured.', 3);
			return $oCObj->stdWrap($content, $conf['stdWrap.']);
		}
		
		if ($xmlsource) {		
			
			// try to load a simpleXML object
			libxml_use_internal_errors(true);
			$xml = simplexml_load_string($xmlsource);
			
			if ($xml instanceof SimpleXMLElement) {
					
				// import this into a DOM object						
				$domXML = dom_import_simplexml($xml);
							
				if ($domXML !== FALSE) {
					
					// set stylesheets (several possible, content will be 'piped' through all of them
					if (is_array($conf['stylesheets.']) && count($conf['stylesheets.']) > 0) {
						$xsltStylesheets = array();
						foreach ($conf['stylesheets.'] as $key => $stylesheet) {
							$file = $oCObj->stdWrap($stylesheet, $conf[$key.'.']);
							if (t3lib_div::validPathStr($xsltStylesheet)) $xsltStylesheets[$key] = $file;
						}
						ksort($xsltStylesheets, SORT_REGULAR);					
					} else {
						$GLOBALS['TT']->setTSlogMessage('You must define some XSL stylesheets for processing the source', 3);
					}				
			
					if (count($xsltStylesheets) > 0) {
						
						foreach ($xsltStylesheets as $stylesheet) {
							
							// load current stylesheet
							$xsl = t3lib_div::makeInstance('DOMDocument');
							$xsl->load($stylesheet);
												
							if ($xsl instanceof DOMDocument) {
								// create a new processor and import current stylesheet
								$xslt = t3lib_div::makeInstance('XSLTProcessor');
								$xslt->importStylesheet($xsl);
								
								// possibility to register PHP functions for usage within the XSL stylesheets
								if ($conf['registerPHPFunctions']) {
									
									// if certain functions are specified, provide restricted registration
									if (is_array($conf['registerPHPFunctions.'])) {

										// test if functions can be called
										$registeredFunctions = array();
										foreach ($conf['registerPHPFunctions.'] as $key => $function) {
											if (strpos($function, '::')) {
												$objectAndMethod = t3lib_div::trimExplode('::', $function);
												if (is_callable($objectAndMethod[0], $objectAndMethod[1])) {
													$registeredFunctions[] = $function;
												}
											} elseif (is_callable($function)) {
												$registeredFunctions[] = $function;
											} else {
												$GLOBALS['TT']->setTSlogMessage('Tried to register a function '.$function.' that is not callable.', 3);
											}
										}
										
										// now register all valid functions
										if (count($registeredFunctions) > 0) {
											$xslt->registerPHPFunctions($registeredFunctions);
										} else {
											$GLOBALS['TT']->setTSlogMessage('None of the functions specified in registerPHPFunctions were callable so nothing gets registered.', 3);
										}
										
									// if the property was just set to 1, register all PHP functions without any restrictions
									} else {
										$xslt->registerPHPFunctions();
									}
								}
								
								// if there already was a result from a former transformation...
								if ($result) {
									// load the transformed XML into a new DOM object
									$formerResult = t3lib_div::makeInstance('DOMDocument');
									$formerResult->loadXML($result);
									// and do a new transformation with the current stylesheet
									$result = $xslt->transformToXML($formerResult);
								// if this is the first run pass the already loaded DOM object
								} else {
									$result = $xslt->transformToXML($domXML);								
								}
								
								// debug output
								if (isset($conf['debug'])) t3lib_div::debug($result);
								
							} else {
								$GLOBALS['TT']->setTSlogMessage('The stylesheet '.$stylesheet.' could not be loaded.', 3);								
							}
						}
						
						$content = $result;
						return $oCObj->stdWrap($content, $conf['stdWrap.']);
						
					} else {
						$GLOBALS['TT']->setTSlogMessage('There were no XSL stylesheets to process the content.', 3);
						return $oCObj->stdWrap($content, $conf['stdWrap.']);								
					}							
					
				} else {
					$GLOBALS['TT']->setTSlogMessage('XML could not be converted to DOM object.', 3);
					return $oCObj->stdWrap($content, $conf['stdWrap.']);
				}
				
			} else {			
				$errors = libxml_get_errors();
				foreach ($errors as $error) {
					$GLOBALS['TT']->setTSlogMessage('XML Problem: '.$this->display_xml_error($error, $xml), 3);
				}
				libxml_clear_errors();
			}			
	
		} else {
			$GLOBALS['TT']->setTSlogMessage('The configured XML source did not return any data.', 3);
		}
	}

	/**
	 * Static wrapper function for calling TypoScript cObjects within XSL stylesheets, e.g. by doing 
	 * <xsl:value-of select="php:functionString('tx_cobj_xslt::typoscriptObjectPath', 'lib.my.object', YOUR XPATH)"/>
	 * registerPHPfunctions has to be set to true within the XSLT configuration for this to work
	 * 
	 * @param 	string	The setup key to be applied from global TypoScript scope
	 * @param	mixed	The matches of the XPATH expression within the XSL stylesheet
	 * @return	string	The rendered TypoScript object
	 */
	public static function typoscriptObjectPath($key, $data) {
		
		// set current value - first possibility is an array of DOMElements (if called with php:function)
		// for this scenario accumulate all matches to an xml string and hand this over to TypoScript processing
		if (is_array($data)) {
			$currentVal = '';
			foreach ($data as $match) {
				$currentVal .= $match->C14N(0,1);
				$GLOBALS['TSFE']->cObj->setCurrentVal($currentVal);
			}
		// second possibility is an incoming string (if called with php:functionString)
		} else {
			$GLOBALS['TSFE']->cObj->setCurrentVal($data);
		}
		
		// get TypoScript configuration from global tmpl scope
		$tsParser = t3lib_div::makeInstance('t3lib_tsparser');
		$configuration = $tsParser->getVal($key, $GLOBALS['TSFE']->tmpl->setup);
		
		// process and return TS object
		if (is_array($configuration)) {
			return $GLOBALS['TSFE']->cObj->cObjGetSingle($configuration[0], $configuration[1]);
		} else {
			$GLOBALS['TT']->setTSlogMessage('The TypoScript key '.$key.' referenced in the XSL stylesheet could not be found', 3);
			return '';
		}
	}	
	
	/**
	 * Returns XML error codes for the TSFE admin panel. Function inspired by http://www.php.net/manual/en/function.libxml-get-errors.php
	 *
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	private function display_xml_error($error, $xml) {

		switch ($error->level) {
			case LIBXML_ERR_WARNING:
				$errormessage .= 'Warning '.$error->code.': ';
			break;
			case LIBXML_ERR_ERROR:
				$errormessage .= 'Error '.$error->code.': ';
			break;
			case LIBXML_ERR_FATAL:
				$errormessage .= 'Fatal error '.$error->code.': ';
			break;
		}

		$errormessage .= trim($error->message).' - Line: '.$error->line.', Column:'.$error->column;

		if ($error->file) {
			$errormessage .= ' - File: '.$error->file;
		}

	    return $errormessage;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cobj_xslt/class.tx_cobj_xslt.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cobj_xslt/class.tx_cobj_xslt.php']);
}
?>