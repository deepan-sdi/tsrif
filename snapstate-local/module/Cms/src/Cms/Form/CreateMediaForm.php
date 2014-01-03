<?php
namespace Cms\Form;
use Zend\Form\Form;

class CreateMediaForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('cms');
        $this->setAttribute('method', 'post');
		$this->setAttribute('class', 'form-horizontal');
		$this->setAttribute('name', 'createMediaForm');
		$this->setAttribute('id', 'createMediaForm');
		//$this->setAttribute('onsubmit', 'return validateDesc();');
		//	Id
		$this->add(array(
            'name' => '_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		//	Approved Date
		$this->add(array(
            'name' => 'approvedDate',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		//	Title
		$this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'media_title',
            'attributes' => array(
                'class' => 'autogrow',
				'style' => 'width:500px;',
				'id'	=> 'media_title',
				'rows'	=> 2
            )
        ));
		//	URL
		$this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'media_url',
            'attributes' => array(
                'class' => 'autogrow',
				'style' => 'width:500px;',
				'id'	=> 'media_url',
				'rows'	=> 2
            )
        ));
		//	Category
		$this->add(array(
             'type' => 'Zend\Form\Element\Select',
             'name' => 'media_category'
	    ));
		//	Description
		$this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'media_description',
            'attributes' => array(
                'class' => 'cleditor',
				'style' => 'opacity: 0;',
				'id'	=> 'media_description',
				'rows'	=> 2
            )
        ));
		//	Tags
		$this->add(array(
             'type' => 'Zend\Form\Element\Select',
             'name' => 'media_tags',
			 'attributes' => array(
				'multiple' => 'multiple',
			 ),
	    ));
		//	Approved
		$this->add(array(
             'type' => 'Zend\Form\Element\Checkbox',
             'name' => 'media_approved',
			 'attributes' => array(
				 'id' => 'media_approved',
				 'style' => 'opacity: 0;margin-left:0px;',
				 'checked'=> '0',
			 ),
             'options' => array(
                     'checked_value' => '1',
                     'unchecked_value' => '0'
             )
	    ));
		//	Status
		$this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'media_status',
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
		//	Submit
        $this->add(array(
            'name' 			=> 'submit',
            'attributes' 	=> array(
                'type'  	=> 'submit',
                'value'		=> 'Create Media',
				'class'		=> 'btn btn-primary',
				//'onclick'	=> 'return validateDesc();',
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