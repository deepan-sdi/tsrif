<?php
	namespace Front\View\Helper;
	use Zend\View\Helper\AbstractHelper;
	
	class Text extends AbstractHelper
	{
	    public function __invoke($var, $length, $function)
	    {
			return $this->$function($var, $length);
	    }
		
		public function displayText($var, $length) {
			if(trim($var) != '' && strlen($var) > $length) {
				$var	= substr($var, 0, $length).'...';
			}
			return $var;
		}
		
	}	?>