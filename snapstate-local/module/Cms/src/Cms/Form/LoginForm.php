<?php
// module/Cms/src/Cms/Form/LoginForm.php:
namespace Cms\Form;

use Zend\Form\Form;

class LoginForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('cms');
        $this->setAttribute('method', 'post');
		$this->setAttribute('class', 'form-horizontal');
		
        $this->add(array(
            'name' => 'Email',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'login_email',
				'class'	=> 'input-large span10',
				'autofocus'	=> '',
				'maxlength'	=> '255',
            ),
            'options' => array(
            ),
        ));
        $this->add(array(
            'name' => 'Password',
            'attributes' => array(
                'type'  => 'password',
				'id'	=> 'password',
				'class' => 'input-large span10',
				'maxlength'	=> '255',
            ),
            'options' => array(
            ),
        ));
		$this->add(array(
             'type' => 'Zend\Form\Element\Checkbox',
             'name' => 'Remember',
			 'attributes' => array(
				 'id' => 'remember',
			 ),
             'options' => array(
                     'checked_value' => '1',
                     'unchecked_value' => '0'
             )
	    ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Login',
				'class'	=> 'btn btn-primary',
                'id' => 'submitbutton',
				'onclick'	=> 'return validateLogin();',
            ),
        ));
    }
}
?>