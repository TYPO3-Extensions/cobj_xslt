my.object = XSLT
my.object {

	source = [STRING / stdWrap]
	source.url = [URL]
	
	transformations {
		
		1 {
		
			stylesheet = [STRING / PATH / stdWrap]
			
			transformToURI = [PATH]
			
			registerPHPFunctions = [BOOLEAN / ARRAY]	
			registerPHPFunctions {
				10 = [object name :: function name]
			}
			
			setParameters {
				namespace = [STRING / stdWrap]
				name = [STRING / stdWrap]
				value = [STRING / stdWrap]
			}
			
			removeParameters {
			}
			
			setProfiling = [BOOLEAN]
			
			stdWrap = [stdWrap to content of transformation]
		}
		
		2 {
			[...]
		}
	}
	
	stdWrap [stdWrap to the whole object]

}