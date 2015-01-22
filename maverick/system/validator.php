<?php
class validator
{
	static $_instance;
	private $rules = array();
	private $errors = array();
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function getInstance()
	{
		if(!(self::$_instance instanceof self))
			self::$_instance = new self;

		return self::$_instance;
	}
		
	public static function make($rules)
	{
		$v = validator::getInstance();
		$app = maverick::getInstance();
		
		$v->reset();
		
		if(!is_array($rules))
			return $v;	// TODO: consider throwing an error here
		
		$v->set_rules($rules);
		$app->validator = $v;
		
		return $v;
	}
	
	public static function get_all_errors($field=null, $wrapper=array())
	{
		$v = validator::getInstance();
		
		if($field && isset($v->errors[$field]))
		{
			$errors = $v->errors[$field];
			
			if(count($wrapper)==2)
			{
				foreach($errors as &$error)
					$error = $wrapper[0] . $error . $wrapper[1];
			}

			return $errors;
		}
		else
		{
			if(count($wrapper)==2)
			{
				foreach($v->errors as $key => &$error)
					$v->errors[$key] = $v->get_all_errors($key, $wrapper);
			}

			return $v->errors;
		}
			
	}
	
	public static function get_first_error($field, $wrapper=array())
	{
		$v = validator::getInstance();
		
		if(isset($v->errors[$field]))
			return(count($wrapper)==2)?$wrapper[0] . reset($v->errors[$field]) . $wrapper[1]:reset($v->errors[$field]);
		else
			return '';
	}
	
	public static function get_error_count()
	{
		$v = validator::getInstance();
		
		return count($v->errors);
	}

	public static function run()
	{
		$v = validator::getInstance();
		$app = maverick::getInstance();
		
		// run through the rules and apply them to any data that exists in the $_REQUEST array
		foreach($v->rules as $field => $rules)
		{
			if(!is_array($rules))
				$rules = (array)$rules;	// cast the single string rules to an array to make them the same to work with as groups of rules
			
			foreach($rules as $rule)
			{
				$params = explode(':', $rule);
				$rule_method = "rule_{$params[0]}";
				$rule = $params[0];
				
				if(method_exists($v, $rule_method))
				{
					$params = array_slice($params, 1);
					
					if(!$v->$rule_method($field, $params))	// if the rule fails, generate the error message from the specific template in the config, and push it into the array for that field
					{
						// look up an error message for this and push it into the errors array
						$error_string = vsprintf( $app->get_config("validator.$rule"), array_merge((array)$field, $params) );

						if(!isset($v->errors[$field]))
							$v->errors[$field] = array();
						
						$v->errors[$field][] = $error_string;
					}
				}
				else
					error::show("the validation rule {$params[0]} does not exist");
			}
		}
		return !count($v->errors);
	}
	
	
	private function rule_required($field)
	{
		return isset($_REQUEST[$field]) && strlen($_REQUEST[$field]);
	}
	
	private function rule_accepted($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return in_array($_REQUEST[$field], array('1', 'yes', 'y', 'checked') );
		else
			return true;
	}
	
	private function rule_alpha($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match("/^[\p{L} ]+$/", $_REQUEST[$field]);
		else
			return true;
	}
	
	private function rule_alpha_apos($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match("/^[\p{L}' ]+$/", $_REQUEST[$field]);
		else
			return true;
	}
	
	private function rule_alpha_numeric($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match("/^[\p{L}\d \.\-\+]+$/", $_REQUEST[$field]);
		else
			return true;
	}
	
	private function rule_alpha_dash($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match("/^[\p{L}\d \-_]+$/", $_REQUEST[$field]);
		else
			return true;
	}
	
	private function rule_numeric($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return is_numeric($_REQUEST[$field]);
		else
			return true;
	}
	
	private function rule_min($field, $value)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return $_REQUEST[$field] >= (float)$value[0];
		else
			return true;
	}
	
	private function rule_max($field, $value)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return $_REQUEST[$field] <= (float)$value[0];
		else
			return true;
	}
	
	private function rule_email($field, $value)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return filter_var($_REQUEST[$field], FILTER_VALIDATE_EMAIL);
		else
			return true;
	}
	
	private function rule_regex($field, $regex)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match($regex[0], $_REQUEST[$field]);
		else
			return true;
	}
	
	private function rule_url($field, $url)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return filter_var($_REQUEST[$field], FILTER_VALIDATE_URL);
		else
			return true;
	}
	
	private function rule_phone($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match("/^[\d\+\- ]{8,}$/", $_REQUEST[$field]);
		else
			return true;
	}
	
	private function rule_ip($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return filter_var($_REQUEST[$field], FILTER_VALIDATE_IP);
		else
			return true;
	}
	
	private function rule_before($field, $date)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return strtotime($_REQUEST[$field]) < strtotime($date[0]);
		else
			return true;
	}
	
	private function rule_after($field, $date)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return strtotime($_REQUEST[$field]) > strtotime($date[0]);
		else
			return true;
	}
	
	private function rule_between($field, $numbers)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
		{
			$min = (float)$numbers[0];
			$max = (float)$numbers[1];
			
			switch(true)
			{
				case is_numeric($_REQUEST[$field]):
					$val = (float)$_REQUEST[$field];
					break;
				default:
					$val = strlen($_REQUEST[$field]);
					break;
			}
			return ($val > $min) && ($val < $max);
		}
		else
			return true;
	}
	
	private function rule_confirmed($field, $field2)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return (isset($_REQUEST[$field2[0]]) && $_REQUEST[$field] == $_REQUEST[$field2[0]] );
		else
			return true;
	}
	
	private function rule_in($field, $array)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return in_array($_REQUEST[$field], $array);
		else
			return true;
	}
	
	private function rule_notin($field, $array)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return !in_array($_REQUEST[$field], $array);
		else
			return true;
	}
	
	
	private function set_rules($rules)
	{
		$v = validator::getInstance();
		
		$v->rules = $rules;
	}
	
	private function reset()
	{
		$v = validator::getInstance();
		
		foreach(array('rules') as $var)
			$v->$var = array();
	}
}