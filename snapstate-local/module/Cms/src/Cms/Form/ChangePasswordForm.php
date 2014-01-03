<?php
namespace Cms\Form;

use Zend\Form\Form;

class ChangePasswordForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('cms');
        $this->setAttribute('method', 'post');
		$this->setAttribute('class', 'form-horizontal');
		
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
				'autofocus' => '',
                'type'  => 'password',
				'id'	=> 'password',
				'class' => 'input-large',
				'maxlength'	=> '255',
            ),
            'options' => array(
            ),
        ));
		$this->add(array(
            'name' => 'newpassword',
            'attributes' => array(
                'type'  => 'password',
				'id'	=> 'newpassword',
				'class' => 'input-large',
				'maxlength'	=> '255',
            ),
            'options' => array(
            ),
        ));
		$this->add(array(
            'name' => 'confirmpassword',
            'attributes' => array(
                'type'  => 'password',
				'id'	=> 'confirmpassword',
				'class' => 'input-large',
				'maxlength'	=> '255',
            ),
            'options' => array(
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Save changes',
				'class'	=> 'btn btn-primary',
            ),
        ));
		$this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type'  => 'reset',
                'value' => 'Cancel',
				'class'	=> 'btn',
            ),
        ));
    }
}
?>