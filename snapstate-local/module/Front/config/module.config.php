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
                    'route' => ':subdomain.sdiphp.com',
					//'route'    => '/',
					//'route' => ':subdomain.localvestaapp.com',
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
            'front' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/front[/[:controller[/[:action[/[:id]]]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                    		'route'    => '/front[/[:controller[/[:action[/[:id[/[:msg]]]]]]]]',
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
			'friends' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/friends',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Friends',
                        'action'        => 'friends',
                    ),
                ),
            ),
			'list-watched' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/list-watched[/[:id[/[:perPage]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Search',
                        'action'        => 'list-watched',
						'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'perPage' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'videos-watched' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/videos-watched',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Search',
                        'action'        => 'view-watched',
                    ),
                ),
            ),
			'video' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/video[/[:id]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Search',
                        'action'        => 'view-video',
						'id' 			=> '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'top-videos' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/top-videos',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Search',
                        'action'        => 'top-videos',
                    ),
                ),
            ),
			'recommended-videos' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/recommended-videos',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Search',
                        'action'        => 'view-recommended',
                    ),
                ),
            ),
			'search' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/search',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Search',
                        'action'        => 'filter',
                    ),
                ),
            ),
			'list-contributed' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/list-contributed[/[:id[/[:perPage]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Search',
                        'action'        => 'list-contributed',
						'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'perPage' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'suggested-videos' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/suggested-videos[/[:id[/[:perPage]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Search',
                        'action'        => 'contributed-videos',
						'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'perPage' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'add-to-playlist' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/add-to-playlist[/[:id[/[:perPage]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Search',
                        'action'        => 'add-to-playlist',
						'id' 			=> '[a-zA-Z][a-zA-Z0-9_-]*',
						'perPage'		=> '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'playlist' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/playlist[/[:id]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Search',
                        'action'        => 'playlist',
						'id' 			=> '[a-zA-Z][a-zA-Z0-9_-]*',
						'perPage'		=> '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'media-list' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/media-list[/[:sortBy[/[:sortType]]]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
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
                        '__NAMESPACE__' => 'Front\Controller',
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
                        '__NAMESPACE__' => 'Front\Controller',
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
                        '__NAMESPACE__' => 'Front\Controller',
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
                        '__NAMESPACE__' => 'Front\Controller',
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
                        '__NAMESPACE__' => 'Front\Controller',
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
                        '__NAMESPACE__' => 'Front\Controller',
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
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'User',
                        'action'        => 'view-group',
						'perPage' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                ),
            ),
			'fbreturn' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/fbreturn/[:slug]',
                    'constraints' => array(
	                    'slug' => '[a-zA-Z][a-zA-Z0-9_\/-]*'
                    ),
	                'defaults' => array(
                    	'controller'=> 'Front\Controller\Index',
	                    'action'	=> 'fbreturn',
    	                'slug'		=> '[a-zA-Z][a-zA-Z0-9_\/-]*'
                    ),
                ),
            ),
			'activate' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/activate[/[:id]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Front\Controller',
                        'controller'    => 'Index',
                        'action'        => 'activate',
						'id'			=> '[a-zA-Z][a-zA-Z0-9_-]*',
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
            'Front\Controller\Index'	=> 'Front\Controller\IndexController',
			'Front\Controller\Search'	=> 'Front\Controller\SearchController',
			'Front\Controller\Friends'	=> 'Front\Controller\FriendsController',
        ),
    ),
	
	'view_helpers' => array(
		'invokables' => array(
			'text'		=> 'Front\View\Helper\Text',
			'customurl'	=> 'Front\View\Helper\Customurl',
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
            'front/index/index' 		=> __DIR__ . '/../view/front/index/index.phtml',
            'error/404'             => __DIR__ . '/../view/error/404.phtml',
            'error/index'           => __DIR__ . '/../view/error/index.phtml',
			'frontend'				=> __DIR__ . '/../view/layout/layout.phtml',
        ),
        'template_path_stack'		=> array(
            __DIR__ . '/../view',
        ),
    ),
);
