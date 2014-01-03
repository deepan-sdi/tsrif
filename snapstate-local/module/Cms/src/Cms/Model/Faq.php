<?php
namespace Cms\Model;
use Zend\Db\Adapter\Adapter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Faq implements InputFilterAwareInterface
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
		$this->value_id 		= (isset($data['value_id'])) ? $data['value_id'] : null;
		$this->language_id 		= (isset($data['language_id'])) ? $data['language_id'] : null;
		$this->faq_id 			= (isset($data['faq_id'])) ? $data['faq_id'] : null;
		$this->faq_question		= (isset($data['faq_question'])) ? $data['faq_question'] : null;
		$this->faq_answer 		= (isset($data['faq_answer'])) ? $data['faq_answer'] : null;
		$this->faq_status		= (isset($data['faq_status'])) ? $data['faq_status'] : null;
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