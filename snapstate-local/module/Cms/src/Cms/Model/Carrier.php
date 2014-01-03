<?php
namespace Cms\Model;
use Zend\Db\Adapter\Adapter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Group implements InputFilterAwareInterface
{
    public $carrier_id;
    public $carrier_name;
    public $carrier_logo;
	public $carrier_themecolor;
	public $carrier_banner;
	public $carrier_font;
	public $carrier_timezone;
	public $carrier_language;
	public $carrier_fbappid;
	public $carrier_fbkey;
	public $carrier_status;
	public $carrier_fbapp_name;
	public $carrier_fb_page;
	public $carrier_foryou_logo;
	public $carrier_forafriend_logo;
	public $carrier_ask_logo;
	public $carrier_backgroundcolor;
	public $carrier_buttoncolor;
	public $carrier_fontcolor;
	public $carrier_buttonhighlightcolor;
	public $carrier_topbanner;
	
	private $_dbAdapter;
	
	public function setDbAdapter($dbAdapter) {
        $this->_dbAdapter = $dbAdapter;
    }

    public function getDbAdapter() {
        return $this->_dbAdapter;
    }
	
	public function exchangeArray($data)
    {
        $this->carrier_id			= (isset($data['carrier_id'])) ? $data['carrier_id'] : null;
        $this->carrier_name			= (isset($data['carrier_name'])) ? $data['carrier_name'] : null;
        $this->carrier_logo			= (isset($data['carrier_logo'])) ? $data['carrier_logo'] : null;
		$this->carrier_themecolor	= (isset($data['carrier_themecolor'])) ? $data['carrier_themecolor'] : null;
		$this->carrier_backgroundcolor	= (isset($data['carrier_backgroundcolor'])) ? $data['carrier_backgroundcolor'] : null;
		$this->carrier_buttonhighlightcolor	= (isset($data['carrier_buttonhighlightcolor'])) ? $data['carrier_buttonhighlightcolor'] : null;
		$this->carrier_buttoncolor	= (isset($data['carrier_buttoncolor'])) ? $data['carrier_buttoncolor'] : null;
		$this->carrier_fontcolor	= (isset($data['carrier_fontcolor'])) ? $data['carrier_fontcolor'] : null;
		$this->carrier_banner		= (isset($data['carrier_banner'])) ? $data['carrier_banner'] : null;
		$this->carrier_timezone		= (isset($data['carrier_timezone'])) ? $data['carrier_timezone'] : null;
		$this->carrier_language		= (isset($data['carrier_language'])) ? $data['carrier_language'] : null;
		$this->carrier_fbappid		= (isset($data['carrier_fbappid'])) ? $data['carrier_fbappid'] : null;
		$this->carrier_fbkey		= (isset($data['carrier_fbkey'])) ? $data['carrier_fbkey'] : null;
		$this->carrier_status		= (isset($data['carrier_status'])) ? $data['carrier_status'] : null;
		$this->carrier_font			= (isset($data['carrier_font'])) ? $data['carrier_font'] : null;
		$this->carrier_fbapp_name	= (isset($data['carrier_fbapp_name'])) ? $data['carrier_fbapp_name'] : null;
		$this->carrier_fb_page		= (isset($data['carrier_fb_page'])) ? $data['carrier_fb_page'] : null;
		$this->carrier_foryou_logo	= (isset($data['carrier_foryou_logo'])) ? $data['carrier_foryou_logo'] : null;
		$this->carrier_forafriend_logo	= (isset($data['carrier_forafriend_logo'])) ? $data['carrier_forafriend_logo'] : null;
		$this->carrier_ask_logo		= (isset($data['carrier_ask_logo'])) ? $data['carrier_ask_logo'] : null;
		$this->carrier_topbanner	= (isset($data['carrier_topbanner'])) ? $data['carrier_topbanner'] : null;
		
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
                'name'     => 'carrier_name',
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
                'name'     => 'carrier_status',
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