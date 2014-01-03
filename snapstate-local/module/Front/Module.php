<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Front;

//	Cache
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

//	Models
use Front\Model\Users;
use Front\Model\UsersTable;

use Front\Model\Group;


class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{
   	public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getServiceManager()->get('translator');
		$eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
		$e->getApplication()->getEventManager()->attach(
			'dispatch',
			function($e) {
				$routeMatch = $e->getRouteMatch();
				$viewModel = $e->getViewModel();
				$viewModel->setVariable('controller', $routeMatch->getParam('controller'));
				$viewModel->setVariable('action', $routeMatch->getParam('action'));
			}, -100);
    }
	
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
	
    public function getAutoloaderConfig()
    {
		return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
	
	public function getServiceConfig()
    {
        return array(
            'factories' => array(
				/*	'Cms\Model\Usermo' =>  function($sm) {
                    $table     = new Usermo();
                    return $table;
                },	*/
				'db-adapter' => function($sm) {
					return $sm->get('db');
				},
				'cache-adapter' => function($sm) {
					return $sm->get('globalcache');
				},
            ),
        );
    }
	
	public function getViewHelperConfig()
	{
	   return array(
	         'factories' => array(
				'text' => function($sm) {
                    $helper = new View\Helper\Text;
                    return $helper;
                },
				'customurl' => function($sm) {
                    $helper = new View\Helper\Customurl;
                    return $helper;
                },
				'Requesthelper' => function($sm){
				   $helper = new View\Helper\Requesthelper;
				   $request = $sm->getServiceLocator()->get('Request');
				   $helper->setRequest($request);
				   return $helper;
				}
	         ),
	   );
	}
}
