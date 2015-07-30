<?php
return array(
	'on' => true,
	'type' => 'apc',	// apc or file - if apc is selected but unavailable, it falls back to file caching
	'location' => 'cache', // the location within the main maverick directory
	'duration' => 30,	// duration that cache should be used in seconds
);