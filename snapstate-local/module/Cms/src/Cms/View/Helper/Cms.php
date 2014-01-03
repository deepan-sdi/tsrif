<?php	
	// Application/View/Helper/ControllerName.php
	namespace Cms\View\Helper;
	
	use Zend\View\Helper\AbstractHelper;
	
	class Cms extends AbstractHelper
	{
	
	protected $routeMatch;
	
	    public function __construct($routeMatch)
	    {
	        $this->routeMatch = $routeMatch;
	    }
	
	    public function __invoke()
	    {
			$controller = $this->routeMatch->getParam('controller', 'index');
			$action = $this->routeMatch->getParam('action', 'index');
			$param	= array('controller' => $controller, 'action' => $action);
	        return $param;
	    }
	} ?>