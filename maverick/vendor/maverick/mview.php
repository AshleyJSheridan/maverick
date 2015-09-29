<?php
namespace maverick;

/**
 * a new view class which behaves the same as \maverick\view but isn't a singleton
 * @package MaverickCMS
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 * @todo backport this to the master branch
 */
class mview
{
	private $view = '';
	private $data = array();
	private $headers = array();
	private $original_headers = array();	// the headers actually specified by the user, which may not actually be set if they are malformed, etc
	private $parse_handlers = array();
	private $app;
	
	/**
	 * the magic constructor
	 * errors out if a view was not specified
	 * @param string $view the view to use in this instance
	 */
	public function __construct($view=null)
	{
		$this->app = \maverick\maverick::getInstance();
		
		if(!$view)
			\error::show('View not specified');

		$this->view = $view;
	}
	
	/**
	 * set a named parameter to add to this instances data array
	 * @param string $name the name by which to reference this data
	 * @param mixed  $data the data to add
	 * @return view
	 */
	public function with($name, $data)
	{
		if(empty($name) || empty($data))
			return $this;	// probably nicest to just return the view without modification so that it doesn't break the chain
		
		$this->data[(string)$name] = $data;
		
		return $this;
	}
	
	/**
	 * add HTTP headers to this view
	 * depending on the header being added, certain restrictions may apply
	 * @param array $headers the headers to pass to the view
	 * @return view
	 */
	public function headers($headers = array() )
	{
		if(!is_array($headers))
			return $this;
		
		foreach($headers as $header => $value)
		{
			// try  to validate the values passed to the most commonly used headers set
			// the $header variable is converted to the correct case in most of these in-case
			// several headers share the same validation constraints - it's a neatness thing!
			switch($header)
			{
				case 'status':
					if(intval($value))
						$this->headers['status'] = $this->set_status_code($value);
					break;
				case 'content-type':
					if(preg_match('/^[a-z0-9]+\/[a-z0-9]+(; charset=.+)?$/', $value) )
						$this->headers[$header] = $this->convert_header_case($header) . ": $value";
					break;
				case 'content-disposition':
					if(preg_match('/^attachment; ?filename=["\'][^"\']+["\']$/', $value) )
						$this->headers[$header] = $this->convert_header_case($header) . ": $value";
					break;
				case 'cache-control':
					if(preg_match('/^(public|private|no-cache(, must-revalidate)?|no-store|max-age=\d+(, (public|private))?(, must-revalidate)?)$/', $value) )
						$this->headers[$header] = $this->convert_header_case($header) . ": $value";
					break;
				case 'pragma':
					if(preg_match('/^(cache|no-cache)$/', $value) )
						$this->headers[$header] = $this->convert_header_case($header) . ": $value";
					break;
				case 'expires':
				case 'last-modified':
					if($this->check_date($value) )
						$this->headers[$header] = $this->convert_header_case($header) . ": $value";
					break;
				case 'content-length':
					if(intval($value))
						$this->headers[$header] = $this->convert_header_case($header) . ": $value";
					break;
				default:
					$this->headers[$header] = $this->convert_header_case($header) . ": $value";
			}//end switch
			
			$this->original_headers[$header] = $value;	// make a record of the actual requested header, regardless of whether it was actually successfully added due to parsing rules
		}//end foreach

		return $this;
	}
	
	/**
	 * adds a custom parse handler to a member array on the view object that can then be iterated
	 * to allow userland code to parse the rendered view and replace custom template snippets
	 * @param string $namespace the namespace given to a template snippet, e.g. {{namespace:
	 * @param string $handler   a string in the form of controller->method, where method is the static method of the given controller which is used as the callback in preg_replace_callback() for the custom parser
	 * @return view
	 */
	public function parse_handler($namespace, $handler)
	{
		if(!preg_match('/^\p{L}[\p{L}\p{N}_]+$/', $namespace) || !preg_match('/^\p{L}[\p{L}\p{N}_]+\-\>\p{L}[\p{L}\p{N}_]+$/', $handler) )
			\error::show("parse handler $handler does not exist");
		
		$this->parse_handlers[] = array($namespace, $handler);
		
		return $this;
	}
	
