<?php
namespace Cms\Model;
use Zend\Db\Adapter\Adapter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Group implements InputFilterAwareInterface
{
	private $_dbAdapter;
	
	public function setDbAdapter($dbAdapter) {
        $this->_dbAdapter = $dbAdapter;
    }
	
    public function getDbAdapter() {
        return $this->_dbAdapter;
    }
	
	public function exchangeArray($data)
    {
       
    }
	
	public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
	
	
	public function getInputFilter()
    {
        if (!isset($this->inputFilter) || !($this->inputFilter)) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();
			
            $inputFilter->add($factory->createInput(array(
                'name'     => 'group_name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
               'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 200,
                        ),
                    ),
                ),
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'group_role',
                'required' => true
            )));
			$inputFilter->add($factory->createInput(array(
                'name'     => 'group_status',
                'required' => true
            )));
            
			$this->inputFilter = $inputFilter;
        }
		
        return $this->inputFilter;
    }
	
	public function getArrayCopy()
    {
        return get_object_vars($this);
    }
	
	
}