<?php
namespace Cms\Form;

use Zend\Form\Form;

class CreateRoleForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('cms');
        $this->setAttribute('method', 'post');
		$this->setAttribute('class', 'form-horizontal');
		$this->setAttribute('name', 'addRole');
		$this->setAttribute('id', 'addRole');
		//	Role Id
		$this->add(array(
            'name' => '_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		//	Name
		$this->add(array(
            'name' => 'role_name',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'roleName',
				'class'	=> 'input-large',
				'autofocus'	=> '',
				'maxlength'	=> '200',
            ),
            'options' => array(
            ),
        ));
		//	Activities
		$this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'role_activity_1',
            'options' => array(
				'checked_value' => '1',
                'unchecked_value' => '0',
			),
            'attributes' => array(
				'style' => 'opacity: 0;margin-left:0px;',
				'id'	=> 'role_activity_1',
            ),
        ));
		$this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'role_activity_2',
            'options' => array(
				'checked_value' => '1',
                'unchecked_value' => '0',
			),
            'attributes' => array(
				'style' => 'opacity: 0;margin-left:0px;',
				'id'	=> 'role_activity_2',
            ),
        ));
		$this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'role_activity_3',
            'options' => array(
				'checked_value' => '1',
                'unchecked_value' => '0',
			),
            'attributes' => array(
				'style' => 'opacity: 0;margin-left:0px;',
				'id'	=> 'role_activity_3',
            ),
        ));
		$this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'role_activity_4',
            'options' => array(
				'checked_value' => '1',
                'unchecked_value' => '0',
			),
            'attributes' => array(
				'style' => 'opacity: 0;margin-left:0px;',
				'id'	=> 'role_activity_4',
            ),
        ));
		$this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'role_activity_5',
            'options' => array(
				'checked_value' => '1',
                'unchecked_value' => '0',
			),
            'attributes' => array(
				'style' => 'opacity: 0;margin-left:0px;',
				'id'	=> 'role_activity_5',
            ),
        ));
		$this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'role_activity_6',
            'options' => array(
				'checked_value' => '1',
                'unchecked_value' => '0',
			),
            'attributes' => array(
				'style' => 'opacity: 0;margin-left:0px;',
				'id'	=> 'role_activity_6',
            ),
        ));
		$this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'role_activity_7',
            'options' => array(
				'checked_value' => '1',
                'unchecked_value' => '0',
			),
            'attributes' => array(
				'style' => 'opacity: 0;margin-left:0px;',
				'id'	=> 'role_activity_7',
            ),
        ));
		$this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'role_activity_8',
            'options' => array(
				'checked_value' => '1',
                'unchecked_value' => '0',
			),
            'attributes' => array(
				'style' => 'opacity: 0;margin-left:0px;',
				'id'	=> 'role_activity_8',
            ),
        ));
		$this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'role_activity_9',
            'options' => array(
				'checked_value' => '1',
                'unchecked_value' => '0',
			),
            'attributes' => array(
				'style' => 'opacity: 0;margin-left:0px;',
				'id'	=> 'role_activity_9',
            ),
        ));
		//	Status
		$this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'role_status',
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
                'value' => 'Create Role',
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