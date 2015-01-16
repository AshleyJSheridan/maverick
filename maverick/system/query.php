<?php
class query
{
	static $_instance;
	private $select = '*';
	private $from = '';
	private $joins = array();
	private $wheres = array();
	private $group_bys = array();
	private $gets = array();
	
	private $join_conditions = array('=', '!=', '<', '<=', '>', '>=');
	private $where_conditions = array('IS', 'IS NOT');
	private $where_internal_conditions = array('IN', 'NOT IN');

	private $query = '';
	
	private function __clone() {}
	
	public static function getInstance()
	{
		if(!(self::$_instance instanceof self))
			self::$_instance = new self;
		return self::$_instance;
	}
	
	public function set_from_table($table)
	{
		$this->set_param('from', $table);
	}
	
	public static function leftJoin($table, $on)
	{
		$q = query::getInstance();
		
		$q->join('left', $table, $on);
				
		return $q;
	}
	
	public static function rightJoin($table, $on)
	{
		$q = query::getInstance();
		
		$q->join('right', $table, $on);
				
		return $q;
	}
	
	public static function outerJoin($table, $on)
	{
		$q = query::getInstance();
		
		$q->join('outer', $table, $on);
				
		return $q;
	}
	
	public static function whereIn($field, $values)
	{
		$q = query::getInstance();
		
		if(!is_array($values))
			return $q;
		
		$q->add_where('IN', $field, $value);
		
		return $q;
	}
	
	public static function whereNotIn($field, $values)
	{
		$q = query::getInstance();
		
		if(!is_array($values))
			return $q;
		
		$q->add_where('NOT IN', $field, $value);
		
		return $q;
	}
	
	public static function where($field, $condition, $value)
	{
		$q = query::getInstance();
		
		if(!in_array($condition, array_merge($q->join_conditions, $q->where_conditions)))
			return $q;
		
		$q->add_where($condition, $field, $value);
		
		return $q;
	}

	public static function groupBy($field, $direction='asc')
	{
		$q = query::getInstance();
		
		if(!in_array($direction, array('asc', 'desc')))
			return $q;
		
		$q->group_bys[] = array(
			'field' => $field,
			'direction' => $direction,
		);
		
		return $q;
	}
	
	public static function get($fields='*')
	{
		$q = query::getInstance();
		
		if((is_array($fields) && !count($fields)) || is_string($fields) && !strlen($fields) )
			return $q;
		
		if(is_array($fields))
		{
			foreach($fields as $field)
				$q->gets[] = $field;
		}
		else
			$q->gets[] = $fields;
		
		//TODO: as this should be the last chainable method called, this should trigger a build and execution of the query
		
		return $q;
			
	}
	
	
	
	private function add_where($type, $field, $value)
	{
		$q = query::getInstance();
		
		if(!in_array($type, array_merge($q->join_conditions, $q->where_conditions, $q->where_internal_conditions)))
			return $q;
		
		$q->wheres[] = array(
			'field' => $field,
			'condition' => $type,
			'value' => $value,
		);
		
		return $q;
	}
	
	private function join($type, $table, $on)
	{
		$q = query::getInstance();
		
		// either there's no table or mal-formed on parameters, so do nothing
		if(!is_array($on) || !strlen($table))
			return $q;
		else
		{
			$q->joins[] = array(
				'table' => $table,
				'type' => $type,
				'on' => $q->on($on),
			);
		}
		
		return $q;
	}
	
	private function on($on)
	{
		$ons = array();
		
		if(is_array(reset($on)))
		{
			foreach($on as $o)
			{
				if(count($o) == 3 && $this->check_join_condition($o[1]))	// ignore any malformed join on arrays
					$ons[] = array('field1' => $o[0], 'condition' => $o[1], 'field2' => $o[2]);
			}
		}
		else
		{
			if(count($on) == 3 && $this->check_join_condition($on[1]))
				$ons[] = array('field1' => $on[0], 'condition' => $on[1], 'field2' => $on[2]);
		}
		
		return $ons;
	}
	
	private function set_param($param, $value)
	{
		$this->$param = $value;
	}
	
	private function check_join_condition($condition)
	{
		return in_array($condition, $this->join_conditions);
	}
}