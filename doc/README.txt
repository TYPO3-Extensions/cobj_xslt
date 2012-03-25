my.object = XSLT
my.object {

	source = [STRING + stdWrap]
	source.url = [URL]
	
	stylesheets {
		file1 = [STRING / PATH + stdWrap]
		file2 = [STRING / PATH + stdWrap]
	}
	
	registerPHPFunctions = [BOOLEAN / ARRAY]	
	registerPHPFunctions {
		10 = [object name :: function name]
	}
	
	debug = [BOOLEAN]	
	
	stdWrap [stdWrap]

}