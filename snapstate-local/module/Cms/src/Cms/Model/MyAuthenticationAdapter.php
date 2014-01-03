<?php
namespace Cms\Model;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Db\Adapter\Adapter as DbAdapter;

class MyAuthenticationAdapter implements AdapterInterface
{
	private $username;
	private $password;
	private $dbAdapter;
	public $authAdapter;

	public function __construct($usr, $psw){
		$this->username = $usr;
		$this->password = $psw;
	}
	
	public function setDbAdapter($dbAdapter) 
	{
		$this->dbAdapter = $dbAdapter;
	}
	
	/**
	 * Performs an authentication attempt
	 *
	 * @return Array([0] =>\Zend\Authentication\Result, [1] => array())
	 * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
	 *               If authentication cannot be performed
	 */
	public function authenticate()
    {    	
    	// Configure the instance with constructor parameters...
    	$this->authAdapter = new AuthAdapter($this->dbAdapter,
    			'users',
    			'user_email',
    			'user_password'
    	);	
    	
		$select = $this->authAdapter->getDbSelect();
    	$select->where('user_status= "1"');
		
    	$this->authAdapter->setIdentity($this->username)
    				->setCredential($this->password)
    				->setCredentialTreatment('MD5(?)');
    	$result = $this->authAdapter->authenticate();
    	
    	return $result;
    }
    
    public function getResultRowObject($returnColumns = null, $omitColumns = null) 
    {
    	return $this->authAdapter->getResultRowObject($returnColumns, $omitColumns);
    }
}