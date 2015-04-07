<?php
/**
 * a validation class for checking form submissions
 */
class validator
{
	static $_instance;
	private $rules = array();
	private $errors = array();
	
	private function __construct() {}
	
	private function __clone() {}
	
	/**
	 * returns a reference to ths singleton instance of this class - there can be only one!
	 * @return validator
	 */
	public static function getInstance()
	{
		if(!(self::$_instance instanceof self))
			self::$_instance = new self;

		return self::$_instance;
	}
		
	/**
	 * applies the passed rules to the instance of this class and returns a reference to itself for chaining
	 * @param array $rules an array of rules to apply to the submitted data
	 * @return validator
	 */
	public static function make($rules)
	{
		$v = validator::getInstance();
		$app = \maverick\maverick::getInstance();
		
		$v->reset();
		
		if(!is_array($rules))
			error::show('Validator ruleset is not an array');
		
		$v->set_rules($rules);
		$app->validator = $v;
		
		return $v;
	}
	
	/**
	 * gets all the errors with submitted data, or all errors for a specific field if given,
	 * with individual errors wrapped with a specified set of tags
	 * @param string|null $field an optional field name
	 * @param array $wrapper an array containing a pair of strings to wrap around an individual error
	 * @return array
	 */
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
		if(!$field)
		{
			if(count($wrapper)==2)
			{
				foreach($v->errors as $key => &$error)
					$v->errors[$key] = $v->get_all_errors($key, $wrapper);
			}

			return $v->errors;
		}
			
	}
	
	/**
	 * return only the first error for the specified field
	 * @param string $field the name of a field
	 * @param array $wrapper an array containing a pair of strings to wrap around an individual error
	 * @return string
	 */
	public static function get_first_error($field, $wrapper=array())
	{
		$v = validator::getInstance();
		
		if(isset($v->errors[$field]))
			return(count($wrapper)==2)?$wrapper[0] . reset($v->errors[$field]) . $wrapper[1]:reset($v->errors[$field]);
		else
			return '';
	}
	
	/**
	 * get the number of fields with errors
	 * note that this does not return the number of total errors where a field may have more than one error
	 * @return int
	 */
	public static function get_error_count()
	{
		$v = validator::getInstance();
		
		return count($v->errors);
	}

	/**
	 * run the rules attached to this instance and apply them to the submitted data
	 * populating the internal errors array with any errors found
	 * @return bool
	 */
	public static function run()
	{
		$v = validator::getInstance();
		$app = \maverick\maverick::getInstance();
		
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
	
	
	/**
	 * apply the required rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @return bool
	 */
	private function rule_required($field)
	{
		return isset($_REQUEST[$field]) && strlen($_REQUEST[$field]);
	}
	
	/**
	 * apply the accepted rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @return bool
	 */
	private function rule_accepted($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return in_array($_REQUEST[$field], array('1', 'yes', 'y', 'checked') );
		else
			return true;
	}
	
	/**
	 * apply the alpha rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @return bool
	 */
	private function rule_alpha($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match("/^[\p{L} ]+$/", $_REQUEST[$field]);
		else
			return true;
	}
	
	/**
	 * apply the alpha_apos rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @return bool
	 */
	private function rule_alpha_apos($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match("/^[\p{L}' ]+$/", $_REQUEST[$field]);
		else
			return true;
	}
	
	/**
	 * apply the alpha_numeric rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @return bool
	 */
	private function rule_alpha_numeric($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match("/^[\p{L}\d \.\-\+]+$/", $_REQUEST[$field]);
		else
			return true;
	}
	
	/**
	 * apply the alpha_dash rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @return bool
	 */
	private function rule_alpha_dash($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match("/^[\p{L}\d \-_]+$/", $_REQUEST[$field]);
		else
			return true;
	}
	
	/**
	 * apply the numeric rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @return bool
	 */
	private function rule_numeric($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return is_numeric($_REQUEST[$field]);
		else
			return true;
	}
	
	/**
	 * apply the min rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @param int the value to use for this rule
	 * @return bool
	 */
	private function rule_min($field, $value)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return $_REQUEST[$field] >= (float)$value[0];
		else
			return true;
	}
	
	/**
	 * apply the max rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @param int the value to use for this rule
	 * @return bool
	 */
	private function rule_max($field, $value)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return $_REQUEST[$field] <= (float)$value[0];
		else
			return true;
	}
	
	/**
	 * apply the email rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @return bool
	 */
	private function rule_email($field, $value)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return filter_var($_REQUEST[$field], FILTER_VALIDATE_EMAIL);
		else
			return true;
	}
	
	/**
	 * apply the regex rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @param string $regex the regular expression to apply to the field
	 * @return bool
	 */
	private function rule_regex($field, $regex)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match($regex[0], $_REQUEST[$field]);
		else
			return true;
	}
	
	/**
	 * apply the url rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @return bool
	 */
	private function rule_url($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return filter_var($_REQUEST[$field], FILTER_VALIDATE_URL);
		else
			return true;
	}
	
	/**
	 * apply the phone rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @return bool
	 */
	private function rule_phone($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return preg_match("/^[\d\+\- ]{8,}$/", $_REQUEST[$field]);
		else
			return true;
	}
	
	/**
	 * apply the ip rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @return bool
	 */
	private function rule_ip($field)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return filter_var($_REQUEST[$field], FILTER_VALIDATE_IP);
		else
			return true;
	}
	
	/**
	 * apply the before rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @param string $date the date to use for this field
	 * @return bool
	 */
	private function rule_before($field, $date)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return strtotime($_REQUEST[$field]) < strtotime($date[0]);
		else
			return true;
	}
	
	/**
	 * apply the after rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @param string $date the date to use for this field
	 * @return bool
	 */
	private function rule_after($field, $date)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return strtotime($_REQUEST[$field]) > strtotime($date[0]);
		else
			return true;
	}
	
	/**
	 * apply the between rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @param array $numbers an array of two numbers to use as the min and max values for this rule
	 * @return bool
	 */
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
	
	/**
	 * apply the confirmed rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @param array $field2 an array (because of the way the arguments are passed) of one element specifying the name of the field to compare this field to
	 * @return bool
	 */
	private function rule_confirmed($field, $field2)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return (isset($_REQUEST[$field2[0]]) && $_REQUEST[$field] == $_REQUEST[$field2[0]] );
		else
			return true;
	}
	
	/**
	 * apply the in rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @param array $array an array of values to use for this rule
	 * @return bool
	 */
	private function rule_in($field, $array)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return in_array($_REQUEST[$field], $array);
		else
			return true;
	}
	
	/**
	 * apply the notin rule to a field
	 * @param string $field the name of the field to which this rule applies
	 * @param array $array an array of values to use for this rule
	 * @return bool
	 */
	private function rule_notin($field, $array)
	{
		if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) )
			return !in_array($_REQUEST[$field], $array);
		else
			return true;
	}
	
	/**
	 * set the rules to this validator instance
	 * @param array $rules the rules to use
	 */
	private function set_rules($rules)
	{
		$v = validator::getInstance();
		
		$v->rules = $rules;
	}
	
	/**
	 * reset this singleton back to base values
	 */
	private function reset()
	{
		$v = validator::getInstance();
		
		foreach(array('rules') as $var)
			$v->$var = array();
	}
}