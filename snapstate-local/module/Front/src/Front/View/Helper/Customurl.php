<?php
	namespace Front\View\Helper;
	use Zend\View\Helper\AbstractHelper;
	
	class Customurl extends AbstractHelper
	{
	    public function __invoke($var, $function)
	    {
			return $this->$function($var);
	    }
		
		public function fetchYoutubeID($url) {
			if(trim($url) != '') {
				$urlArray	= parse_url($url);
				$url		= (isset($urlArray['query']) && trim($urlArray['query']) != '') ? str_replace('v=', '', $urlArray['query']) : '';
			}
			return $url;
		}
		
	}	?>