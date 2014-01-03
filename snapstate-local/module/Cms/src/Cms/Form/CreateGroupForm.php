<?php
// module/Cms/src/Cms/Form/CreateUserForm.php:
namespace Cms\Form;

use Zend\Form\Form;

class CreateGroupForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('cms');
        $this->setAttribute('method', 'post');
		$this->setAttribute('class', 'form-horizontal');
		$this->setAttribute('name', 'createGroupForm');
		$this->setAttribute('id', 'createGroupForm');
		$this->add(array(
            'name' => '_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		
        $this->add(array(
            'name' => 'group_name',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'carrierName',
				'class'	=> 'input-large',
				'autofocus'	=> '',
				'maxlength'	=> '50',
            ),
            'options' => array(
            ),
        ));
		//	Roles
		$this->add(array(
             'type' => 'Zend\Form\Element\Select',
             'name' => 'group_role'
	    ));
		$this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'group_status',
            'options' => array(
                'value_options' => array(
                    '1' => 'Active',
                    '0' => 'Inactive',
                ),
            ),
            'attributes' => array(
                'value' => '1', //set checked to '1'
				'class' => 'radio inline'
            )
        ));
		
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Create Group',
				'class'	=> 'btn btn-primary',
            ),
        ));
		
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