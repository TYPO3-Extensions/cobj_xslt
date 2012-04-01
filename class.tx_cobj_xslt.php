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
	 * @var DOMDocument $xsl Instance for loading a XSL stylesheet during a transformation run
	 */
	var $xsl;

	/**
	 * 
	 * @var XSLTProcessor $xslt XSLT Processor instance during a transformation run
	 */
	var $xslt;

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

			// Check if necessary XML extensions are loaded with PHP
		if (extension_loaded('SimpleXML') && extension_loaded('libxml') && extension_loaded('dom') && extension_loaded('xsl')) {

				// Fetch XML data - if source is neither a valid url nor a path, its considered a XML string
			if (isset($conf['source']) || is_array($conf['source.'])) {
					// First process the source property with stdWrap
				$xmlsource = $oCObj->stdWrap($conf['source'], $conf['source.']);
					// Fetch by URL
				if (t3lib_div::isValidUrl($xmlsource)) {
					$xmlsource = t3lib_div::getURL($xmlsource, 0, FALSE);
					// Fetch by absolute path
				} elseif ($path = t3lib_div::getFileAbsFileName($xmlsource)) {
					$xmlsource = t3lib_div::getURL($path, 0, FALSE);
				}
			} else {
				$GLOBALS['TT']->setTSlogMessage('Source for XML is not configured.', 3);
			}

				// start XSLT transformation
			if (!empty($xmlsource)) {

					// Try to load a simpleXML object; this makes validation of XML and error handling easy
				libxml_use_internal_errors(true);
				$xml = simplexml_load_string($xmlsource);

				if ($xml instanceof SimpleXMLElement) {

						// Import the simpleXML object into a DOM object (necessary for XSLT support)
					$domXML = dom_import_simplexml($xml);

						// If it worked and transformations are configured
					if ($domXML instanceof DOMElement && count($conf['transformations.']) > 0) {

							// Initialize transformation result
						$result = '';

							// Sort transformation configuration
						ksort($conf['transformations.']);

						foreach ($conf['transformations.'] as $index => $transformation) {

								// Prepare new XSL DOM for this run
							$this->xsl = t3lib_div::makeInstance('DOMDocument');

								// Get current stylesheet with stdWrap
							$stylesheet = $oCObj->stdWrap($transformation['stylesheet'], $transformation['stylesheet.']);

								// Load XSL styles either from url or path/string
							if (t3lib_div::isValidUrl($stylesheet)) {
								$xslLoaded = $this->xsl->loadXML(t3lib_div::getURL($transformation['stylesheet.']['url'], 0, FALSE));
							} else {
								$xslLoaded = $this->loadXslStylesheet($stylesheet);
							}
								// If the loading wasn't successfull, skip this run
							if ($xslLoaded === FALSE) {
								$GLOBALS['TT']->setTSlogMessage('No XSL stylesheet set for transformation '.$index.'', 3);
								continue;
							}

								// Start XSLT processing
							if ($this->xsl instanceof DOMDocument) {

									// Create a new XSLT processor and import the current stylesheet
								$this->xslt = t3lib_div::makeInstance('XSLTProcessor');
								$this->xslt->importStylesheet($this->xsl);

									// Possibility to register PHP functions for use within the XSL stylesheet
								if (isset($transformation['registerPHPFunctions'])) {

										// If particular functions are configured, provide restricted registration
									if (is_array($transformation['registerPHPFunctions.'])) {

											// Test if the functions can be called
										$registeredFunctions = array();
										foreach ($transformation['registerPHPFunctions.'] as $key => $function) {
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

											// Now register all valid functions
										if (count($registeredFunctions) > 0) {
											$this->xslt->registerPHPFunctions($registeredFunctions);
										} else {
											$GLOBALS['TT']->setTSlogMessage('None of the functions specified in registerPHPFunctions were callable so nothing gets registered.', 3);
										}

										// If registerPHPFunctions was just set to 1, register all PHP functions without any restrictions
									} else {
										$this->xslt->registerPHPFunctions();
									}
								}

									// Set parameters for this stylesheet
								if (is_array($transformation['setParameters.'])) {

									foreach ($transformation['setParameters.'] as $parameter => $value) {
										$paramNamespace = '';
										if (substr($parameter, -1) == '.' && is_array($value) === TRUE) {
											$paramName = substr($parameter, 0, -1);
											$paramNamespace = $value['namespace'];
											$paramValue = $oCObj->stdWrap($value['value'], $value['value.']);
											$this->xslt->setParameter($paramNamespace, $paramName, $paramValue);
										} else {
											$GLOBALS['TT']->setTSlogMessage('Settig the parameter ' . $parameter . ' failed due to misconfiguration', 3);
										}
									}
								}

									// Remove parameters from this stylesheet
								if (is_array($transformation['removeParameters.'])) {

									foreach ($transformation['removeParameters.'] as $parameter => $value) {
										$paramNamespace = '';
										if (substr($parameter, -1) == '.' && is_array($value) === TRUE) {
											$paramName = substr($parameter, 0, -1);
											if (isset($value['namespace'])) {
												$paramNamespace = $value['namespace'];
											}
											$this->xslt->removeParameter($paramNamespace, $paramName);
										} elseif (substr($parameter, -1) !== '.' && (int) $value > 0) {
											$paramName = $parameter;
											$this->xslt->removeParameter('', $paramName);
										}
									}
								}

									// Activate profiling if configured
									// @TODO: PHP 5.3 check to warn if still used with TYPO3 4.5 and PHP 5.2
								if (isset($transformation['setProfiling'])) {
									$profilingTempFile = t3lib_div::tempnam('xslt_profiler_');
									$this->xslt->setProfiling($profilingTempFile);
								}

									// If there is a result from a former transformation of the source
								if ($result !== '') {

										// Load the transformed XML from the last run into a new DOM object
									$formerResult = t3lib_div::makeInstance('DOMDocument');

										// If the XML is valid, apply the current XSL transformation
									if ($formerResult->loadXML($result) !== FALSE) {
										$result = $this->xslt->transformToXML($formerResult);
									} else {
										$GLOBALS['TT']->setTSlogMessage('The XML transformation with '.$index.' failed because the XML resulting from the former transformation could not be processed.', 3);
									}
									
									// First run, process the loaded source
								} else {
									$result = $this->xslt->transformToXML($domXML);
								}

									// Load the profiling result from temporary file into admin panel
								if (isset($transformation['setProfiling'])) {
									$profilingInformation = str_replace(' ', ' ', t3lib_div::getURL($profilingTempFile));
									$GLOBALS['TT']->setTSlogMessage('Profiling result for XSL transformation ' . $index . "\n" . $profilingInformation, 1);
									t3lib_div::unlink_tempfile($profilingTempFile);
								}

									// stdWrap of this runs transformation
								if ($transformation['stdWrap.']) {
									$result = $oCObj->stdWrap($result, $transformation['stdWrap.']);
								}

									// If set write the result of this transformation to a file
									// Use TYPO3 functions here (and not transformToURI) so that the stdWrap output can be included
								if ($resultFile = t3lib_div::getFileAbsFileName($transformation['transformToURI'])) {
									t3lib_div::writeFile($resultFile, $result);
								}
								
							} else {
								$GLOBALS['TT']->setTSlogMessage('The stylesheet '.$index.' could not be loaded or contained errors.', 3);
							}
						}
						
						// Set content to final result of all transformations
						$content = $result;

					} else {
						$GLOBALS['TT']->setTSlogMessage('XML could not be converted to a DOM object or no transformations were configured.', 3);
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

		} else {
			$GLOBALS['TT']->setTSlogMessage('The PHP extensions SimpleXML, dom, xsl and libxml must be loaded.', 3);
		}

		return $oCObj->stdWrap($content, $conf['stdWrap.']);
	}

	/**
	 * Static wrapper function for calling TypoScript cObjects from XSL stylesheets, e.g. by doing 
	 * <xsl:value-of select="php:functionString('tx_cobj_xslt::typoscriptObjectPath', 'lib.my.object', YOUR XPATH)"/>
	 * registerPHPfunctions must be set in the configuration of the cObj for this to work
	 * 
	 * @param string $key The setup key to be applied from the global TypoScript scope
	 * @param mixed	$data The matches of the XPATH expression from the XSL stylesheet
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
	 * Tries to loads a XSL stylesheet from a path or a string
	 * 
	 * @param string $stylesheet The XSL styles as string or a file path
	 * @return boolean
	 */
	private function loadXslStylesheet($stylesheet) {		
		$path = t3lib_div::getFileAbsFileName($stylesheet);
		if (t3lib_div::isAbsPath($path) === TRUE) {
			return $this->xsl->load($path);
		} else {
			return $this->xsl->loadXML($stylesheet);
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