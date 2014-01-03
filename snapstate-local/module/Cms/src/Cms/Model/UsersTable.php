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

class UsersTable extends AbstractTableGateway
{
    protected $table = 'users';
	
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Users());
        $this->initialize();
    }
	
	public function fetchAll()
    {
		//	Session for Carrier accessibility
		$userSession = new Container('user');
		if($userSession->offsetExists('carrier')) {
			$carrierFlag	= $userSession->carrier;
		}
        $rowset = $this->select(array('user_carrier_id' => $carrierFlag));
		$rowset->buffer();
		$rowset->next();
        return $rowset;
    }
	
    public function getUser($id)
    {
        $id  = (int) $id;
        $rowset = $this->select(array('user_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
	
	public function getUsersList()
	{
		//	Session for Carrier accessibility
		$whereClause	= '';
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();
		$whereClause	.= ' user_id != ' . $user->user_id;
		
		$userSession = new Container('user');
		if($userSession->offsetExists('carrier')) {
			$carrierFlag	= $userSession->carrier;
			$whereClause	.= ' AND user_carrier_id = ' . $carrierFlag;
		}
		$listingSession = new Container('listing');
		if($listingSession->offsetExists('status') && $listingSession->status != '') {
			$whereClause	.= ' AND user_status = ' . $listingSession->status;
		}
		if($listingSession->offsetExists('field') && $listingSession->field != '' && $listingSession->offsetExists('keyword') && $listingSession->keyword != '') {
			$whereClause	.= ' AND ' . $listingSession->field . ' like "' . addslashes($listingSession->keyword) . '%"';
		}
		if($listingSession->offsetExists('usermode') && $listingSession->usermode != '' && $listingSession->offsetExists('usermode') && $listingSession->usermode != '') {
			$whereClause	.= ' AND user_role_id = ' . $listingSession->usermode;
		}
		
		$orderClause	= '';
		if($listingSession->offsetExists('sortBy')) {
			$orderClause	.= ' ORDER BY '.$listingSession->sortBy;
		}
		if($listingSession->offsetExists('sortType') && $listingSession->sortType == 1) {
			$orderClause	.= ' DESC';
		}
		
		$sql	= 'SELECT * FROM users
					WHERE ' . $whereClause . ' ' . $orderClause;
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		$result		= $this->resultSetPrototype->initialize($result);
		$result->buffer();
		$result->next();
		return $result;
	}
	
	public function updateUser($values, $condition) {
		$sql	= 'UPDATE users set ' . $values . ' WHERE '. $condition;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
	public function getUserByEmail($email)
    {
		$rowset = $this->select(array('user_email' => $email));
		$row = $rowset->current();
		if (!$row) {
			return false;
		} else {
        	return $row;
		}
    }
	
	public function updatePasswordByEmail($email, $password) {
		$sql	= 'UPDATE users set user_password = "'.addslashes(md5($password)).'" WHERE user_email = "'.$email.'"';
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
    public function saveUser(Users $user)
    {
        $data = array(
			'user_role_id'		=> $user->user_role_id,
            'user_email'		=> $user->user_email,
			'user_firstname'	=> $user->user_firstname,
            'user_lastname'		=> $user->user_lastname,
			'user_password' 	=> md5($user->user_password),
			'user_status'		=> $user->user_status,
			'user_updateddate'	=> $user->user_updateddate,
			'user_carrier_id'	=> $user->user_carrier_id,
			'user_createdby'	=> $user->user_createdby,
			'user_access'		=> $user->user_access,
        );
		
        $user_id = (int)$user->user_id;
        if ($user_id == 0) {
			$data['user_createddate']	= $user->user_createddate;
			$this->insert($data);
        } else {
			if ($this->getUser($user_id)) {
				if(trim($user->user_password) == '') {
					unset($data['user_password']);
				}
                $this->update($data, array('user_id' => $user_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
	
    public function deleteUser($id)
    {
        $this->delete(array('user_id' => $id));
    }
}