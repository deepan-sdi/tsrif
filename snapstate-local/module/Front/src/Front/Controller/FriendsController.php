<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Front\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

//	Session
use Zend\Session\Container;

//	Auth
use Zend\Authentication,
	Zend\Authentication\Result,
	Zend\Authentication\AuthenticationService;

//	Cache
use Zend\Cache\Storage\StorageInterface;

//	SMTP Mailer
use PHPMailer;

class FriendsController extends AbstractActionController
{
	protected $usersTable;
	
	protected $cache;
	
	public function setCache(StorageInterface $cache)
    {
        $this->cache = $cache;
    }
	/************************************
	 *	Method: connect     	         
	 *  Purpose: To connect with MongoDB 
	 ***********************************/
	
	public function connect()
	{
		//$conn = new \Mongo(HOST, array("username" => USERNAME, "password" => PASSWORD, "db" => DATABASE));
		$conn = new \Mongo(HOST);
		return $conn;
	}
	/*************************************
	 *	Method: listVideos	     	      
	 *  Purpose: To select the videos	  
	 ************************************/
	
	public function listFriends($page, $limit)
	{
		//	Session for listing
		$listingSession	= new Container('fo_listing');
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media;
		$skip		= ($page - 1) * $limit;
		$sort		= array('date_approved' => 0);
		if(isset($listingSession->keyword) && trim($listingSession->keyword) != '') {
			$keywordArray	= explode(' ', $listingSession->keyword);
			$keywords		= array();
			if(is_array($keywordArray) && count($keywordArray) > 0) {
				foreach($keywordArray as $key => $value) {
					$value	= strtolower($value);
					if(trim($value) != '') {
						$keywords[]	= new \MongoRegex("/".trim($value)."/");
					}
				}
			}
			$document	= array('media_status' => '1', 'media_title' => array('$all' => $keywords));
		} else {
			$document	= array('media_status' => '1');
		}
		
		$cursor		= $collection->find($document)->skip($skip)->limit($limit)->sort($sort);
		return $cursor;
	}
	/*******************************************************
	 *	Method: checkFriend                           		
	 *  Purpose: To check whether they are already friends  
	 ******************************************************/
	
	public function checkFriend($invitedFriendId) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->friend_list;
		$userSession= new Container('fo_user');
		$document	= array('$or' => array(
											array('user_id' => (string)$userSession->userSession['_id'], 'friend_id' => (string)$invitedFriendId), 
											array('user_id' => (string)$invitedFriendId, 'friend_id' => (string)$userSession->userSession['_id'])
										));
		$results	= $collection->find($document);
		
