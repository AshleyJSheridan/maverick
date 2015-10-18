<?php
namespace helpers\html;

/**
 * a helper class for rendering some of the HTML used in the cms
 * @package Maverick
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class cms extends \helpers\html\html
{
	/**
	 * gets a list of all available elements in the snippets directory that follow a prescribed pattern, and caches it for use later if it's needed
	 * @todo maybe add a way for this to become more flexible and allow for arbitrary element sets to be matched and returned
	 * @param string $type    the type of element to return a list of
	 * @param array  $extra   this can be used to include any extra bits that can be used in an individual case
	 * @param bool   $wrapped whether or not the returned array wraps each element in <option> tags taken from the select_option snippet
	 * @return array
	 */
	public static function get_available_elements($type, $extra, $wrapped=true)
	{
		$h = html::getInstance();
		
		$elements = array();
		
		// determine the right glob to use for the requested set of elements
		switch($type)
		{
			case 'form':
				$glob = 'input_*.php';
				break;
		}
		
		// calculate the substr offset for this set of elements - it should be the number of characters up until the first * (of which there should be only 1)
		$start_offset = strpos($glob, '*');
		$end_offset = $start_offset + 4;	// the +4 magic value is just the .php extension, which should always be 4 characters as it should always be .php
		
		// pull from the object cache, or create it
		if(isset($h->cached_snippets["elements_$type"]))
			$elements = $h->cached_snippets["elements_$type"];
		else
		{
			foreach(glob(MAVERICK_VIEWSDIR . 'cms/includes/snippets/' . $glob) as $element)
				$elements[] = substr(basename($element), $start_offset, strlen(basename($element) )-$end_offset );

			$elements = $h->cached_snippets["elements_$type"] = $elements;
		}
		
		// perform the replacements - this isn't done earlier because we don't want to cache replaced versions, as snippets will often change because of these replacements
		if($wrapped)
		{
			foreach($elements as $element)
			{
				$element = array('value' => $element);

				$element['selected'] = ($type == 'form' && !empty($extra['default']) && $extra['default'] == $element['value'] )?'selected="selected"':'';

				$elements[] = \helpers\html\html::load_snippet(MAVERICK_VIEWSDIR . 'cms/includes/snippets/select_option.php', $element);
			}
		}

		return $elements;
	}

	/**
	 * gets a list of all available content types that follow a prescribed pattern, and caches it for use later if it's needed
	 * @param bool   $wrapped whether or not the returned array wraps each element in <option> tags taken from the select_option snippet
	 * @return array
	 */
	public static function get_content_types($current_type, $wrapped=true)
	{
		$h = html::getInstance();
		
		$types = array();
		
		if(isset($h->cached_snippets["content_types"]))
			$types = $h->cached_snippets["content_types"];
		else
		{
			foreach(glob(MAVERICK_HTDOCS . 'js/cms/content_types/*.js') as $type)
				$types[] = substr(basename($type), 0, strlen(basename($type) ) - 3 );	// the 3 comes from .js which should never change

			$types = $h->cached_snippets["content_types"] = $types;
		}

		// perform the replacements - this isn't done earlier because we don't want to cache replaced versions, as snippets will often change because of these replacements
		if($wrapped)
		{
			foreach($types as $type)
			{
				$type = array('value' => $type);

				$type['selected'] = ($current_type == $type)?'selected="selected"':'';

				$types[] = \helpers\html\html::load_snippet(MAVERICK_VIEWSDIR . 'cms/includes/snippets/select_option.php', $type);
			}
		}
		
		return $types;
	}
}
