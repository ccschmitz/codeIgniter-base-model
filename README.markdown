# CodeIgniter Base Model

Here is the base model that I use on all of my CodeIgniter projects. This provides all of the basic CRUD functionality I need just by extending my models to the MY_Model class and defining a few variables:

		class Company_model extends MY_Model {

		    var $primary_table = 'companies';
		
		    var $validate_field_existence = TRUE;
				
		    var $fields = array(
		        'id',
		        'name',
		        'address',
		        'city',
		        'state',
		        'zipcode',
		        'phone',
		        'is_active',
		        'date_created',
		        'date_modified'
		    );
		
		    var $required_fields = array(
		        'name',
		        'address',
		        'city',
		        'state',
		        'zipcode',
		        'phone'
		    );

		}
		
* **primary_table** - The name of the table the model should execute queries on.
* **validate_field_existence** - Set to true to turn on field existence validation.
* **fields**  - An array of the fields in the database that the model has access to.  If you don't specify the fields here they will be pulled dynamically from the database and the query will be cached.
* **required_fields** An array of fields that must be submitted any time a record is created or updated.

Inserting records works like this:

		// put all form data into an array
		$options = array(
		    'name' => $this->input->post('company_name'),
		    'address' => $this->input->post('company_address'),
		    'city' => $this->input->post('company_city'),
		    'state' => $this->input->post('company_state'),
		    'zipcode' => $this->input->post('company_zip'),
		    'phone' => $this->input->post('company_phone'),
		    'is_active' => $this->input->post('company_is_active')
		);
		// send the array to the model
		$company = $this->company_model->add($options);

When selecting records, you can filter by any field specified in the fields array of the model:

		$options = array(
		    'name' => $this->input->post('search_name'),
		    'zipcode' => $this->input->post('search_zipcode')
		);
		$company = $this->company_model->get($options);
		
If you specify the primary key, the model knows you are looking for a single record and will return the object rather than the query result.

This model is based on a model by Shawn McCool from his article [How To Write A Better Model In CodeIgniter](). It has worked out pretty well for me so far but I would love to get some feedback from the community and see if anyone has ideas for improvements or if they have a base model that they enjoy working with.

The code is up on [GitHub]() if anyone would like to contribute.