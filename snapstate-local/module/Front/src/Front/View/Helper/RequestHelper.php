<?php	
	namespace Cms\View\Helper;
	use Zend\View\Helper\AbstractHelper;
	
	class Requesthelper extends AbstractHelper
	{
	    protected $request;
	    
	    //get Request
	    public function setRequest($request)
	    {
	        $this->request = $request;    
	    }
	    
	    public function getRequest()
	    {
	        return $this->request;    
	    }
	    
	    public function __invoke()
	    {
	        return $this->getRequest()->getServer()->get('QUERY_STRING');     
	    }
	}
 ?>