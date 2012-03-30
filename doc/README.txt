## Example TS configuration for cObj XSLT ##

my.object = XSLT
my.object {

	source = [STRING / stdWrap]
	source.url = [URL]

	transformations {

		1 {
		
			stylesheet = [STRING / PATH / stdWrap]
			stylesheet.url = [URL]
			
			transformToURI = [PATH]
			
			registerPHPFunctions = [BOOLEAN / ARRAY]
			registerPHPFunctions {
				10 = [object name :: function name]
			}

			setParameters {
				parametername {
					namespace = [STRING]
					value =  [STRING / stdWrap]
				}
			}

			removeParameters {
				parametername {
					namespace = [STRING]
				}
			}

			setProfiling = [BOOLEAN]

			stdWrap = [stdWrap to result of this transformation]
		}

		2 {
			[...]
		}
	}

	stdWrap [stdWrap to the whole object]
}