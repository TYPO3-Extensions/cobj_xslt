

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


Other interesting use cases
^^^^^^^^^^^^^^^^^^^^^^^^^^^

Many more ideas spring to mind where you could make use of the XSLT
object. Here are some:

Transform flexform content. Might come in handy if you use
`Templavoila
<http://typo3.org/extensions/repository/view/templavoila/current/>`_ ,
`FLUX <http://typo3.org/extensions/repository/view/flux/current/>`_ or
any other XML based content format.

Access and display XML content provided over REST APIs on your TYPO3
website.

Use it together with the `XPATH content object
<http://typo3.org/extensions/repository/view/cobj-xpath>`_ to select
and then transform chunks of XML structures.

Store your TYPO3 content as XML files, for example by defining another
pagetype that outputs XML which is then written to disk with the XSLTs
transformToURI property.

And much more...

If you have ideas or wishes for some more tutorials or features, just
drop a line.

