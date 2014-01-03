<?php
/**
 * Global Configuration 
 */

return array(
    'db' => array(
        'driver'         => 'Pdo',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
            'Zend\Cache\Storage\Filesystem' => function($sm){
			    $cache = Zend\Cache\StorageFactory::factory(array(
				'adapter' => 'filesystem',
				'plugins' => array(
				    'exception_handler' => array('throw_exceptions' => false),
				    'serializer'
				)
			    ));
			    $cache->setOptions(array(
				    'cache_dir' => './data/cache',
					'ttl'       => 60 * 60 * 24,
			    ));
	            return $cache;
		    },
        ),
		'aliases' => array(
			'db' => 'Zend\Db\Adapter\Adapter',
			'globalcache'	=> 'Zend\Cache\Storage\Filesystem',
		),
    ),
);
