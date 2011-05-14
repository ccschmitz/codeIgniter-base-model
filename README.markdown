# CodeIgniter Base Model

Here is the base model that I use on all of my CodeIgniter projects. This provides all of the basic CRUD functionality I need just by extending my models to the MY_Model class and defining a few variables:

		class Company_model extends MY_Model {

		    var $primary_table = 'companies';
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
		
* The primary_table variable is the name of the table the model should execute queries on.
* The fields array is just a list of the fields in the database that the model has access to.
* The required_fields are fields that must be submitted any time a record is created or updated.

With this model in place I can just insert a record like this:

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
		
and I have a new record in the database!

Selecting records is a breeze too. You can select records by any field specified in the fields array of the model:

		$options = array( 
		    'id' => $this->uri->segment(4)
		);
		$company = $this->company_model->get($options);

This model is based on the model by Shawn McCool from his article How To Write A Better Model In CodeIgniter. It's worked out pretty well so far but I would love to get some feedback from the community and see if anyone has ideas for improvements or if they have a base model that they enjoy working with.