<?php
namespace maverick;

/**
 * the main query builder class
 */
class query
{
	static $_instance;
	private $from = '';
	private $joins = array();
	private $wheres = array();
	private $group_bys = array();
	private $order_bys = array();
	private $gets = array();
	private $data = array();
	private $results;
	
	private $join_conditions = array('=', '!=', '<', '<=', '>', '>=');
	private $where_conditions = array('IS', 'IS NOT');
	private $where_internal_conditions = array('IN', 'NOT IN');

	private $queries = array();
	
	private function __clone() {}
	
	/**
	 * returns the singleton of this query class
	 * @param bool $reset an optional argument that determines whether or not to blank this instance out for a new query
	 * @return \maverick\query
	 */
	public static function getInstance($reset=false)
	{
		if($reset)
		{
			// not everything needs to be reset, only those variables pertaining to an individual query
			
			$q = query::getInstance();
			
			foreach(array('joins', 'wheres', 'group_bys', 'order_bys', 'gets', 'data') as $var)
				$q->$var = array();
		}
		
		if(!(self::$_instance instanceof self))
			self::$_instance = new self;

		return self::$_instance;
	}
	
	/**
	 * get all generated queries created with this class
	 * @return array
	 */
	public function get_queries()
	{
		$q = query::getInstance();
		
		return $q->queries;
	}
	
	/**
	 * set the table to use in the FROM clause
	 * @param string $table
	 */
	public function set_from_table($table)
	{
		$this->from = $table;
	}
	
	/**
	 * creates a standard JOIN clause
	 * @param string $table the table to join
	 * @param array $on the conditions on which to join
	 * @return \maverick\query
	 */
	public static function leftJoin($table, $on)
	{
		$q = query::getInstance();
		
		$q->join('left', $table, $on);
				
		return $q;
	}
	
	/**
	 * creates a RIGHT JOIN clause
	 * @param string $table the table to join
	 * @param array $on the conditions on which to join
	 * @return \maverick\query
	 */
	public static function rightJoin($table, $on)
	{
		$q = query::getInstance();
		
		$q->join('right', $table, $on);
				
		return $q;
	}
	
	/**
	 * creates an OUTER JOIN clause
	 * @param string $table the table to join
	 * @param array $on the conditions on which to join
	 * @return \maverick\query
	 */
	public static function outerJoin($table, $on)
	{
		$q = query::getInstance();
		
		$q->join('outer', $table, $on);
				
		return $q;
	}
	
	/**
	 * creates a WHERE IN clause
	 * @param string $field the field to WHERE
	 * @param array $values the list of values to IN
	 * @return \maverick\query
	 */
	public static function whereIn($field, $values)
	{
		$q = query::getInstance();
		
		if(!is_array($values))
			return $q;
		
		$q->add_where('IN', $field, $values);
		
		return $q;
	}
	
	/**
	 * create a WHERE IN ... OR clause
	 * @param string $field the field to WHERE
	 * @param array $values the list of values to IN
	 * @return \maverick\query
	 */
	public static function whereInOr($field, $values)
	{
		$q = query::getInstance();
		
		if(!is_array($values))
			return $q;
		
		$q->add_where('IN', $field, $value, 'or');
		
		return $q;
	}
	
	/**
	 * create a WHERE NOT IN clause
	 * @param string $field the field to WHERE NOT
	 * @param array $values the list of values to IN
	 * @return \maverick\query
	 */
	public static function whereNotIn($field, $values)
	{
		$q = query::getInstance();
		
		if(!is_array($values))
			return $q;
		
		$q->add_where('NOT IN', $field, $values);
		
		return $q;
	}
	
	/**
	 * creates a wHERE NOT IN ... OR clause
	 * @param string $field the field to WHERE NOT
	 * @param array $values the list of values to IN
	 * @return \maverick\query
	 */
	public static function whereNotInOr($field, $values)
	{
		$q = query::getInstance();
		
		if(!is_array($values))
			return $q;
		
		$q->add_where('NOT IN', $field, $value, 'or');
		
		return $q;
	}
	
