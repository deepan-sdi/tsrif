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

class LanguageTable extends AbstractTableGateway
{
    protected $table = 'languages';
	
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Language());
        $this->initialize();
    }
	
    public function getLanguage($id)
    {
        $id  = (int) $id;
        $rowset = $this->select(array('language_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
	
	public function getLanguagesList()
	{
		$whereClause	= '';
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();
		$whereClause	.= '';
		
		$userSession = new Container('user');
		if($userSession->offsetExists('carrier')) {
			$carrierFlag	= $userSession->carrier;
		}
		$listingSession = new Container('listing');
		
		$whereClause	.= ' AND carrier_id = ' . $carrierFlag;
		
		if($listingSession->offsetExists('status') && $listingSession->status != '') {
			$whereClause	.= ' AND language_status = ' . $listingSession->status;
		}
		
		if($listingSession->offsetExists('field') && $listingSession->field != '' && $listingSession->offsetExists('keyword') && $listingSession->keyword != '') {
			$whereClause	.= ' AND ' . $listingSession->field . ' like "' . addslashes($listingSession->keyword) . '%"';
		}
		
		$orderClause	= '';
		
		if($listingSession->offsetExists('sortBy')) {
			$orderClause	.= ' ORDER BY '.$listingSession->sortBy;
		}
		
		if($listingSession->offsetExists('sortType') && $listingSession->sortType == 1) {
			$orderClause	.= ' DESC';
		}
		
		$sql	= 'SELECT * FROM languages
					WHERE 1 ' . $whereClause . ' ' . $orderClause;
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		$result		= $this->resultSetPrototype->initialize($result);
		$result->buffer();
		$result->next();
		return $result;
	}
	public function languageTerms($languageId, $cid)
	{
		$sql		= "INSERT INTO terms SET carrier_id = ".$cid.", language_id = ".$languageId;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
    public function saveLanguage(Language $language)
    {
        $data = array(
			'language_id'		=> $language->language_id,
            'language_title'	=> $language->language_title,
			'language_code'		=> $language->language_code,
			'language_flag'		=> $language->language_flag,
			'carrier_id'		=> $language->carrier_id,
			'language_status'	=> $language->language_status,
			//'language_default'	=> $language->language_default,
        );
		
        $language_id = (int)$language->language_id;
        if ($language_id == 0) {
			$this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
			if ($this->getLanguage($language_id)) {
                $this->update($data, array('language_id' => $language_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
	
	public function unsetDefaultLanguage($carrier)
    {
        $this->update(array('language_default' => '0'), array('carrier_id' => $carrier));
    }
	
    public function deleteLanguage($id)
    {
        $this->delete(array('language_id' => $id));
    }
	
	public function deleteLanguagePages($id)
	{
		$sql		= 'DELETE FROM page_values WHERE language_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
	public function deleteLanguageTerms($id)
	{
		$sql		= 'DELETE FROM terms WHERE language_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
	/*	public function deleteLanguageCategory($id)
	{
		$sql		= 'DELETE FROM faq_category_values WHERE language_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}	*/
	
	public function deleteLanguageFAQ($id)
	{
		$sql		= 'DELETE FROM faq_values WHERE language_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
	public function getLanguageImage($id)
	{
		$sql		= 'SELECT language_flag FROM languages WHERE language_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		return $result;
	}
	
	public function deleteCategory($id)
	{
		$sql		= 'DELETE FROM faq_category WHERE carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
	public function getActiveLanguages($carrierId)
	{
		$sql		= 'SELECT language_id, language_title, language_code FROM languages WHERE carrier_id = '.$carrierId.' and language_status = 1';
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		//$result		= $this->resultSetPrototype->initialize($result);
		return $result;
	}
	
}