	/**
	 * use object buffering to build up the views into a single string and either return or echo it
	 * optionally output any headers that have been added to the view instance
	 * @param bool $echo         whether to echo the view or return it as a string
	 * @param bool $with_headers if set to true and $echo is also set to true, then send headers to the browser, otherwise do nothing
	 * @return string
	 */
	public function render($echo=true, $with_headers=false)
	{
		$view_file_path = MAVERICK_VIEWSDIR . "$this->view.php";

		if(!file_exists($view_file_path))
			error::show("View '$this->view' does not exist");
		else
		{
			ob_start();
			include $view_file_path;
			$view = ob_get_contents();
			ob_end_clean();

			if($this->app->get_config('config.view_parsing') !== false)
				$view = $this->parse_view($view);

			// this just stores the view if the config value is set to cache it
			// the extra check ensures that the admin section is never cached, because that would be silly
			if($this->app->get_config('cache.on') !== false &&
				!(strlen($this->app->get_config('cms.path') ) && strstr($this->app->requested_route_string, $this->app->get_config('cms.path') ) )
			)
			{
				$hash = $this->app->get_request_route_hash();
				\maverick\cache::store($hash, $view);
				
				if($with_headers)
					\maverick\cache::store("{$hash}_headers", json_encode($this->headers) );
			}
			
			if($with_headers)
			{
				foreach($this->headers as $header)
					header($header);
				
				// teapot
				if(isset($this->original_headers['status']) && $this->original_headers['status'] == 418 && $this->app->get_config('config.teapot') !== false)
				{
					$teapot = \helpers\html\html::load_snippet(\MAVERICK_BASEDIR . 'vendor/maverick/teapot.html', array() );
					$view = preg_replace('/<body/', "<body>$teapot", $view, 1);
					
				}
			}
			
			if($echo)
				echo $view;
			else
				return $view;
		}
	}
	