	/**
	 * creates a WHERE clause
	 * @param string $field the field to WHERE
	 * @param array $condition the conditions to create the WHERE on
	 * @param mixed $value the value(s) to use in the WHERE
	 * @return \maverick\query
	 */
	public static function where($field, $condition, $value)
	{
		$q = query::getInstance();
		
		if(!in_array($condition, array_merge($q->join_conditions, $q->where_conditions)))
			return $q;
		
		$q->add_where($condition, $field, $value);
		
		return $q;
	}
	
	/**
	 * create a WHERE ... OR clause
	 * @param string $field the field to WHERE
	 * @param array $condition the conditions to create the WHERE on
	 * @param mixed $value the value(s) to use in the WHERE
	 * @return \maverick\query
	 */
	public static function whereOr($field, $condition, $value)
	{
		$q = query::getInstance();
		
		if(!in_array($condition, array_merge($q->join_conditions, $q->where_conditions)))
			return $q;
		
		$q->add_where($condition, $field, $value, 'or');
		
		return $q;
	}

	/**
	 * create a GROUP BY clause
	 * @param string $field the field to group by
	 * @return \maverick\query
	 */
	public static function groupBy($field)
	{
		$q = query::getInstance();
		
		$q->group_bys[] = array(
			'field' => $field,
		);
		
		return $q;
	}
	
	/**
	 * create an ORDER BY clause
	 * @param string $field the field to order by
	 * @param string $direction the direction to order results
	 * @return \maverick\query
	 */
	public static function orderBy($field, $direction='asc')
	{
		$q = query::getInstance();
		
		if(!in_array($direction, array('asc', 'desc')))
			return $q;
		
		$q->order_bys[] = array(
			'field' => $field,
			'direction' => $direction,
		);
		
		return $q;
	}
	
	/**
	 * create a SELECT clause
	 * @param array $fields the fields to fetch
	 * @return \maverick\query
	 */
	public static function get($fields=array('*'))
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
		
		$q->result('select');
		