		while($results->hasNext())
		{
			$resultArray	= $results->getNext();
		}
		if(isset($resultArray) && is_array($resultArray)) {
			return 1;
		} else {
			return 0;
		}
	}
	/*******************************************************
	 *	Method: getUserIdFromFBUID                       	
	 *  Purpose: To get user's ID from FBUID				
	 ******************************************************/
	
	public function getUserIdFromFBUID($fbuid) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$userSession= new Container('fo_user');
		$document	= array('user_fbuid' => $fbuid);
		$results	= $collection->find($document);
		$userId		= 0;
		while($results->hasNext())
		{
			$resultArray	= $results->getNext();
			$userId			= $resultArray['_id'];
		}
		return $userId;
	}
	/*************************************************
	 *	Method: insertFriend                       	  
	 *  Purpose: To insert friend list				  
	 ************************************************/
	
	public function insertFriend($friendId) {
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$collection	= $conn->snapstate->friend_list;
		$query		= array('user_id'	=> (string)$userSession->userSession['_id'],
							'friend_id'	=> (string)$friendId
							);
		$results	= $collection->insert($query);
	}
	/***************************************
	 *	Method: getFriends              	
	 *  Purpose: To fetch the friends		
	 **************************************/
	
	public function getFriends($page = 0, $limit = 0) {
		$conn			= $this->connect();
		$userSession	= new Container('fo_user');
		$skip			= ($page - 1) * $limit;
		$document		= array('$or' => array(
							array('user_id' => (string)$userSession->userSession['_id']),
							array('friend_id' => (string)$userSession->userSession['_id'])
						));
		$collection		= $conn->snapstate->friend_list;
		$results		= $collection->find($document)->skip($skip)->limit($limit);
		return $results;
	}
	/****************************************
	 *	Method: getFriendsDetails            
	 *  Purpose: To fetch the friends details
	 ***************************************/
	
	public function getFriendsDetails($array) {
		$conn			= $this->connect();
		$friendsArray	= array();
		foreach($array as $key => $value) {
			$friendsArray[]	= new \MongoID($value);
		}
		$document		= array('_id' => array('$in' => $friendsArray), 'user_status' => '1');
		$collection		= $conn->snapstate->users;
		$results		= $collection->find($document);
		$resultArray	= array();
		while($results->hasNext())
		{
			$resultArray[]	= $results->getNext();
		}
		return $resultArray;
	}
	
	
	
	/********************************************************************************************
	 *	Action: friends                                                                           
	 *	Page: It acts as a default page.                                                         
	 *******************************************************************************************/
	
	public function friendsAction()
	{
		//	Validate Authentication
		$userSession = new Container('fo_user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('front', array('controller' => 'index', 'action' => 'index'));
		}
		$this->layout('frontend');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		//	Destroy listing Session Vars
		$listingSession = new Container('fo_listing');
		$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}
		
		if ($request->isPost()) {
			$formData	= $request->getPost();
			if(isset($formData['search']) && $formData['search'] != '')
				$listingSession->keyword	= $formData['search'];
			else
				$listingSession->keyword	= '';
			
		}
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
			'action'		=> $this->params('action'),
			'controller'	=> $this->params('controller'),
		));
    }
	/**************************************
	 *	Action: invite-eia-email           
	 *	Page: Sends invitation mails	   
	 *************************************/
	
	public function inviteViaEmailAction()
    {
		$request 		= $this->getRequest();
		$formData		= $request->getPost();
		$userSession	= new Container('fo_user');
		
		if(isset($formData['emails']) && isset($userSession->userSession['_id']) && trim($userSession->userSession['_id']) != '') {
			$emailArray	= json_decode($formData['emails']);
			foreach($emailArray as $pkey => $pvalue) {
				//	Invitation Mail has to be sent
				$senderName		= ucwords($userSession->userSession['user_firstname'].' '.$userSession->userSession['user_lastname']);
				$emailaddress	= 'deepan@sdi.la';
				$link		= DOMAINPATH.'/?referral='.base64_encode($userSession->userSession['_id']).'&email='.$pvalue;
				$subject	= 'Snapstate - Invitation Mail';
				$message	= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
								<html xmlns="http://www.w3.org/1999/xhtml">
								<head>
								<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
								<title>Invitation</title>
								</head>
								
								<body>
								<table width="650" border="0" cellspacing="0" cellpadding="0" style="margin:40px auto; background:#fff;">
								
								  <tr>
								    <td><table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #DEDEDE;">
								      <tr>
								        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
								          <tr>
								            <td style="padding:10px;"><img src="'.DOMAINPATH.'/Front/img/mail/logo.png" width="136" height="36" /></td>
								            <td align="right" style="padding-right:10px;" class="txt1"><a href="#">'.ADMIN_EMAIL.'</a></td>
								          </tr>
								          <tr>
								            <td colspan="2" style="background:#DEDEDE;font-size:12px; height:25px;"><table width="100%" border="0" cellspacing="0" cellpadding="0">
								  <tr>
								    <td  style="padding-left:10px; color:#535353">'.date('F, Y').'</td>  
								  </tr>
								</table>
								</td>
								            </tr>
								        </table></td>
								      </tr>
								      <tr>
								        <td style="padding:10px;"><img src="'.DOMAINPATH.'/Front/img/mail/banner.png" width="634" height="215" /></td>
								      </tr>
								      <tr>
								        <td></td>
								      </tr>
								      <tr>
								        <td><table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding:0px 20px;">
								  <tr>
								    <td style="text-align: justify; line-height:18px;color:#1868AE;">Hello, </td>
								 </tr>
								   <tr>
								    <td style="color: #147EC2;font-size: 14px;font-weight: normal;padding: 10px 0;">You have just received an invitation from '.$senderName.' to join the Snapstate.com website.</td>
								  </tr>
								 <tr>
								    <td style="color: #147EC2;text-align: justify; line-height:18px;padding-bottom:10px">Click the link below to accept this invitation and create an account:</td>
								 </tr>
								  <tr>
								    <td style="text-align: justify; line-height:18px; padding-bottom:10px"><a href="'.$link.'" title="Click here to accept the invitation & create your account">'.$link.'</a></td>
								 </tr>
								 <tr>
								    <td style="text-align: justify; line-height:18px; padding-bottom:10px;padding-top:10px;">Thanks,</td>
								 </tr>
								 <tr>
								    <td style="text-align: justify; line-height:18px; padding-bottom:10px;">The Snapstate Team.</td>
								 </tr>
								  
								</table>
								</td>
								      </tr>
								    </table></td>
								  </tr>
								  <tr>
								    <td class="txt2" style="padding:10px 0;border:1px solid #DEDEDE; text-align:center;font-size: 11px; background:url('.DOMAINPATH.'/Front/img/mail/footer-bg.png) no-repeat; color:#fff;">© Copyright '.date('Y').' SnapState.com. All rights reserved. </td>
								  </tr>
								</table>
								
								</body>
								</html>';
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
				$headers .= 'From: Snapstate.com <admin@snapstate.com>' . "\r\n";
				//$to		= 'To: ' . $emailaddress . "\r\n";
				$to		= 'To: ' . $pvalue . "\r\n";
				$headersMessage	= $headers . $to;
				
				if(MAILER) {
					//mail('', $subject, $message, $headersMessage);	
					//	SMTP Settings
						$mail = new PHPMailer();
						$mail->IsSMTP();
						$mail->SMTPDebug	= 1;
						$mail->SMTPAuth		= true;
						$mail->SMTPSecure	= 'ssl';
						$mail->Host			= "smtp.gmail.com";
						$mail->Port			= 465;
						$mail->IsHTML(true);
						$mail->Username		= SMTP_USERNAME_DEMO;
						$mail->Password		= SMTP_PASSWORD_DEMO;
						
						$mail->SetFrom("admin@snapstate.com");
						$mail->Subject	= $subject;
						$mail->Body		= $message;
						$mail->AddAddress($pvalue);
						$mail->Send();
				}
			}
			echo '1';
		} else {
			echo '-1';
		}
		return $this->getResponse();
	}
	/**************************************
	 *	Action: invite-eia-email           
	 *	Page: Sends invitation mails	   
	 *************************************/
	
	public function updateFbfriendsAction()
    {
		$request 		= $this->getRequest();
		$formData		= $request->getPost();
		$userSession	= new Container('fo_user');
		
		//	Destroy Temporary Session Vars
		$tempSession	= new Container('fo_temp_session');
		$sessionArray	= array();
		foreach($tempSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$tempSession->offsetUnset($value);
		}
		//	End
		
		if(isset($formData['fbusers']) && isset($userSession->userSession['_id']) && trim($userSession->userSession['_id']) != '') {
			$friendsArray	= json_decode($formData['fbusers']);
			foreach($friendsArray as $key => $value) {
				$userId	= $this->getUserIdFromFBUID($value);
				if($userId != '0') {
					$friendsFlag	= $this->checkFriend($userId);
					if(!$friendsFlag) {
						$this->insertFriend($userId);
					}
				}
			}
			echo '1';
		} else {
			echo '-1';
		}
		return $this->getResponse();
	}
	/********************************
	 *	Action: list-friends         
	 *  Module: To list the friend	 
	 *	Note:	AJAX call with view  
	 *******************************/
	
	public function listFriendsAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		$userSession	= new Container('fo_user');
		
		$matches	= $this->getEvent()->getRouteMatch();
		$page		= $matches->getParam('id', 1);
		$perPage	= $matches->getParam('perPage', '3');
		
		//	Session for listing
		$listingSession = new Container('fo_listing');
		if($page == '0') {
			$listingSession->page	= 1;
			$page	= 1;
		} else if($listingSession->offsetExists('page')) {
			$page	= $listingSession->page+1;
			$listingSession->page	= $page;
		} else {
			$page	= 1;
		}
		$message		= '';
		$perPage		= PERPAGE;
		$recordsArray	= $this->getFriends($page, $perPage);
		$totalRecords	= $recordsArray->count();
		$resultArray	= array();
		
		while($recordsArray->hasNext())
		{
			$tempArray	= $recordsArray->getNext();
			if($tempArray['user_id'] != (string)$userSession->userSession['_id'])
				$resultArray[$tempArray['user_id']]		= $tempArray['user_id'];
			if($tempArray['friend_id'] != (string)$userSession->userSession['_id'])
				$resultArray[$tempArray['friend_id']]	= $tempArray['friend_id'];
		}
		$friendsArray	= $this->getFriendsDetails($resultArray);
		$result->setVariables(array('records'		=> $friendsArray,
									'message'		=> $message,
									'page'			=> $page,
									'perPage'		=> $perPage,
									'totalRecords'	=> $totalRecords,
									'action'		=> $this->params('action'),
									'controller'	=> $this->params('controller')));
		return $result;
    }
}
