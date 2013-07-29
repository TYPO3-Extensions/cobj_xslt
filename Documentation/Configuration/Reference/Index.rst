

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Reference
^^^^^^^^^


.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         source
   
   Data type
         string
         
         \+ stdWrap
   
   Description
         This fetches the XML data from a source. Can be a XML string, a field
         in the database containing XML, a file (path or via TypoScript FILE
         cObject) or an external resource.
         
         **Example (field):**
         
         ::
         
            page.10 = XSLT
            page.10 {
               source.data = page : my_xml_field
               [...]
            }
         
         Fetches the XML from the field 'my\_xml\_field' of the current page
         record.
         
         **Example (stdWrap / FILE):**
         
         ::
         
            page.10 = XSLT
            page.10 {
               source.cObject = FILE
               source.cObject.file = fileadmin/myfile.xml
               [...]
            }
         
         This fetches the XML from a file included with TypoScript's FILE
         content object. Please note: Due to FILE's internal settings, the data
         can't be larger than 1024kb. See TSref.
         
         **Example (external):**
         
         ::
         
            page.10 = XSLT
            page.10 {
               source = http://news.typo3.org/rss.xml
               [...]
            }
         
         This draws the XML from an external source. It can be an URL like
         above or an external file resource of any size.
   
   Default


.. container:: table-row

   Property
         transformations.[1,2,3...]
   
   Data type
         array
   
   Description
         This configuration array contains all transformations in index =>
         settings notation. During rendering, the content object pipes the XML
         data through all configured transformations in numeric order. See the
         subproperties for configuration details.
         
         **Example:**
         
         ::
         
            page.10 = XSLT
            page.10 {
               source.data = page : my_xml_field
               transformations {
                  1 {
                      stylesheet = fileadmin/my.xsl
                      setProfiling = 1
                      [...]
                  }
               }
            }
   
   Default


.. container:: table-row

   Property
         [i].stylesheet
   
   Data type
         string
         
         \+ stdWrap
   
   Description
         This property sets the XSL stylesheet applied to the current
         transformation. Stylesheets can be loaded from a string, a path, with
         stdWrap or from an external resource.
         
         **Example (string):**
         
         ::
         
            transformations.1 {
               stylesheet (
            <xsl:stylesheet version="1.0" 
            xmlns:xsl="http://www.w3.org/1999/XSL/Transform"      
            <xsl:output method="html" encoding="utf8" indent="yes"/>
            <xsl:template match="item">             
            <p><xsl:value-of select="description"/></p>
            </xsl:template>
            </xsl:stylesheet>
            )
               [...]
            }
         
         **Example (path):**
         
         ::
         
            transformations.1 {
               stylesheet = fileadmin/my.xsl
            }
         
         **Example (stdWrap):**
         
         ::
         
            transformations.1 {
               stylesheet.cObject = FILE
               stylesheet.cObject.file = fileadmin/my.xsl
               [...]
            }
         
         **Example (external):**
         
         ::
         
            transformations.1 {
               stylesheet = http://example.org/external.xsl
               [...]
            }
   
   Default


.. container:: table-row

   Property
         [i].transformToURI
   
   Data type
         path
   
   Description
         If a valid filepath is set, the result of the current transformation
         is not only returned but also written to a file. This is very useful
         for debugging multi-transformation scenarios as well as for providing
         transformed XML resources that can then be picked up by following XSLT
         objects.
         
         **Example:**
         
         ::
         
            transformations.1 {
               transformToURI = fileadmin/transformation-1.xml
               [...]
            }
   
   Default


.. container:: table-row

   Property
         [i].registerPHPFunctions
   
   Data type
         Boolean+ array
   
   Description
         The use of PHP functions within XSL stylesheets should be considered
         carefully. When set however, this configuration property provides
         really powerful possibilities.  **Note:** You must declare the PHP
         namespace in your XSL stylesheet: xmlns:php=" `http://php.net/xsl
         <http://php.net/xsl>`_ ".
         
         If this property is set to 1, all available PHP functions in your
         environment can be called from your XSL stylesheets. This can be
         restricted by providing specific function names in a key => name
         notation below the property.
         
         **Example:**
         
         ::
         
            transformations.1 {
                registerPHPFunctions = 1
                registerPHPFunctions {
                   1 = strtoupper
                }
            }
         
         This activates the PHP function registration and restricts the calling
         of functions to strtoupper() for the current stylesheet. In your XSL
         stylesheet you can then do:
         
         ::
         
            <h1><xsl:value-of select="php:functionString('strtoupper', title)"/></h1>
         
         This will transform the content of the matched tags to uppercase.
         
         **typoscriptObjectPath:**
         
         In addition to calling standard PHP functions, the XSLT object
         provides the possibility to work with TypoScript cObjects from your
         XSL stylesheets. This functionality is quite similar to the
         <f:cObject> viewhelper in FLUID. For activation, you need to register
         the static typoscriptObjectPath function of this extension for the
         current stylesheet:
         
         ::
         
            transformations.1 {
                registerPHPFunctions = 1
                registerPHPFunctions {
                   1 = tx_cobj_xslt::typoscriptObjectPath
                }
            }
         
         In your stylesheet, you can then do:
         
         ::
         
            <xsl:value-of select="php:functionString('tx_cobj_xslt::typoscriptObjectPath', 'lib.my.cObject', title)"/>
         
         This will submit the matches found by the stylesheet to lib.my.cObject
         for further processing.
   
   Default


.. container:: table-row

   Property
         [i].setParameters
   
   Data type
         array
         
         \+ subproperties
   
   Description
         Makes it possible to set parameters for the current stylesheet from
         TypoScript. The syntax is:
         
         ::
         
            transformations.1 {
                setParameters {
                   your_parameter_name {
                       namespace = your_namespace
                       value = your_value
                   }
                }
            }
         
         The keys of the array are the parameter names. Below each parameter
         name a namespace (string) and a value can be set. The  **.value**
         subproperty has stdWrap capabilities.
         
         **Example:**
         
         ::
         
            transformations.1 {
                setParameters {
                   pagetitle.value.data = page:title
                }
            }
         
         And in your XSL stylesheet:
         
         ::
         
            <xsl:param name="pagetitle" select="default"/>
            <h1><xsl:value-of select="$pagetitle"/></h1>
   
   Default


.. container:: table-row

   Property
         [i].removeParameters
   
   Data type
         array
         
         \+ subproperties
   
   Description
         Remove formerly set parameters from the stylesheet. The syntax is:
         
         ::
         
            transformations.1 {
                removeParameters {
                   your_parameter_name = 1
                   your_parameter_name {
                       namespace = your_namespace
                   }
                }
            }
         
         The namespace property is optional. Parameters to remove must be set
         to 1.
   
   Default


.. container:: table-row

   Property
         [i].setProfiling
   
   Data type
         boolean
   
   Description
         This activates profiling for the current stylesheet. The profiling
         information is written to the TSFE admin panel.
   
   Default


.. container:: table-row

   Property
         [i].stdWrap
   
   Data type
         stdWrap
   
   Description
         stdWrap properties for the current transformation.
   
   Default


.. container:: table-row

   Property
         stdWrap
   
   Data type
         stdWrap
   
   Description
         stdWrap properties for the whole XSLT cObject
         
         ::
         
            page.10 = XSLT
            page.10 {
               
               [...]
            
               stdWrap {
                  outerWrap = <code>|</code>
               }
            }
   
   Default


.. ###### END~OF~TABLE ######


[cObject:XSLT]

