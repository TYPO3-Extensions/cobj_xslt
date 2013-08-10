

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


ChangeLog
---------

+----------------+---------------------------------------------------------------+
| Version        | Changes                                                       |
+================+===============================================================+
| 1.3.0          | Feature release                                               |
|                |                                                               |
|                | - Version compatibility set to 4.5.0-6.1.99                   |
|                |                                                               |
|                | - ReST based manual                                           |
|                |                                                               |
|                | - stdWrap for transformToURI property                         |
|                |                                                               |
|                | - new property supressReturn to suppress output if a          |
|                | transformation should only be written to file                 |
+----------------+---------------------------------------------------------------+
| 1.2.0          | Feature release                                               |
|                |                                                               |
|                | - New XSLT view helper for Fluid templates                    |
|                |                                                               |
|                | - New tutorial XSLT and FLUIDTEMPLATE                         |
|                |                                                               |
|                | - New tutorial about <xslt> TypoTag                           |
|                |                                                               |
|                | - New loading mechanism for XSL stylesheets that now supports |
|                | all types (stdWrap,string,path,url)                           |
+----------------+---------------------------------------------------------------+
| 1.1.1          | Maintenance release                                           |
|                |                                                               |
|                | - Loading XML files from a path could fail sometimes          |
+----------------+---------------------------------------------------------------+
| 1.1.0          | Feature release                                               |
|                |                                                               |
|                | - TypoScript change: The former 'source.url' and              |
|                |'stylesheet.url' properties are dropped and the functionality  |
|                |is now fused into the parent property. This makes it possible  |
|                |to use stdWrap for constructing URLs to the resources          |
+----------------+---------------------------------------------------------------+
| 1.0.0          | First public version                                          |
+----------------+---------------------------------------------------------------+