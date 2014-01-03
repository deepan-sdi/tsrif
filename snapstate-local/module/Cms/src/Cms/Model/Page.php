<?php
namespace Cms\Model;
use Zend\Db\Adapter\Adapter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Page implements InputFilterAwareInterface
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
		
		$this->page_id			= (isset($data['page_id'])) ? $data['page_id'] : null;
		$this->page_name		= (isset($data['page_name'])) ? $data['page_name'] : null;
		
		$this->carrier_id		= (isset($data['carrier_id'])) ? $data['carrier_id'] : null;
		$this->page_type 		= (isset($data['page_type'])) ? $data['page_type'] : null;
		$this->page_status		= (isset($data['page_status'])) ? $data['page_status'] : null;
		
		$this->field_id			= (isset($data['field_id'])) ? $data['field_id'] : null;
		$this->page_value_id 	= (isset($data['page_value_id'])) ? $data['page_value_id'] : null;
		$this->page_field 		= (isset($data['page_field'])) ? $data['page_field'] : null;
		$this->page_value		= (isset($data['page_value'])) ? $data['page_value'] : null;
		$this->language_id 		= (isset($data['language_id'])) ? $data['language_id'] : null;
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
			
			$this->inputFilter = $inputFilter;
        }
		
        return $this->inputFilter;
    }
	
	public function getArrayCopy()
    {
        return get_object_vars($this);
    }
	
}