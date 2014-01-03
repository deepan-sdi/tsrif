<?php
// module/Cms/src/Cms/Form/LoginForm.php:
namespace Cms\Form;

use Zend\Form\Form;

class ForgetPasswordForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('cms');
        $this->setAttribute('method', 'post');
		$this->setAttribute('class', 'form-horizontal');
		
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'email',
				'class'	=> 'input-large span10',
				'autofocus'	=> '',
				'maxlength'	=> '255',
            ),
            'options' => array(
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'button',
                'value' => 'Reset Password',
				'class'	=> 'btn btn-primary',
				'onclick'	=> 'return validateEmail();',
            ),
        ));
    }
}
?>