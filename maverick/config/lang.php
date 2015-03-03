<?php
return array(
	
	// see https://msdn.microsoft.com/en-gb/library/ee825488%28v=cs.20%29.aspx for a full list of language cultures
	// this *is* case-sensitive and match the culture exactly for best interoperability with translating tools
	// this will be used as the defaul language culture
	// if this is set to empty, then it will default to en-GB from within the base maverick class itself, you should not specify the .utf8 ending for this!
	// finally, you can use either hyphens or the underscore here, as hyphens will be replaced by underscores internally
	'default' => 'en-GB',
	
	'active' => true,	// set this to true if you want to make use of i18n translation features in your application
);
