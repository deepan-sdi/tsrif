<?php
// module/Cms/src/Cms/Form/CreateUserForm.php:
namespace Cms\Form;

use Zend\Form\Form;

class CreateUserForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('cms');
        $this->setAttribute('method', 'post');
		$this->setAttribute('class', 'form-horizontal');
		$this->setAttribute('name', 'addUser');
		$this->setAttribute('id', 'addUser');
		//	User Id
		$this->add(array(
            'name' => '_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		//	Group
		$this->add(array(
             'type' => 'Zend\Form\Element\Select',
             'name' => 'user_group'
	    ));
		//	Firstname
		$this->add(array(
            'name' => 'user_firstname',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'firstName',
				'class'	=> 'input-large',
				'autofocus'	=> '',
				'maxlength'	=> '200',
            ),
            'options' => array(
            ),
        ));
		//	Lastname
		$this->add(array(
            'name' => 'user_lastname',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'lastName',
				'class'	=> 'input-large',
				'maxlength'	=> '200',
            ),
            'options' => array(
            ),
        ));
		//	Email
		$this->add(array(
            'name' => 'user_email',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'userEmail',
				'style'	=> 'width:210px;',
				'maxlength'	=> '255',
            ),
            'options' => array(
            ),
        ));
		//	Password
		$this->add(array(
            'name' => 'user_password',
            'attributes' => array(
                'type'  => 'password',
				'id'	=> 'userPassword',
				'class' => 'input-large',
				'maxlength'	=> '255',
            ),
            'options' => array(
            ),
        ));
		//	FBUID
		$this->add(array(
            'name' => 'user_fbuid',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'fbuid',
				'class'	=> 'input-large',
				'autofocus'	=> '',
				'maxlength'	=> '200',
            ),
            'options' => array(
            ),
        ));
		//	Gender
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'user_gender',
            'options' => array(
                'value_options' => array(
                    '1' => 'Male',
                    '2' => 'Female',
                ),
            ),
            'attributes' => array(
                'value' => '1', //set checked to '1'
				'style' => 'opacity: 0;',
				'class' => 'radio inline'
            )
        ));
		//	DOB
		$this->add(array(
            'name' => 'user_dob',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'user_dob',
				'class'	=> 'input-large datepicker',
				'autofocus'	=> '',
				'maxlength'	=> '200',
				'readonly' => 'readonly',
            ),
            'options' => array(
            ),
        ));
		//	Status
		$this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'user_status',
            'options' => array(
                'value_options' => array(
                    '1' => 'Active',
                    '0' => 'Inactive',
                ),
            ),
            'attributes' => array(
                'value' => '1', //set checked to '1'
				'style' => 'opacity: 0;',
				'class' => 'radio inline'
            )
        ));
		//	Submit
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Create User',
				'class'	=> 'btn btn-primary',
            ),
        ));
		//	Reset
		$this->add(array(
			'name'	=> 'reset',
            'attributes' => array(
                'type'  => 'reset',
                'value' => 'Cancel',
				'class'	=> 'btn',
            ),
        ));
    }
}
?>