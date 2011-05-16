<?php

class MY_Model extends CI_Model {
	
	/**
	 * Specify the primary table to execute queries on
	 *
	 * @var string
	 */
	protected $primary_table = '';
	
	/**
	 * Fields that are allowed to be inserted or updated
	 *
	 * @var array
	 */
	protected $fields = array();
	
	/**
	 * Fields that are required to insert or update a record
	 *
	 * @var array
	 */
	protected $required_fields = array();
	
	/**
	 * Specify additional models to be loaded
	 *
	 * @var array
	 */
	protected $models = array();
	
	/**
	 * Set the primary key for the table
	 *
	 * @var string
	 */
	protected $primary_key = 'id';
	
	/**
	 * Boolean to toggle field existence checks
	 *
	 * @var bool
	 */
	protected $validate_field_existence = FALSE;
	
	/**
	 * Used if there is no primary key for the table
	 *
	 * @var bool
	 */
	protected $no_primary_key = FALSE;
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
				
		// if the models array is not empty
		if ( ! empty($this->models))
		{
			// load each additional model
			foreach ($this->models as $model)
			{
				$this->load->model($model);
			}
		}
	}
	
	/**
	 * add method creates a record in the table.
	 *
	 * Options: array of fields available
	 *
	 * @param array $options
	 * @return int ID on success, bool false on fail
	 */
	function add($options = array())
	{
		// make sure required values are there
		if ( ! $this->_required($this->required_fields, $options))
		{
			return FALSE;
		}
		
		// check if fields have been specified or get them from the table
		$this->_set_editable_fields($this->primary_table);
		
		// do field existence check
		$this->_validate_options_exist($options);

		// default values
		$default = array(
			'date_created' => date($this->config->item('log_date_format')),
			'date_modified' => date($this->config->item('log_date_format'))
		);
		$options = $this->_default($default, $options);
		
		// qualification (make sure that we're not allowing the site to insert data that it shouldn't)
		foreach ($this->fields as $field) 
		{
			// if we are trying to set the value of the field
			if (isset($options[$field]))
			{
				$this->db->set($field, $options[$field]);
			}
		}
		
		// Execute the query
		$query = $this->db->insert($this->primary_table);

		// if the query was run successfully
		if ($query)
		{
			// if there is no primary key
			if ($this->no_primary_key == FALSE)
			{
				return $this->db->insert_id();
			}
			else
			{
				return TRUE;
			}
		}
	}
	
	/**
	 * get method returns an array of qualified record objects
	 *
	 * Option: Values
	 *
	 * Returns (array of objects)
	 *
	 * @param array $options
	 * @return array result()
	 */
	function get($options = array())
	{
		// default values
		$defaults = array(
			'sort_direction' => 'asc'
		);
		$options = $this->_default($defaults, $options);
		
		// check if fields have been specified or get them from the table
		$this->_set_editable_fields($this->primary_table);
		
		// add where clauses to query
		foreach ($this->fields as $field)
		{
			if (isset($options[$field]))
			{
				$this->db->where($field, $options[$field]);
			}
		}

		// if limit / offset are declared then we need to take them into account
		if (isset($options['limit']) && isset($options['offset']))
		{
			$this->db->limit($options['limit'], $options['offset']);
		}
		else
		{
			if (isset($options['limit']))
			{
			    $this->db->limit($options['limit']);
			}
		}

		// sort
		if (isset($options['sort_by']))
		{
			$this->db->order_by($options['sort_by'], $options['sort_direction']);
		}
		
		// execute the query
		$query = $this->db->get($this->primary_table);
		
		// if an id was specified...
		if (isset($options[$this->primary_key]))
		{
			// return the result as an object
			return $query->row();
		}
		else
		{
			// return the regular query result
			return $query;
		}
	}
	
	/**
	 * update method alters a record in the table.
	 *
	 * Option: Values
	 *
	 * @param array $options
	 * @return int affected_rows()
	 */
	function update($options = array())
	{
		// required values
		$required = array($this->primary_key);
		if ( ! $this->_required($required, $options))
		{
			return FALSE;
		}
		
		// check if fields have been specified or get them from the table
		$this->_set_editable_fields($this->primary_table);
		
		// do field existence check
		$this->_validate_options_exist($options);

		// default values
		$default = array(
			'date_modified' => date($this->config->item('log_date_format'))
		);
		$options = $this->_default($default, $options);
		
		// qualification (make sure that we're not allowing the site to insert data that it shouldn't)
		foreach ($this->fields as $field) 
		{
			// if we are trying to set the value of the field
			if (isset($options[$field]))
			{
				$this->db->set($field, $options[$field]);
			}
		}
				
		// update on primary key
		$this->db->where($this->primary_key, $options[$this->primary_key]);

		// Execute the query
		$this->db->update($this->primary_table);

		// Return the number of rows updated, or false if the row could not be inserted
		return $this->db->affected_rows();
	}
	
	/**
	 * delete method removes a record from the table
	 *
	 * Option: Values
	 * --------------
	 * id (required)
	 *
	 * @param array $options
	 */
	function delete($options = array())
	{
		// required values
		$required = array($this->primary_key);
		if ( ! $this->_required($required, $options))
		{
			return FALSE;
		}
		
		// execute delete query
		$this->db->where($this->primary_key, $options[$this->primary_key]);
		return $this->db->delete($this->primary_table);
	}
	
	/**
	 * Validates that the fields you are trying to modify actually exist in the database
	 * 
	 * Only use this method for debugging, not fit for production code because of the number of queries it has to run
	 *
	 * @param string $options 
	 * @return void
	 */
	function _validate_options_exist($options)
	{
		// if field existence validation is enabled
		if ($this->validate_field_existence == TRUE)
		{
			foreach ($options as $key => $value)
			{
				// if the field does not exist in the database
				if ( ! $this->db->field_exists($key, $this->primary_table))
				{
					// display an error
					show_error('You are trying to insert data into a field that does not exist.  The field "'. $key .'" does not exist in the "'. $this->primary_table .'" table.');
				}
			}
		}
	}
	
	/**
	 * set editable fields in the table, if no fields are specified in the model, fields will be pulled dynamically from the table
	 *
	 * @return void
	 */
	function _set_editable_fields()
	{
		// if the fields array is empty
		if (empty($this->fields))
		{
			// pull the fields dynamically from the database
			$this->db->cache_on();
			$this->fields = $this->db->list_fields($this->primary_table);
			$this->db->cache_off();
		}
	}
	
	/**
	 * _required method returns false if the $data array does not contain all of the keys assigned by the $required array.
	 *
	 * @param array $required
	 * @param array $data
	 * @return bool
	 */
	function _required($required, $data)
	{
		foreach ($required as $field)
		{
			if ( ! isset($data[$field]))
			{
				return FALSE;
			}
		}
		return TRUE;
	}
	
	/**
	 * _default method combines the options array with a set of defaults giving the values in the options array priority.
	 *
	 * @param array $defaults
	 * @param array $options
	 * @return array
	 */
	function _default($defaults, $options)
	{
		return array_merge($defaults, $options);
	}
}