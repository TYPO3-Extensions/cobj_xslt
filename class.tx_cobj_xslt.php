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

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

/**
 * Extends tslib_cObj with XSLT cobject
 *
 * @access public
 * @author Torsten Schrade
 * @package TYPO3
 * @subpackage tx_cobj_xslt
 */
class tx_cobj_xslt {

	/**
	 * Renders the XSLT content object
	 * 
	 * @param string $name XSLT
	 * @param array	$conf TypoScript configuration of the cObj
	 * @param string $TSKey Key in the TypoScript array passed to this function
	 * @param object $oCObj	Reference to the parent class
	 * 
	 * @return string The transformed XML string
	 * 
	 */
	public function cObjGetSingleExt($name, array $conf, $TSkey, tslib_cobj &$oCObj) {

		$content = '';
		
			// Check if XML extensions are loaded at all
		if (!extension_loaded('SimpleXML') || !extension_loaded('libxml')) {
			$GLOBALS['TT']->setTSlogMessage('The PHP extensions SimpleXML, libxml amd DOM must be loaded.', 3);
			return $oCObj->stdWrap($content, $conf['stdWrap.']);
		} elseif (!extension_loaded('dom')) {
			$GLOBALS['TT']->setTSlogMessage('The PHP extension DOM must be loaded.', 3);
			return $oCObj->stdWrap($content, $conf['stdWrap.']);			
		}
		
			// Fetch XML data
		if (is_array($conf['source.']) || isset($conf['source'])) {
						
				// Get XML by external url
			if (isset($conf['source.']['url']) && t3lib_div::isValidUrl($conf['source.']['url'])) {
				
				$xmlsource = t3lib_div::getURL($conf['source.']['url'], 0, FALSE);
				if (!$xmlsource) $GLOBALS['TT']->setTSlogMessage('XML could not be fetched from URL.', 3);
				
				// Get XML with stdWrap
			} else {
				if ($conf['source.']['url']) unset($conf['source.']['url']);
				$xmlsource = $oCObj->stdWrap($conf['source'], $conf['source.']);				
			}		
		} else {
			$GLOBALS['TT']->setTSlogMessage('Source for XML is not configured.', 3);
		}
		
		if (!empty($xmlsource)) {		
			
				// Try to load a simpleXML object
			libxml_use_internal_errors(true);
			$xml = simplexml_load_string($xmlsource);
			
			if ($xml instanceof SimpleXMLElement) {
					
					// Import this into a DOM object						
				$domXML = dom_import_simplexml($xml);
							
				if ($domXML !== FALSE) {					
						// Set stylesheets (several possible, keys freely choosable, content will be 'piped' through all of them
					if (is_array($conf['stylesheets.']) && count($conf['stylesheets.']) > 0) {
						$xslStylesheets = array();
						foreach ($conf['stylesheets.'] as $key => $stylesheet) {					
							if (substr($key, -1) == '.') {							
								$value = $oCObj->stdWrap('', $conf['stylesheets.'][$key]);	
								$xslStylesheets[substr($key, 0, -1)] = $value;															
							} else {							
								$xslStylesheets[$key] = $stylesheet;
							}
						}
						ksort($xslStylesheets, SORT_REGULAR);					
					} else {
						$GLOBALS['TT']->setTSlogMessage('You must define some XSL stylesheets for processing the source', 3);
					}	
			
					if (count($xslStylesheets) > 0) {
						
						foreach ($xslStylesheets as $index => $stylesheet) {
	
								// Load current stylesheet...
							$xsl = t3lib_div::makeInstance('DOMDocument');
							
								// Load the styles from file
							$file = t3lib_div::getFileAbsFileName($stylesheet);							
							if (t3lib_div::isAbsPath($file) == TRUE) {
								$xsl->load($file);
								// Load the styles from an XML string
							} else {
								$xsl->loadXML($stylesheet);
							}							

								// Start XSLT processing				
							if ($xsl instanceof DOMDocument) {
									// Create a new processor and import current stylesheet
								$xslt = t3lib_div::makeInstance('XSLTProcessor');
								$xslt->importStylesheet($xsl);
								
									// Activate profiling if configured
									// @TODO: PHP 5.3 check
								if (isset($conf['setProfiling'])) {
									$profilingTempFile = t3lib_div::tempnam('xslt_profiler_');
									$xslt->setProfiling($profilingTempFile);
								}
								
									// Possibility to register PHP functions for usage within the XSL stylesheets
								if ($conf['registerPHPFunctions']) {
									
										// If only certain functions are specified, provide restricted registration
									if (is_array($conf['registerPHPFunctions.'])) {

											// Test if the functions can be called
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
										
											// Now register all the valid functions
										if (count($registeredFunctions) > 0) {
											$xslt->registerPHPFunctions($registeredFunctions);
										} else {
											$GLOBALS['TT']->setTSlogMessage('None of the functions specified in registerPHPFunctions were callable so nothing gets registered.', 3);
										}
										
										// If registerPHPFunctions was just set to 1, register all PHP functions without any restrictions
									} else {
										$xslt->registerPHPFunctions();
									}
								}
								
									// If there already was a result from a former transformation
								if ($result) {
																		
										// Load the formerly transformed XML into a new DOM object
									$formerResult = t3lib_div::makeInstance('DOMDocument');

										// If the output of the former transformation is valid XML, apply the next transformation
									if ($formerResult->loadXML($result) !== FALSE) {
										$result = $xslt->transformToXML($formerResult);
									} else {
										$GLOBALS['TT']->setTSlogMessage('The XML transformation with '.$index.' failed because the XML resulting from the former transformation could not be processed.', 3);
									}
									
									// First run, pass the already loaded DOM object
								} else {
									$result = $xslt->transformToXML($domXML);														
								}

									// Load profiling result from file into admin panel
								if (isset($conf['setProfiling'])) {
									$profilingInformation = str_replace(' ', ' ', t3lib_div::getURL($profilingTempFile));
									$GLOBALS['TT']->setTSlogMessage('Profiling result for XSL stylesheet ' . $index . ':' . "\n" . $profilingInformation, 1);
									t3lib_div::unlink_tempfile($profilingTempFile);
								}
									// Debug output if configured
								if ($conf['debug'] === 1) t3lib_utility_Debug::debug($result);
								
							} else {
								$GLOBALS['TT']->setTSlogMessage('The stylesheet '.$index.' could not be loaded or contained errors.', 3);								
							}
						}
						
						$content = $result;
						
// transformToURI ?
						
						return $oCObj->stdWrap($content, $conf['stdWrap.']);
						
					} else {
						$GLOBALS['TT']->setTSlogMessage('There were no XSL stylesheets to process the content.', 3);								
					}							
					
				} else {
					$GLOBALS['TT']->setTSlogMessage('XML could not be converted to a DOM object.', 3);
				}
				
			} else {			
				$errors = libxml_get_errors();
				foreach ($errors as $error) {
					$GLOBALS['TT']->setTSlogMessage('XML Problem: '.$this->getXmlErrorCode($error), 3);
				}
				libxml_clear_errors();
			}			
	
		} else {
			$GLOBALS['TT']->setTSlogMessage('The configured XML source did not return any data.', 3);
		}
		
		return $oCObj->stdWrap($content, $conf['stdWrap.']);
	}

