<?php
namespace Cms\Model;
use Zend\Db\Adapter\Adapter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Language implements InputFilterAwareInterface
{
    public $language_id;
    public $language_title;
    public $language_code;
	public $languageFlag;
	public $language_flag;
	public $carrier_id;
	public $language_status;
	public $language_default;
	
	private $_dbAdapter;
	
	public function setDbAdapter($dbAdapter) {
        $this->_dbAdapter = $dbAdapter;
    }
	
    public function getDbAdapter() {
        return $this->_dbAdapter;
    }
	
	public function exchangeArray($data)
    {
        $this->carrier_id		= (isset($data['carrier_id'])) ? $data['carrier_id'] : null;
        $this->language_id		= (isset($data['language_id'])) ? $data['language_id'] : null;
        $this->language_title	= (isset($data['language_title'])) ? $data['language_title'] : null;
		$this->language_code	= (isset($data['language_code'])) ? strtoupper($data['language_code']) : null;
		$this->language_flag	= (isset($data['language_flag'])) ? $data['language_flag'] : null;
		$this->language_flag_old= (isset($data['language_flag_old'])) ? $data['language_flag_old'] : null;
		$this->language_status	= (isset($data['language_status'])) ? $data['language_status'] : null;
		//$this->language_default	= (isset($data['language_default'])) ? $data['language_default'] : null;
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
                'name'     => 'language_title',
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
                            'max'      => 200,
                        ),
                    ),
                ),
            )));
			
			$inputFilter->add($factory->createInput(array(
                'name'     => 'language_code',
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
                            'max'      => 20,
                        ),
                    ),
                ),
            )));
			
			$inputFilter->add($factory->createInput(array(
                'name'     => 'languageFlag',
                'required' => true,
            )));
			
			$inputFilter->add($factory->createInput(array(
                'name'     => 'language_status',
                'required' => true
            )));
            
			$this->inputFilter = $inputFilter;
        }
		
        return $this->inputFilter;
    }
	
	public function getInputFilterEditLanguage()
    {
        if (!isset($this->inputFilter) || !($this->inputFilter)) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();
			
            $inputFilter->add($factory->createInput(array(
                'name'     => 'language_title',
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
                            'max'      => 200,
                        ),
                    ),
                ),
            )));
			
			$inputFilter->add($factory->createInput(array(
                'name'     => 'language_code',
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
                            'max'      => 20,
                        ),
                    ),
                ),
            )));
			
			/*	$inputFilter->add($factory->createInput(array(
                'name'     => 'languageFlag',
                'required' => true,
            )));	*/
			
			$inputFilter->add($factory->createInput(array(
                'name'     => 'language_status',
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