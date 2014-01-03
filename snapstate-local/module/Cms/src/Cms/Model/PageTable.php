<?php
namespace Cms\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;

//	Session
use Zend\Session\Container;

//	Auth
use Zend\Authentication,
	Zend\Authentication\Result,
	Zend\Authentication\AuthenticationService;

class PageTable extends AbstractTableGateway
{
    protected $table = 'page_values';
	
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Page());
        $this->initialize();
    }
	
	public function updateFaqCategory($data, $where) {
		$this->update($data, $where);
	}
	
	public function insertFaq($data) {
		$sql	= "insert into faq set faq_status = ".$data['faq_status'];
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
	}
	
	public function updateFaqValues($data) {
		$sql	= "UPDATE faq_values SET
							faq_question	= '". addslashes($data['faq_question']) ."',
							faq_answer		= '". addslashes($data['faq_answer']) ."',
							faq_category_id	= " . $data['faq_category_id'] . "
						WHERE faq_id = " . $data['faq_id'] . " AND language_id = " . $data['language_id'];
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
	}
	
	public function updateVariables($formData, $id) {
		foreach($formData as $key => $value) {
			$sql		= "UPDATE page_values SET page_value = '".addslashes($value)."' WHERE language_id = ".$key." AND page_field = '".$id."'";
			$statement	= $this->adapter->query($sql);
			$result		= $statement->execute();
		}
	}
	
	public function updateTerms($formData, $id) {
		foreach($formData as $key => $value) {
			$sql		= "UPDATE terms SET content = '".addslashes($value)."' WHERE language_id = ".$key." AND carrier_id = ".$id;
			$statement	= $this->adapter->query($sql);
			$result		= $statement->execute();
		}
	}
	
	public function getCategory($id) {
		$sql		= "SELECT val.value_id, val.category_id, val.category_value, val.language_id, category.category_status FROM faq_category_values AS val
						JOIN faq_category AS category ON category.category_id = val.category_id
						WHERE category.category_id = ".$id;
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $result;
	}
	
	public function fetchVariables() {
		$sql		= "SELECT page_id, field_name, field_value FROM variables";
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $result;
	}
	
	public function insertVariables($content) {
		$sql		= "INSERT INTO page_values VALUES ".$content;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $result;
	}
	
	public function getTerms()
	{
		$userSession = new Container('user');
		if($userSession->offsetExists('carrier')) {
			$carrierFlag	= $userSession->carrier;
		}
		$sql	= "SELECT a.language_id, a.language_title, b.content, b.terms_id FROM languages as a
					JOIN terms as b on a.language_id = b.language_id
					WHERE a.carrier_id = ".$carrierFlag." AND a.language_status = 1
					GROUP BY b.terms_id";
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $result;
	}
	
	public function getPagesList()
	{
		$whereClause	= '';
		$auth 			= new AuthenticationService();
		$user 			= $auth->getIdentity();
		$whereClause	.= '';
		
		$userSession = new Container('user');
		if($userSession->offsetExists('carrier')) {
			$carrierFlag	= $userSession->carrier;
		}
		$listingSession = new Container('listing');
		$whereClause	= '';
		$orderClause	= '';
		
		if($listingSession->offsetExists('keyword') && $listingSession->keyword != '') {
			$whereClause	.= ' AND page_name like "%' . addslashes($listingSession->keyword) . '%"';
		}
		
		if($user->user_role_id == 3 && isset($user->user_access) && trim($user->user_access) != '') {
			$whereClause	.= ' AND page_id IN ('.str_replace('##', ',', $user->user_access).')';
		} else if($user->user_role_id == 3 && isset($user->user_access) && trim($user->user_access) == '') {
			$whereClause	.= ' AND page_id = 0';
		}
		
		if($listingSession->offsetExists('sortBy')) {
			$orderClause	.= ' ORDER BY '.$listingSession->sortBy;
		} else {
			$orderClause	.= ' ORDER BY page_name';
		}
		
		if($listingSession->offsetExists('sortType') && $listingSession->sortType == 1) {
			$orderClause	.= ' DESC';
		}
		
		$sql	= 'SELECT a.page_name, a.page_id FROM new_pages AS a
					WHERE 1'. $whereClause . $orderClause;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		$result		= $this->resultSetPrototype->initialize($result);
		$result->buffer();
		$result->next();
		return $result;
	}
	
	/*	public function getPagesList()
	{
		$whereClause	= '';
		$auth 			= new AuthenticationService();
		$user 			= $auth->getIdentity();
		$whereClause	.= '';
		
		$userSession = new Container('user');
		if($userSession->offsetExists('carrier')) {
			$carrierFlag	= $userSession->carrier;
		}
		$listingSession = new Container('listing');
		$whereClause	= '';
		$orderClause	= '';
		
		if($listingSession->offsetExists('keyword') && $listingSession->keyword != '') {
			$whereClause	.= ' AND page_type like "%' . addslashes($listingSession->keyword) . '%"';
		}
		
		if($listingSession->offsetExists('sortBy')) {
			$orderClause	.= ' ORDER BY '.$listingSession->sortBy;
		}
		
		if($listingSession->offsetExists('sortType') && $listingSession->sortType == 1) {
			$orderClause	.= ' DESC';
		}
		
		$sql	= 'SELECT a.page_type, a.page_id FROM `pages` AS a
					WHERE a.carrier_id = ' . $carrierFlag . $whereClause . $orderClause;
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		$result		= $this->resultSetPrototype->initialize($result);
		$result->buffer();
		$result->next();
		return $result;
	}	*/
	
	public function getPageType($id) 
	{
		$userSession = new Container('user');
		if($userSession->offsetExists('carrier')) {
			$carrierFlag	= $userSession->carrier;
		}
		$sql		= "SELECT page_name FROM new_pages WHERE page_id = ".$id;
		//$sql		= "SELECT page_field FROM page_values WHERE page_id = ".$id." AND carrier_id = ".$carrierFlag." GROUP BY page_field";
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $result;
	}
	public function getPageValesList()
	{
		$whereClause	= '';
		$auth 			= new AuthenticationService();
		$user 			= $auth->getIdentity();
		$whereClause	.= '';
		
		$userSession = new Container('user');
		if($userSession->offsetExists('carrier')) {
			$carrierFlag	= $userSession->carrier;
		}
		$listingSession = new Container('listing');
		$whereClause	= '';
		$orderClause	= '';
		
		if($listingSession->offsetExists('keyword') && $listingSession->keyword != '') {
			$whereClause	.= ' AND a.page_value like "%' . addslashes($listingSession->keyword) . '%"';
		}
		
		if($listingSession->offsetExists('sortBy')) {
			$orderClause	.= ' ORDER BY '.$listingSession->sortBy;
		}
		
		if($listingSession->offsetExists('sortType') && $listingSession->sortType == 1) {
			$orderClause	.= ' DESC';
		}
		
		$sql	= 'SELECT a.page_field FROM `page_values` AS a 
					JOIN carrier as b ON b.carrier_language = a.language_id
					WHERE a.carrier_id = '.$carrierFlag.' AND a.page_id = ' . $listingSession->page_type . $whereClause . 
					' GROUP BY a.page_field' . $orderClause;
		/*	if($_SERVER['REMOTE_ADDR'] == '115.248.201.61') {
			echo '<br>==>'.__LINE__.'==>'.__FILE__.'==>'.$sql;
		}	*/
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		$result		= $this->resultSetPrototype->initialize($result);
		$result->buffer();
		$result->next();
		return $result;
	}
	/*	public function getPageValesList()
	{
		$whereClause	= '';
		$auth 			= new AuthenticationService();
		$user 			= $auth->getIdentity();
		$whereClause	.= '';
		
		$userSession = new Container('user');
		if($userSession->offsetExists('carrier')) {
			$carrierFlag	= $userSession->carrier;
		}
		$listingSession = new Container('listing');
		$whereClause	= '';
		$orderClause	= '';
		
		if($listingSession->offsetExists('keyword') && $listingSession->keyword != '') {
			$whereClause	.= ' AND a.page_field like "%' . addslashes($listingSession->keyword) . '%"';
		}
		
		if($listingSession->offsetExists('sortBy')) {
			$orderClause	.= ' ORDER BY '.$listingSession->sortBy;
		}
		
		if($listingSession->offsetExists('sortType') && $listingSession->sortType == 1) {
			$orderClause	.= ' DESC';
		}
		
		$sql	= 'SELECT a.page_field, a.field_id, b.page_type FROM `page_values` AS a
					JOIN pages as b on b.page_id = a.page_id
					WHERE b.carrier_id = '.$carrierFlag.' AND a.page_id = ' . $listingSession->page_type . $whereClause . 
					' GROUP BY a.page_field' . $orderClause;
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		$result		= $this->resultSetPrototype->initialize($result);
		$result->buffer();
		$result->next();
		return $result;
	}	*/
	public function getFieldValues($id)
	{
		$userSession = new Container('user');
		if($userSession->offsetExists('carrier')) {
			$carrierFlag	= $userSession->carrier;
		}
		$sql		= "SELECT val.page_field, val.page_value, lang.language_title, val.page_value_id, val.language_id FROM page_values AS val
						JOIN languages AS lang ON lang.language_id = val.language_id
						WHERE val.page_field = '".$id."' AND lang.language_status = 1 AND lang.carrier_id = ".$carrierFlag;
		
		/*	$sql		= "SELECT val.page_field, val.page_value, lang.language_title, val.page_value_id, val.language_id FROM page_values AS val
						JOIN languages AS lang ON lang.language_id = val.language_id
						WHERE val.field_id = ".$id." AND lang.language_status = 1 AND lang.carrier_id = ".$carrierFlag;	*/
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $result;
	}
}