	/**
	 * Static wrapper function for calling TypoScript cObjects within XSL stylesheets, e.g. by doing 
	 * <xsl:value-of select="php:functionString('tx_cobj_xslt::typoscriptObjectPath', 'lib.my.object', YOUR XPATH)"/>
	 * registerPHPfunctions has to be set to true within the XSLT configuration for this to work
	 * 
	 * @param string $key The setup key to be applied from global TypoScript scope
	 * @param mixed	$data The matches of the XPATH expression within the XSL stylesheet
	 * 
	 * @return string The rendered TypoScript object
	 */
	public static function typoscriptObjectPath($key, $data) {
		
			// Set data to the current value - first possibility is an incoming array of DOMElements (if called with php:function in the XSL styleheet)
		if (is_array($data)) {
			$currentVal = '';
			// Accumulate all matches to a XML string and hand it over for TypoScript processing
			foreach ($data as $match) {
				$currentVal .= $match->C14N(0,1);
				$GLOBALS['TSFE']->cObj->setCurrentVal($currentVal);
			}
			// Second possibility is an incoming string (if called with php:functionString in the XSL stylesheet)
		} else {
			$GLOBALS['TSFE']->cObj->setCurrentVal($data);
		}
		
			// Get TypoScript configuration from global scope
		$tsParser = t3lib_div::makeInstance('t3lib_tsparser');
		$configuration = $tsParser->getVal($key, $GLOBALS['TSFE']->tmpl->setup);
		
			// Process and return a TS object
		if (is_array($configuration)) {
			return $GLOBALS['TSFE']->cObj->cObjGetSingle($configuration[0], $configuration[1]);
		} else {
			$GLOBALS['TT']->setTSlogMessage('The TypoScript key '.$key.' referenced in the XSL stylesheet could not be found', 3);
			return '';
		}
	}	
	
	/**
	 * Returns XML error codes for the TSFE admin panel.
	 * Function inspired by http://www.php.net/manual/en/function.libxml-get-errors.php
	 *
	 * @param LibXMLError $error
	 * @return string
	 */
	private function getXmlErrorCode(LibXMLError $error) {
		$errormessage = '';

		switch ($error->level) {
			case LIBXML_ERR_WARNING:
				$errormessage .= 'Warning ' . $error->code . ': ';
				break;
			case LIBXML_ERR_ERROR:
				$errormessage .= 'Error ' . $error->code . ': ';
				break;
			case LIBXML_ERR_FATAL:
				$errormessage .= 'Fatal error ' . $error->code . ': ';
				break;
		}

		$errormessage .= trim($error->message) . ' - Line: ' . $error->line . ', Column:' . $error->column;

		if ($error->file) {
			$errormessage .= ' - File: ' . $error->file;
		}

		return $errormessage;
	}
	
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/cobj_xslt/class.tx_cobj_xslt.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/cobj_xslt/class.tx_cobj_xslt.php']);
}
?>