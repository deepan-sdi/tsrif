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

class FaqTable extends AbstractTableGateway
{
    protected $table = 'faq';
	
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Faq());
        $this->initialize();
    }
	
	public function deleteFaq($id)
    {
		$this->deleteFaqValues($id);
        $sql		= "DELETE FROM faq WHERE faq_id = " . $id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
    }
	
	public function deleteFaqValues($id)
    {
        $sql		= "DELETE FROM faq_values WHERE faq_id = " . $id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
    }
	
	public function insertFaq($data) {
		$sql	= "insert into faq set faq_status = ".$data['faq_status'].", carrier_id = ".$data['carrier_id'] ;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
	}
	
	public function updateFaq($data) {
		$sql	= "UPDATE faq SET faq_status = " . $data['faq_status'] . " WHERE faq_id = " . $data['faq_id'];
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
	}
	
	public function insertFaqValues($data) {
		$sql	= "insert into faq_values set
							faq_id			= " . $data['faq_id'] . ",
							faq_question	= '". addslashes($data['faq_question']) ."',
							faq_answer		= '". addslashes($data['faq_answer']) ."',
							language_id		= " . $data['language_id'];
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
	}
	
	public function updateFaqValues($data) {
		$result	= $this->getFaqValue($data['faq_id'], $data['language_id']);
		if($result) {
			$sql	= "UPDATE faq_values SET
								faq_question	= '". addslashes($data['faq_question']) ."',
								faq_answer		= '". addslashes($data['faq_answer']) ."'
							WHERE faq_id = " . $data['faq_id'] . " AND language_id = " . $data['language_id'];
		} else {
			$sql	= "INSERT INTO faq_values SET
								faq_question	= '". addslashes($data['faq_question']) ."',
								faq_answer		= '". addslashes($data['faq_answer']) ."',
								faq_id = " . $data['faq_id'] . ",
								language_id = " . $data['language_id'];
		}
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
	public function getFaqValue($id, $lid) {
		$sql		= "SELECT faq_value_id FROM faq_values WHERE faq_id = ".$id." AND language_id = ".$lid;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		$resultValue = 0;
        foreach($result as $key => $value) {
			$resultValue = 1;
		}
        return $resultValue;
	}
	
	public function getFaq($id) {
		$sql		= "SELECT val.faq_value_id, val.faq_question, val.faq_answer, val.language_id, 
							faq.faq_status FROM faq_values AS val
						JOIN faq AS faq ON faq.faq_id = val.faq_id 	
						WHERE faq.faq_id = ".$id;
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $result;
	}
	
	public function getFaqList()
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
		
		if($listingSession->offsetExists('status') && $listingSession->status != '') {
			$whereClause	.= ' AND a.faq_status = ' . $listingSession->status;
		}
		$categoryFlag = 0;
		if($listingSession->offsetExists('field') && $listingSession->field != '' && $listingSession->offsetExists('keyword') && $listingSession->keyword != '') {
			$whereClause	.= ' AND ' . $listingSession->field . ' like "%' . addslashes($listingSession->keyword) . '%"';
			$categoryFlag	= ($listingSession->field == 'f.category_value') ? 1 : 0;
		}
		
		if($listingSession->offsetExists('sortBy')) {
			$orderClause	.= ' ORDER BY '.$listingSession->sortBy;
		}
		
		if($listingSession->offsetExists('sortType') && $listingSession->sortType == 1) {
			$orderClause	.= ' DESC';
		}
		
		$sql	= 'SELECT a.*, b.* FROM `faq` AS a
					JOIN faq_values AS b ON a.faq_id = b.faq_id
					JOIN carrier AS c ON c.carrier_language = b.language_id';
		
		$sql	.= ' WHERE c.carrier_id = ' . $carrierFlag . $whereClause . $orderClause;
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		$result		= $this->resultSetPrototype->initialize($result);
		$result->buffer();
		$result->next();
		return $result;
	}
}