	/**
	 * perform a redirect and add in a response code if it was set
	 * @param string $url           the URL to redirect to - although the specs say it has to be absolute, every browser accepts relative too
	 * @param int    $response_code if a positive integer, this is the HTTP response code that is sent too
	 * @return bool
	 */
	public static function redirect($url, $response_code = null)
	{
		// set the host and protocol in order to validate relative URLs
		// note that any URL that does not begin with a protocol is considered relative, as per the spec
		$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME'];
		$protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
			|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
			|| (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') )?'https://':'http://';

		// if the URL is not considered valid, even after treating it as relative and converting it to absolute, then just bomb out
		if(!filter_var($url, FILTER_VALIDATE_URL) && ! filter_var("{$protocol}$host/$url", FILTER_VALIDATE_URL) )
			return false;
		
		if($response_code && intval($response_code))
			header("Location: $url", true, $response_code);
		else
			header("Location: $url");
			
		exit;
	}
	
	/**
	 * parse the rendered view for {{placeholders}} and replace them
	 * @param string $view the rendered view (typically html) possibly containing {{placeholders}}
	 * @return string
	 */
	private function parse_view($view)
	{
		// check for the use of multi-lingual gettext stuff
		if($this->app->get_config('lang.active') !== false)
		{
			// match the format of {{_('string to translate')}} - the quote style can be either single or double,
			// and the text to translate must start with a letter \p{L}
			if(preg_match_all('/\{\{_\(([\'"]\p{L}[^\'"]+[\'"])\)\}\}/', $view, $matches) && !empty($matches[1]) )
			{
				$find = $replace = array();
				
				foreach($matches[1] as $match)
				{
					$find[] = "{{_($match)}}";
					$replace[] = _(trim($match, "\"'") );
				}
				
				$view = str_replace($find, $replace, $view);
			}
		}
		// run any custom handlers that have been added - the callback will pass up to three matched parameters in the form
		// {{handler_namespacee:param1:param2:param3}} with each matched parameter being in an array with
		// param1 being array index 1, param2 being array index 3, and param3 being array index5
		// the callback method must be a static method, and the return must be a string
		if(count($this->parse_handlers))
		{
			foreach($this->parse_handlers as $parse_handler)
			{
				list($controller, $method) = explode('->', $parse_handler[1]);

				// build the match string used to replace snippets in the templates
				$match_arguments = "([\p{L}\p{N}_\/\[\]\"\=, ]+)";	// this is the portion of the regex responsible for a single argument match - adjust to allow for more argument characters
				$match_str = "$match_arguments";
				$num_arguments = 5;	// this controls how many arguments are matched - beyond this and the template parser will fail to match at all
				for($i=0; $i<$num_arguments; $i++)
					$match_str .= "(?::$match_arguments)?";
				
				$view = preg_replace_callback("/\{\{{$parse_handler[0]}:$match_str\}\}/", array($controller, $method), $view);
			}
		}
			
		// match simple placeholder formats - this check should always be last
		if(preg_match_all('/\{\{(\p{L}[\p{L}\p{N}_\.]+)/', $view, $matches) && !empty($matches[1]) )
		{
			$find = $replace = array();

			foreach($matches[1] as $match)
			{
				$find[] = "{{{$match}}}";

				$r = \data::get($match);
				if(is_array($r))
					$r = implode($r);

				$replace[] = $r;
			}

			$view = str_replace($find, $replace, $view);
		}
		
		return $view;
	}
	
	/**
	 * verify that a date string is in the correct format
	 * this is used to verify dates used in the headers
	 * @param string $date_string the date string to validate
	 * @return bool
	 */
	private function check_date($date_string)
	{
		$d = DateTime::createFromFormat('D, d M Y H:i:s e', $date_string);

		return $d && $d->format('D, d M Y H:i:s e') == $date_string;
	}
	
	/**
	 * convert a lowercase header to the correct capitalised case by casting to lower,
	 * breaking apart, and then capitalising, before joing together again
	 * @param string $header the header to convert the case for
	 * @return string
	 */
	private function convert_header_case($header)
	{
		return implode('-', array_map('ucfirst', explode('-', strtolower($header) ) ) );
	}
	
	/**
	 * set the full status code from an HTTP status code
	 * @param int $code the HTTP status code
	 * @return string
	 */
	private function set_status_code($code)
	{
		$codes = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			208 => 'Already Reported',
			209 => 'IM Used',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Switch Proxy',
			307 => 'Temporary Redirect',
			308 => 'Permanent Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot',
			419 => 'Authentication Timeout',
			420 => 'Method Failure',
			421 => 'Enhance Your Calm',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			426 => 'Upgrade Required',
			428 => 'Precondition Required',
			429 => 'Too Many Requests',
			431 => 'Request Header Fields Too Large',
			440 => 'Login Timeout',
			444 => 'No Response',
			449 => 'Retry With',
			450 => 'Blocked by Windows Parental Controls',
			451 => 'Unavailable For Legal Reasons',
			494 => 'Request Header Too Large',
			495 => 'Cert Error',
			496 => 'No Cert',
			497 => 'HTTP to HTTPS',
			498 => 'Token expired/invalid',
			499 => 'Client Closed Request',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			508 => 'Loop Detected',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not Extended',
			511 => 'Network Authentication Required',
			598 => 'Network read timeout error',
			599 => 'Network connect timeout error',
		);
		
		if(isset($codes[$code]) )
			return "{$_SERVER["SERVER_PROTOCOL"]} $code {$codes[$code]}";
		else
			return $this->set_status_code(200);
	}
}
