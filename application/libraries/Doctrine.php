<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Doctrine\Common\ClassLoader,
	Doctrine\ORM\Configuration,
	Doctrine\ORM\EntityManager,
	Doctrine\Common\Cache\ArrayCache,
	Doctrine\DBAL\Logging\EchoSQLLogger;

class Doctrine {

	public $entityManager = null;

	protected $CI;

	/**
	 * Doctrine constructor.
	 * @param array $dbConfig
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function __construct($dbConfig = array())
	{
		// load database configuration from CodeIgniter
		require_once APPPATH.'config/database.php';

        //  codeigniter super class
        $this->CI = &get_instance();

		$entitiesClassLoader = new ClassLoader('models', rtrim(APPPATH, "/" ));
		$entitiesClassLoader->register();
		$proxiesClassLoader = new ClassLoader('Proxies', APPPATH.'models/proxies');
		$proxiesClassLoader->register();

		// Set up caches
		$config = new Configuration;
		$cache = new ArrayCache;
		$config->setMetadataCacheImpl($cache);
		$driverImpl = $config->newDefaultAnnotationDriver(array(APPPATH.'models/Entities'));
		$config->setMetadataDriverImpl($driverImpl);
		$config->setQueryCacheImpl($cache);

		// Proxy configuration
		$config->setProxyDir(APPPATH.'/models/proxies');
		$config->setProxyNamespace('Proxies');

		$config->setAutoGenerateProxyClasses( TRUE );

		// Set up logger
		//$logger = new EchoSQLLogger;
		//$config->setSQLLogger($logger);

		// Database connection information
		$connectionOptions = null;

		if (!empty($dbConfig) &&
			is_array($dbConfig) &&
			count($dbConfig) !== 0) {

			$connectionOptions = $dbConfig;

		}
		else {

            /**
             * @var array $db
             */
			$connectionOptions = array(
				'driver' => 'pdo_mysql',
				'user' =>     $db['default']['username'],
				'password' => $db['default']['password'],
				'host' =>     $db['default']['hostname'],
				'dbname' =>   $db['default']['database'],
				'charset' =>  $db['default']['char_set']
			);

		}

		// Create EntityManager
		$this->entityManager = EntityManager::create($connectionOptions, $config);
	}
}
