<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'default' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/[:slug]',
                    'constraints' => array(
                    'slug' => '[a-zA-Z][a-zA-Z0-9_\/-]*'
                    ),
                'defaults' => array(
                    'controller'=> 'Front\Controller\Index',
                    'action'	=> 'index',
                    'slug'		=> 'home'
                    ),
                ),
            ),
			'home' => array(
                //'type' => 'Zend\Mvc\Router\Http\Literal',
				'type' => 'Hostname',
                'options' => array(
                    //'route'    => '/',
					'route' => ':subdomain.sdiphp.com',
                    'defaults' => array(
                        'controller' => 'Front\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'cms' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/cms[/[:controller[/[:action[/[:id]]]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                    		'route'    => '/cms[/[:controller[/[:action[/[:id[/[:msg]]]]]]]]',
							'constraints' => array(
                        		'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        		//'id'     => '[0-9]+',
								'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'msg' => '[a-zA-Z][a-zA-Z0-9_-]*',
							),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
			'group-list' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/group-list[/[:sortBy[/[:sortType]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'User',
                        'action'        => 'view-group',
						'sortBy' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'sortType' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'user-list' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/user-list[/[:sortBy[/[:sortType]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'User',
                        'action'        => 'view-user',
						'sortBy' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'sortType' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'role-list' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/role-list[/[:sortBy[/[:sortType]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'User',
                        'action'        => 'view-role',
						'sortBy' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'sortType' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'category-list' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/category-list[/[:sortBy[/[:sortType]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'Media',
                        'action'        => 'view-category',
						'sortBy'		=> '[a-zA-Z][a-zA-Z0-9_-]*',
						'sortType'		=> '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'media-list' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/media-list[/[:sortBy[/[:sortType]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'Media',
                        'action'        => 'view-media',
						'sortBy'		=> '[a-zA-Z][a-zA-Z0-9_-]*',
						'sortType'		=> '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'tag-list' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/tag-list[/[:sortBy[/[:sortType]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'Media',
                        'action'        => 'view-tag',
						'sortBy'		=> '[a-zA-Z][a-zA-Z0-9_-]*',
						'sortType'		=> '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'role-listcount' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/role-listcount[/[:perPage]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'User',
                        'action'        => 'view-role',
						'perPage' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'user-listcount' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/user-listcount[/[:perPage]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'User',
                        'action'        => 'view-user',
						'perPage' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'category-listcount' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/category-listcount[/[:perPage]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'Media',
                        'action'        => 'view-category',
						'perPage' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'media-listcount' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/media-listcount[/[:perPage]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'Media',
                        'action'        => 'view-media',
						'perPage' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'tag-listcount' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/tag-listcount[/[:perPage]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'Media',
                        'action'        => 'view-tag',
						'perPage' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'group-listcount' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/group-listcount[/[:perPage]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'User',
                        'action'        => 'view-group',
						'perPage' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'initializers' => array(
		function ($instance, $sm) {
		    if ($instance instanceof \Zend\Db\TableGateway\AbstractTableGateway) {
			$instance->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
		    }
		}
    ),
	'controllers' => array(
        'invokables' => array(
            'Cms\Controller\Index'	=> 'Cms\Controller\IndexController',
			'Cms\Controller\User'	=> 'Cms\Controller\UserController',
			'Cms\Controller\Media'	=> 'Cms\Controller\MediaController',
        ),
    ),
	
	'view_helpers' => array(
		'invokables' => array(
			'datetime'		=> 'Cms\View\Helper\Datetime',
		),  
	),
    'view_manager'					=> array(
		'display_not_found_reason'	=> true,
        'display_exceptions'		=> true,
        'doctype'					=> 'HTML5',
        'not_found_template'		=> 'error/404',
        'exception_template'		=> 'error/index',
        'template_map'				=> array(
            'layout/layout'         => __DIR__ . '/../view/layout/layout.phtml',
            'cms/index/index' 		=> __DIR__ . '/../view/cms/index/index.phtml',
            'error/404'             => __DIR__ . '/../view/error/404.phtml',
            'error/index'           => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack'		=> array(
            __DIR__ . '/../view',
        ),
    ),
);