		return $q;
	}
	
	/**
	 * create an INSERT clause
	 * @param array $data the data to insert
	 * @return boolean|\maverick\query
	 */
	public static function insert($data)
	{
		$q = query::getInstance();
		
		if(!is_array($data))
			return false;
		
		$q->data = $data;
		
		$q->result('insert');
		
		return $q;
	}
	
	/**
	 * creates an UPDATE clause
	 * @param array $data the data to use in the update
	 * @return boolean|\maverick\query
	 */
	public static function update($data)
	{
		$q = query::getInstance();
		
		if(!is_array($data))
			return false;
		
		$q->data = $data;
		
		$q->result('update');
		
		return $q;
	}
	
	/**
	 * create a DELETE clause
	 * @return \maverick\query
	 */
	public static function delete()
	{
		$q = query::getInstance();
		
		$q->result('delete');
		
		return $q;
	}

	/**
	 * builds a query, executes it, and returns the results of the query
	 * @param string $type the type of query being executed
	 * @return boolean
	 */
	private function result($type)
	{
		$maverick = \maverick\maverick::getInstance();
		$q = query::getInstance();
		
		if(!strlen($q->from)) return false;
		
		$from = "FROM {$q->from}";
		list($join_string, $join_params) = $q->compile_joins($q->joins);
		list($where_string, $where_params) = $q->compile_wheres($q->wheres);
		list($group_by_string, $group_by_params) = $q->compile_group_bys($q->group_bys);
		list($order_by_string, $order_by_params) = $q->compile_order_bys($q->order_bys);

		switch($type)
		{
			case 'select':
			{
				$select_string = implode(',', $q->gets);
				$params = array_merge($join_params, $where_params, $group_by_params, $order_by_params);
				
				$stmt = $maverick->db->pdo->prepare("SELECT $select_string $from $join_string $where_string $group_by_string $order_by_string");

				break;
			}
			case 'insert':
			{
				list($insert_string, $params) = $q->compile_inserts($q->data);
				
				$stmt = $maverick->db->pdo->prepare("INSERT INTO {$q->from} $insert_string");
				
				break;
			}
			case 'delete':
			{
				$params = $where_params;
				
				$stmt = $maverick->db->pdo->prepare("DELETE FROM {$q->from} $where_string");
				
				break;
			}
			case 'update':
			{
				list($update_string, $update_params) = $q->compile_updates($q->data);
				$params = array_merge($update_params, $where_params);
				
				$stmt = $maverick->db->pdo->prepare("UPDATE {$q->from} SET $update_string  $where_string");

				break;
			}
		}
		
		if ($stmt->execute( $params ) )
		{
			switch($type)
			{
				case 'select':
				{
					$results = array();
					while ($row = $stmt->fetch(\PDO::FETCH_ASSOC))
						$results[] = $row;
					
					break;
				}
				default:
					$results = true;
			}
		}
		else
			$results = false;
		
		$q->numrows = count($results);
		$q->queries[] = array('query' => $stmt->queryString, 'params' => $params);

		$q->results = $results;
	}
	
	/**
	 * compiles the list of updates, generates that part of the query string and 
	 * adds values to be escaped into the $params array
	 * @param array $data the data to use in the UPDATE
	 * @return array
	 */
	private function compile_updates($data)
	{
		$update_string = '';
		$params = array();
		
		foreach($data as $key => $value)
		{
			if(!strlen($key))
				continue;
			
			$update_string .= strlen($update_string)?', ':'';
			
			$update_string .= " $key = ?";
			
			$params[] = $value;
		}
		
		return array($update_string, $params);
	}

	/**
	 * compiles the list of inserts, generates that part of the query string and 
	 * adds values to be escaped into the $params array
	 * @param array $data the data to use in the INSERT
	 * @return array
	 */
	private function compile_inserts($data)
	{
		$insert_string = '';
		$params = array();
		
		if(isset($data[0]))	// crude, but should determine if this is a single key/value array for inserting one record, or a multi-dimensional array for a bulk insert
		{
			$insert_string .= ' (' . implode(',', array_keys($data[0])) . ') VALUES ';
			
			for($i=0; $i<count($data); $i++)
			{
				$insert_string .= ($i)?', ':'';
				
				$insert_string .= ' (' . implode(',', array_fill(0, count(array_keys($data[$i]) ), '?') ) . ') ';
				
				foreach($data[$i] as $value)
					$params[] = $value;
			}
		}
		else
		{
			$insert_string .= ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count(array_keys($data) ), '?') ) . ') ';
			foreach($data as $value)
				$params[] = $value;
		}
		
		return array($insert_string, $params);
	}
	
	/**
	 * compile the joins, create those parts of the query string and adds any
	 * values to be parameterised into the $params array
	 * @param array $joins the array of joins
	 * @return array
	 */
	private function compile_joins($joins)
	{
		$join_string = '';
		$params = array();
		
		foreach($joins as $join)
		{
			$join_string .= " {$join['type']} JOIN {$join['table']} ON ";
			
			for($i=0; $i<count($join['on']); $i++)
			{
				if(!$i)
					$join_string .= ' ( ';
				
				if($i)
					$join_string .= ' AND ';
				
				if(is_object($join['on'][$i]['field1']) && get_class($join['on'][$i]['field1']) == 'maverick\db_raw')
				{
					$join_string .= ' ? ';
					$params[] = (string)$join['on'][$i]['field1'];
				}
				else
					$join_string .= $join['on'][$i]['field1'];
				
				$join_string .= " {$join['on'][$i]['condition']} ";
				
				if(is_object($join['on'][$i]['field2']) && get_class($join['on'][$i]['field2']) == 'maverick\db_raw')
				{
					$join_string .= ' ? ';
					$params[] = (string)$join['on'][$i]['field2'];
				}
				else
					$join_string .= $join['on'][$i]['field2'];
				
				if($i==count($join['on'])-1)
					$join_string .= ' ) ';
			}
		}
		
		return array($join_string, $params);
	}
	
	/**
	 * compile the wheres, create those parts of the query string and adds any
	 * values to be parameterised into the $params array
	 * @param array $wheres the array of where clauses
	 * @return array
	 */
	private function compile_wheres($wheres)
	{
		$where_string = '';
		$params = array();

		for($i=0; $i<count($wheres); $i++)
		{
			$where_string .= (!$i)?' WHERE ':" {$wheres[$i]['type']} ";
			
			if(is_object($wheres[$i]['field']) && get_class($wheres[$i]['field']) == 'maverick\db_raw')
			{
				$where_string .= ' ? ';
				$params[] = (string)$wheres[$i]['field'];
			}
			else
				$where_string .= $wheres[$i]['field'];
			
			$where_string .= " {$wheres[$i]['condition']} ";
			
			if(is_object($wheres[$i]['value']) && get_class($wheres[$i]['value']) == 'maverick\db_raw')
			{
				$where_string .= ' ? ';
				$params[] = (string)$wheres[$i]['value'];
			}
			else
			{
				switch($wheres[$i]['condition'])
				{
					case 'IN':
					case 'NOT IN':
					{
						if(is_array($wheres[$i]['value']))
						{
							$where_string .= ' (';
							for($j=0; $j<count($wheres[$i]['value']); $j++)
							{
								$where_string .= ($j)?',':'';
								
								$where_string .= '?';
								$params[] = $wheres[$i]['value'][$j];
							}
							$where_string .= ') ';
						}

						break;
					}
					default:
						$where_string .= $wheres[$i]['value'];
				}
			}
				//$where_string .= $wheres[$i]['field'];
		}
		
		return array($where_string, $params);
	}
	
	/**
	 * compile the group bys, create those parts of the query string and adds any
	 * values to be parameterised into the $params array
	 * @param array $group_bys the array of group by clauses
	 * @return array
	 */
	private function compile_group_bys($group_bys)
	{
		$group_by_string = '';
		$params = array();
		
		for($i=0; $i<count($group_bys); $i++)
		{
			$group_by_string .= (!$i)?' GROUP BY ':',';
			
			if(is_object($group_bys[$i]['field']) && get_class($group_bys[$i]['field']) == 'maverick\db_raw')
			{
				$group_by_string .= ' ? ';
				$params[] = (string)$group_bys[$i]['field'];
			}
			else
				$group_by_string .= $group_bys[$i]['field'];
		}

		return array($group_by_string, $params);
	}

	/**
	 * compile the order bys, create those parts of the query string and adds any
	 * values to be parameterised into the $params array
	 * @param array $order_bys the array of order by clauses
	 * @return array
	 */
	private function compile_order_bys($order_bys)
	{
		$order_by_string = '';
		$params = array();
		
		for($i=0; $i<count($order_bys); $i++)
		{
			$order_by_string .= (!$i)?' ORDER BY ':',';
			
			if(is_object($order_bys[$i]['field']) && get_class($order_bys[$i]['field']) == 'maverick\db_raw')
			{
				$order_by_string .= ' ? ';
				$params[] = (string)$order_bys[$i]['field'];
			}
			else
				$order_by_string .= $order_bys[$i]['field'];
			
			$order_by_string .= " {$order_bys[$i]['direction']} ";
		}

		return array($order_by_string, $params);
	}

	/**
	 * get the results from an executed query
	 * @return mixed
	 */
	public function fetch()
	{
		return $this->results;
	}
	
	/**
	 * adds a where clause to the query object
	 * @param string $condition the condition to WHERE on
	 * @param string $field the field to WHERE on
	 * @param mixed $value the value to WHERE on
	 * @param string $type the type of WHERE, either 'and' or 'or'
	 * @return \maverick\query
	 */
	private function add_where($condition, $field, $value, $type='and')
	{
		$q = query::getInstance();
		
		if(!in_array($condition, array_merge($q->join_conditions, $q->where_conditions, $q->where_internal_conditions)))
			return $q;
		
		$q->wheres[] = array(
			'field' => $field,
			'condition' => $condition,
			'value' => $value,
			'type' => strtoupper($type),
		);
		
		return $q;
	}
	
	/**
	 * add a join clause to the query object
	 * @param string $type the type of join, e.g. LEFT, RIGHT, etc
	 * @param string $table the table to join
	 * @param array $on the join conditions
	 * @return \maverick\query
	 */
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
				'type' => strtoupper($type),
				'on' => $q->on($on),
			);
		}
		
		return $q;
	}
	
	/**
	 * add an ON clause to the query object and return an array of consistent structure
	 * to use throughout the rest of the class
	 * @param array $on the ON conditions
	 * @return array
	 */
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
	
	/**
	 * verify that a join condition is in the safe set of allowed join conditions
	 * @param string $condition the condition to check
	 * @return bool
	 */
	private function check_join_condition($condition)
	{
		return in_array($condition, $this->join_conditions);
	}
}
