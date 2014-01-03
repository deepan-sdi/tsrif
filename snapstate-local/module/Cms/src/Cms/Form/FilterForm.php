<?php
namespace Cms\Form;

use Zend\Form\Form;

class FilterForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('Filter');
        $this->setAttribute('method', 'post');
		
        $this->add(array(
            'name' => 'keyword',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'Keyword',
				'placeholder'	=> 'Keyword',
				'class' => 'input-large',
				'onfocus'	=> "if(this.value == '') {this.value = '';}",
				'onblur'	=> "if (this.value == '') {this.value = '';}",
            ),
            'options' => array(
            ),
        ));
		
		$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'selectOption',
            'options' => array(
                'value_options' => array(
                    ''				=> 'Select Option',
                    'user_firstname'=> 'First Name',
                    'user_lastname' => 'Last Name',
					'user_email' 	=> 'Email'
                ),
            ),
            'attributes' => array(
                'value' => ''
            )
        ));
		
		$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'selectUserOption',
            'options' => array(
                'value_options' => array(
                    ''	=> 'Select Group',
                    '1' => 'Admin',
                    '2' => 'Contributor',
					'3'	=> 'User'
                ),
            ),
            'attributes' => array(
                'value'	=> ''
            )
        ));
		
		$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'selectLanguageOption',
            'options' => array(
                'value_options' => array(
                    '' => 'Select Option',
                    'language_title' => 'Language Name',
                    'language_code' => 'Language Code'
                ),
            ),
            'attributes' => array(
                'value' => ''
            )
        ));
		
		$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'selectFaqOption',
            'options' => array(
                'value_options' => array(
                    '' => 'Select Option',
                    'b.faq_question'	=> 'FAQ Question',
					'b.faq_answer'	=> 'FAQ Answer'
                ),
            ),
            'attributes' => array(
                'value' => ''
            )
        ));
		
		$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'selectGender',
            'options' => array(
                'value_options' => array(
                    '' => 'Select Gender',
                    '1' => 'Male',
                    '2' => 'Female'
                ),
            ),
            'attributes' => array(
                'value' => ''
            )
        ));
		
		$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'selectStatus',
            'options' => array(
                'value_options' => array(
                    '2' => 'Select Status',
                    '1' => 'Active',
                    '0' => 'Inactive'
                ),
            ),
            'attributes' => array(
                'value' => '2'
            )
        ));
		
		$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'approvalStatus',
            'options' => array(
                'value_options' => array(
                    '2' => 'Select Approval Status',
                    '1' => 'Approved',
                    '0' => 'Pending'
                ),
				'attributes' => array(
	                'value' => '2'
	            )
            )
        ));
		
		$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'categoryFilter'
        ));
		
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Filter',
				'class'	=> 'btn btn-primary',
            ),
        ));
		
		$this->add(array(
            'name' => 'reset',
            'attributes' => array(
                'type'  => 'reset',
                'value' => 'Reset',
				'class'	=> 'btn',
            ),
        ));
    }
}
?>