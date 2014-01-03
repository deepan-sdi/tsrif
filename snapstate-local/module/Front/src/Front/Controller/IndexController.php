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

class IndexController extends AbstractActionController
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
	/***********************************
	 *	Method: registerUser   	    	
	 *  Purpose: To register an user	
	 **********************************/
	
	public function registerUser($formData) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$userId		= new \MongoId();
		$query		= array('_id'			=> $userId,
							'user_firstname'=> $formData['first_name'],
							'user_lastname' => $formData['last_name'],
							'user_email' 	=> $formData['email_address'],
							'user_group' 	=> USER_GROUP_ID,
							'user_fbuid' 	=> '',
							'user_password' => md5($formData['registration_password']),
							'user_dob' 		=> '',
							'user_gender'	=> '',
							'user_status'	=> '0',
							'date_created'	=> date('m/d/Y H:i:s'),
							'date_modified'	=> date('m/d/Y H:i:s')
							);
		$results	= $collection->insert($query);
		if($results) {
			return $userId;
		} else {
			return 0;
		}
	}
	/*************************************************
	 *	Method: checkEmail                            
	 *  Purpose: To validate the email existence      
	 ************************************************/
	
	public function checkEmail($formData, $opt) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$userSession = new Container('fo_user');
		if($opt == 1) {
			$results	= $collection->find(array('user_email' => new \MongoRegex('/^' . preg_quote(trim($formData['email_address'])) . '$/i')));
		} else if($opt == 2) {
			$mongoID	= new \MongoID(trim($formData['_id']));
			$document	= array('_id'	=> array('$ne' => $mongoID), 'user_email' => new \MongoRegex('/^' . preg_quote(trim($formData['email_address'])) . '$/i'));
			$results	= $collection->find($document);
		}
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
	/*************************************************
	 *	Method: checkSubscriptionEmail                
	 *  Purpose: To validate the email existence      
	 ************************************************/
	
	public function checkSubscriptionEmail($formData, $opt) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->newsletter;
		$userSession = new Container('fo_user');
		if($opt == 1) {
			$results	= $collection->find(array('email_address' => new \MongoRegex('/^' . preg_quote(trim($formData['email_address'])) . '$/i')));
		}
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
	/************************************
	 *	Method: selectUser     	         
	 *  Purpose: To select user with auth
	 ***********************************/
	
	public function selectUser($username, $password)
	{
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$document	= array('$or' => array(
							array('user_email' => new \MongoRegex('/^' . preg_quote(trim($username)) . '$/i'), 'user_password' => md5($password), 'user_group' => USER_GROUP_ID, 'user_status' => '1'),
							array('user_email' => new \MongoRegex('/^' . preg_quote(trim($username)) . '$/i'), 'user_password' => md5($password), 'user_group' => CONTRIBUTOR_GROUP_ID, 'user_status' => '1')
						));
		$cursor		= $collection->find($document);
		return $cursor;
	}
	/*************************************
	 *	Method: selectUserByEmail     	  
	 *  Purpose: To validate email address
	 ************************************/
	
	public function selectUserByEmail($email)
	{
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$document	= array('user_email' => new \MongoRegex('/^' . preg_quote(trim($email)) . '$/i'), 'user_group' => USER_GROUP_ID);
		$cursor		= $collection->find($document);
		return $cursor;
	}
	/***********************************
	 *	Method: getExtendedFriends    	
	 *  Purpose: To get Extended friends
	 **********************************/
	
	public function getExtendedFriends($friends, $media) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media_views;
		$document	= array('media_id' => array('$in' => $media), 'user_id' => array('$in' => $friends));
		$cursor		= $collection->find($document);
		$resultArray= array();
		while($cursor->hasNext())
		{
			$tempResult			= $cursor->getNext();
			$friendsArray[]		= new \MongoID($tempResult['user_id']);
			$resultArray['media'][$tempResult['media_id']][$tempResult['user_id']]	= $tempResult['user_id'];
		}
		
		if(is_array($resultArray) && count($resultArray) > 0) {
			$collection	= $conn->snapstate->users;
			$document	= array('_id' => array('$in' => $friendsArray));
			$cursor		= $collection->find($document);
			while($cursor->hasNext())
			{
				$tempRec	= $cursor->getNext();
				$resultArray['friends'][(string)$tempRec['_id']]	= $tempRec;
			}
		}
		return $resultArray;
	}
	/***********************************
	 *	Method: registerFbUser	    	
	 *  Purpose: To register a FB user	
	 **********************************/
	
	public function registerFBUser($formData) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$userId		= new \MongoId();
		$query		= array('_id'			=> $userId,
							'user_firstname'=> $formData['user_firstname'],
							'user_lastname' => $formData['user_lastname'],
							'user_email' 	=> $formData['user_email'],
							'user_group' 	=> USER_GROUP_ID,
							'user_fbuid' 	=> $formData['user_fbuid'],
							'user_password' => '',
							'user_dob' 		=> '',
							'user_gender'	=> '',
							'user_status'	=> '1',
							'date_created'	=> $formData['created_date'],
							'date_modified'	=> $formData['created_date']
							);
		$results	= $collection->insert($query);
		if($results) {
			return $userId;
		} else {
			return 0;
		}
	}
	/*******************************************
	 *	Method: selectFBUser     	      		
	 *  Purpose: To select the user with FBUID	
	 ******************************************/
	
	public function selectFBUser($fbuid, $email)
	{
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$document	= array('user_fbuid' => $fbuid, 'user_email' => new \MongoRegex('/^' . preg_quote(trim($email)) . '$/i'), 'user_group' => USER_GROUP_ID, 'user_status' => '1');
		$cursor		= $collection->find($document);
		return $cursor;
	}
	/*************************************
	 *	Method: updateFBDetails     	  
	 *  Purpose: To update FBUID details  
	 ************************************/
	
	public function updateFBDetails($data)
	{
		$conn		= $this->connect();
		$document	= array('$set' => array('user_fbuid' => $data['user_fbuid'], 'user_status' => '1'));
		$query		= array('user_email' => new \MongoRegex('/^' . preg_quote(trim($data['user_email'])) . '$/i'));
		$collection	= $conn->snapstate->users;
		$collection->update($query, $document);
	}
	/*************************************
	 *	Method: updateStatus	     	  
	 *  Purpose: To update user status	  
	 ************************************/
	
	public function updateStatus($data)
	{
		$conn		= $this->connect();
		$document	= array('$set' => array('user_status' => '1'));
		$query		= array('_id' => new \MongoID($data));
		$collection	= $conn->snapstate->users;
		$result		= $collection->update($query, $document);
		//return $result;
		return $conn->lastError();
	}
	/*************************************
	 *	Method: listVideos	     	      
	 *  Purpose: To select the videos	  
	 ************************************/
	
	public function listVideos($page, $limit)
	{
		$userSession= new Container('fo_user');
		//	Session for listing
		$listingSession	= new Container('fo_listing');
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media;
		$skip		= ($page - 1) * $limit;
		$sort		= array('_id' => -1);
		
		//	Search Criteria
		$document	= array();
		$document['media_approved']	= '1';
		$document['media_status']	= '1';
		
		//	Keyword
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
			$document['media_title_lower'] = array('$all' => $keywords);
		}
		//	Category
		if(isset($listingSession->category) && trim($listingSession->category) != '') {
			$document['media_category'] = $listingSession->category;
		}
		//	Raning
		if(isset($listingSession->ranking) && trim($listingSession->ranking) == 1) {	//	Raning - Like
			$likedVideos	= $this->getTopRatedVideos('like');
			$tempArray		= array();
			if(is_array($likedVideos) && count($likedVideos) > 0) {
				foreach($likedVideos as $pkey => $pvalue) {
					$tempArray[]	= new \MongoID($pkey);
				}
				//$document['_id'] = array('$in' => $tempArray);
			}
		} else if(isset($listingSession->ranking) && trim($listingSession->ranking) == 2) {	//	Raning - Like
			$dislikedVideos	= $this->getTopRatedVideos('dislike');
			$tempArray		= array();
			if(is_array($dislikedVideos) && count($dislikedVideos) > 0) {
				foreach($dislikedVideos as $pkey => $pvalue) {
					$tempArray[]	= new \MongoID($pkey);
				}
				//$document['_id'] = array('$in' => $tempArray);
			}
		}
		//	Length
		if(isset($listingSession->length) && trim($listingSession->length) != '') {
			switch($listingSession->length) {
				case '1':
				case '2':
				case '3':
				case '4':
					$document['media_length'] = array('$lt' => '5');
					break;
				case 5:
					$document['media_length'] = array('$gt' => $listingSession->length);
					break;
			}
		}
		//	Friend
		$newtempArray		= array();
		if(isset($listingSession->friend) && trim($listingSession->friend) == '1') {
			$friendsMediaArray	= $this->getFriendsVideosList();
			if(is_array($friendsMediaArray) && count($friendsMediaArray) > 0) {
				foreach($friendsMediaArray as $pkey => $pvalue) {
					$tempArray[]	= new \MongoID($pkey);
				}
			}
		} else if(isset($listingSession->friend) && trim($listingSession->friend) == '2') {
			$friendsMediaArray	= $this->getFriendsVideosList();
			if(is_array($friendsMediaArray) && count($friendsMediaArray) > 0) {
				foreach($friendsMediaArray as $pkey => $pvalue) {
					$newtempArray[]	= new \MongoID($pkey);
				}
			}
		}
		
		//	Seen
		if(isset($listingSession->seen) && trim($listingSession->seen) != '0' && trim($listingSession->seen) != '') {
			$innerFlag	= 0;
			switch($listingSession->seen) {
				case '1':
					$videosArray	= $userSession->mediaSession['videos'];
					if(is_array($videosArray) && count($videosArray) > 0) {
						$viewedVideos	= array_unique($videosArray);
						foreach($viewedVideos as $key => $value) {
							$newtempArray[]	= new \MongoID($value);
							$innerFlag++;
						}
					}
					break;
				case '2':
					$videosArray	= $userSession->mediaSession['videos'];
					if(is_array($videosArray) && count($videosArray) > 0) {
						foreach($videosArray as $key => $value) {
							if($key > strtotime("-1 week")) {
								$tempArray[]	= new \MongoID($value);
								$innerFlag++;
							}
						}
					}
					break;
				case '3':
					$videosArray	= $userSession->mediaSession['videos'];
					if(is_array($videosArray) && count($videosArray) > 0) {
						foreach($videosArray as $key => $value) {
							if($key > strtotime("-30 days")) {
								$tempArray[]	= new \MongoID($value);
								$innerFlag++;
							}
						}
					}
					break;
				case '4':
					$videosArray	= $userSession->mediaSession['videos'];
					if(is_array($videosArray) && count($videosArray) > 0) {
						foreach($videosArray as $key => $value) {
							if($key > strtotime("-90 days")) {
								$tempArray[]	= new \MongoID($value);
								$innerFlag++;
							}
						}
					}
					break;
				case '5':
					$videosArray	= $userSession->mediaSession['videos'];
					if(is_array($videosArray) && count($videosArray) > 0) {
						foreach($videosArray as $key => $value) {
							if($key < strtotime("-90 days")) {
								$tempArray[]	= new \MongoID($value);
								$innerFlag++;
							}
						}
					}
					break;
			}
			if($innerFlag == 0) {
				$tempArray		= array();
				$tempArray[]	= 0;
			}
		}
		
		//	Common to both Friends & Ratings
		if(isset($tempArray) && is_array($tempArray) && count($tempArray) > 0)
			$document['_id'] = array('$in' => $tempArray);
		//	Common to both Friends & Seen
		if(isset($newtempArray) && is_array($newtempArray) && count($newtempArray) > 0)
			$document['_id'] = array('$nin' => $newtempArray);
		
		//	Common
		$cursor		= $collection->find($document)->skip($skip)->limit($limit)->sort($sort);
		return $cursor;
	}
	/************************************
	 *	Method: selectGroup  	         
	 *  Purpose: To select user group	 
	 ***********************************/
	
	public function selectGroup($groupId)
	{
		$conn		= $this->connect();
		$collection	= $conn->snapstate->groups;
		$document	= array('_id' => new \MongoID($groupId));
		$cursor		= $collection->find($document);
		return $cursor;
	}
	/*************************************
	 *	Method: updateUserprofile     	  
	 *  Purpose: To update user profile	  
	 ************************************/
	
	public function updateUserprofile($data)
	{
		$userSession= new Container('fo_user');
		$conn		= $this->connect();
		$updateArray= array('user_firstname'=> $data['user_fname'], 
							'user_lastname'	=> $data['user_lname'], 
							'user_email'	=> $data['user_email'], 
							'user_gender'	=> $data['user_gender'],
							'date_modified'	=> $data['date_modified']);
		if(isset($data['user_dob']) && $this->isValidDate(str_replace('/', '-', $data['user_dob']))) {
			$updateArray['user_dob'] = $data['user_dob'];
		}
		if(isset($data['user_photo_name']) && trim($data['user_photo_name']) != '') {
			$updateArray['user_photo']	= $data['user_photo_name'];
		}
		$document	= array('$set' => $updateArray);
		$query		= array('_id' => new \MongoID($userSession->userSession['_id']));
		$collection	= $conn->snapstate->users;
		$result		= $collection->update($query, $document);
		return $result;
	}
	/*************************************
	 *	Method: updateStatus	     	  
	 *  Purpose: To update user status	  
	 ************************************/
	
	public function updatePassword($data)
	{
		$conn		= $this->connect();
		$document	= array('$set' => array('user_password' => $data['new_password']));
		$query		= array('_id' => new \MongoID($data['_id']));
		$collection	= $conn->snapstate->users;
		$result		= $collection->update($query, $document);
		return $result;
		//return $conn->lastError();
	}
	/*************************************
	 *	Method: getRatedVideos	     	  
	 *  Purpose: To find the rated videos 
	 ************************************/
	
	public function getRatedVideos()
	{
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$collection	= $conn->snapstate->media_ratings;
		$document	= array('user_id' => (string)$userSession->userSession['_id']);
		$cursor		= $collection->find($document);
		$resultArray= array();
		while($cursor->hasNext())
		{
			$tempArray							= $cursor->getNext();
			$resultArray[$tempArray['media_id']]= $tempArray['rating'];
		}
		return $resultArray;
	}
	/*************************************************
	 *	Method: insertFriend                       	  
	 *  Purpose: To insert friend list				  
	 ************************************************/
	
	public function insertFriend($friendId, $userId) {
		$conn		= $this->connect();
		//	Check the friendID existance
		$collection	= $conn->snapstate->users;
		$document	= array('_id' => new \MongoID($friendId), 'user_status' => '1');
		$cursor		= $collection->find($document);
		$flag		= 0;
		while($cursor->hasNext())
		{
			$tempArray	= $cursor->getNext();
			$flag		= 1;
		}
		return $flag;
	}
	/****************************************************
	 *	Method:	selectCategories						 
	 *	Purpose: To list the active categorties in filter
	 ***************************************************/
	
	public function selectCategories() {
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$collection	= $conn->snapstate->categories;
		$document	= array('category_status' => '1');
		$cursor		= $collection->find();
		$resultArray= array();
		while($cursor->hasNext())
		{
			$tempArray						= $cursor->getNext();
			$resultArray[(string)$tempArray['_id']]	= $tempArray['category_name'];
		}
		return $resultArray;
	}
	/*************************************
	 *	Method: getTopRatedVideos	      
	 *  Purpose: To list rated videos	  
	 ************************************/
	
	public function getTopRatedVideos($option)
	{
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media_ratings;
		$document	= array('rating' => $option);
		$cursor		= $collection->find($document);
		$resultArray= array();
		while($cursor->hasNext())
		{
			$tempArray							= $cursor->getNext();
			$resultArray[$tempArray['media_id']]= $tempArray['rating'];
		}
		return $resultArray;
	}
	/*************************************
	 *	Method: getFriendsList	     	  
	 *  Purpose: Fetch the friends 		  
	 ************************************/
	
	public function getFriendsList()
	{
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$collection	= $conn->snapstate->friend_list;
		$document	= array('$or' => array(
								array('user_id' => (string)$userSession->userSession['_id']), 
								array('friend_id' => (string)$userSession->userSession['_id'])
							));
		$results	= $collection->find($document);
		$resultArray= array();
		while($results->hasNext())
		{
			$tempArray		= $results->getNext();
			if((string)$userSession->userSession['_id'] != $tempArray['user_id'])
				$resultArray[$tempArray['user_id']]		= $tempArray['user_id'];
			if((string)$userSession->userSession['_id'] != $tempArray['friend_id'])
				$resultArray[$tempArray['friend_id']]	= $tempArray['friend_id'];
		}
		return $resultArray;
	}
	/*******************************************
	 *	Method: getFriendsVideosList   	  		
	 *  Purpose: Fetch the friends viewed videos
	 ******************************************/
	
	public function getFriendsVideosList()
	{
		$conn			= $this->connect();
		$userSession	= new Container('fo_user');
		$collection		= $conn->snapstate->media_views;
		$friendsArray	= array();
		if(isset($userSession->mediaSession['friends']) && count($userSession->mediaSession['friends']) > 0) {
			$friendsArray	= $userSession->mediaSession['friends'];
		}
		$document	= array('user_id' => array('$in' => array_values($friendsArray)));
		$results	= $collection->find($document);
		$resultArray= array();
		while($results->hasNext())
		{
			$tempArray		= $results->getNext();
			$resultArray[$tempArray['media_id']]	= $tempArray['media_id'];
		}
		return $resultArray;
	}
	/***************************************
	 *	Method: getVideosList	     	  	
	 *  Purpose: To fetch the viewed videos	
	 **************************************/
	
	public function getVideosList()
	{
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$collection	= $conn->snapstate->media_views;
		$document	= array('user_id' => (string)$userSession->userSession['_id']);
		$cursor		= $collection->find($document);
		$resultArray= array();
		while($cursor->hasNext())
		{
			$tempArray											= $cursor->getNext();
			$resultArray[strtotime($tempArray['date_viewed'])]	= $tempArray['media_id'];
		}
		return $resultArray;
	}
	/****************************************
	 *	Method: getPlaylist		     	  	 
	 *  Purpose: To fetch the playlist videos
	 ***************************************/
	
	public function getPlaylist()
	{
		$conn			= $this->connect();
		$userSession	= new Container('fo_user');
		$collection		= $conn->snapstate->playlists;
		$document		= array('user_id' => (string)$userSession->userSession['_id']);
		$cursor			= $collection->find($document);
		$resultArray	= array();
		while($cursor->hasNext()) {
			$tempArray								= $cursor->getNext();
			$resultArray[(string)$tempArray['_id']]	= $tempArray['name'];
		}
		return $resultArray;
	}
	/**********************************
	 *	Method: listMediaTags		   
	 *  Module: To List Media Tags	   
	 *********************************/
	
	public function listMediaTags() {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->tags;
		$query		= array('tag_status' => '1');
		$sort		= array('tag_name' => 1);
		$cursor		= $collection->find($query)->sort($sort);
		$resultArray= array();
		
		while($cursor->hasNext())
		{
			$resultArray[]	= $cursor->getNext();
		}
		return $resultArray;
	}
	/***********************************
	 *	Method: postFeedback	    	
	 *  Purpose: To post feedbacks		
	 **********************************/
	
	public function postFeedback($formData) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->feedbacks;
		$query		= array('email_address'	=> $formData['feedback_email'],
							'category'		=> $formData['feedback_category'],
							'comments'		=> $formData['feedback_message'],
							'date_posted'	=> date('m/d/Y H:i:s'));
		$results	= $collection->insert($query);
	}
	/***********************************
	 *	Method: registerNewsletter    	
	 *  Purpose: To subscribe newsletter
	 **********************************/
	
	public function registerNewsletter($formData) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->newsletter;
		$query		= array('email_address'		=> $formData['newsletter_email'],
							'subscription_date'	=> date('m/d/Y H:i:s'));
		$results	= $collection->insert($query);
	}
	/********************************************************************************************
	 *	Action: Index                                                                            
	 *	Page: It acts as a default page.                                                         
	 *******************************************************************************************/
	
	public function indexAction()
	{
		$this->layout('frontend');
		$result = new ViewModel();
		
		//	Clear Video Session - Destroy listing Session Vars
		$listingSession = new Container('fo_listing');
		$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}
		//	Clear Video Session - Destroy listing Session Vars
		$listingSession = new Container('fo_temp_session');
		$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}
		
		$tempSession	= new Container('fo_temp_session');
		//	Start: Category Cache Creation
		$sm	= $this->getServiceLocator();
		$this->globalcache = $sm->get('cache-adapter');
		if( ($resultSet = $this->globalcache->getItem('category-cache')) == FALSE) {
			$categoriesArray	= $this->selectCategories();
			$this->globalcache->setItem('category-cache',  $categoriesArray);
			$resultSet			= $categoriesArray;
		}
		$tempArray['categories'] = $resultSet;
		//$this->globalcache->removeItem('category-cache');
		//	End: Category Cache
		
		//	Start: Tags Cache Creation
		if( ($resultSet = $this->globalcache->getItem('tags-cache')) == FALSE) {
			$tagsArray	= $this->listMediaTags();
			$this->globalcache->setItem('tags-cache',  $tagsArray);
			$resultSet	= $tagsArray;
		}
		$tempArray['tags'] = $resultSet;
		$tempSession->tempSession	= $tempArray;
		//$this->globalcache->removeItem('category-cache');
		//	End: Tags Cache
		
		$userSession	= new Container('fo_user');
		
		if(isset($userSession->userSession['_id']) && $userSession->userSession['_id'] != '') {
			//	Friend Invitation Acceptance Process
			$tempfSession	= new Container('fo_ftemp_session');
			$getParams		= $this->params()->fromQuery('referral');
			if(trim($getParams) != '') {
				$emailRequests	= array('referral' => base64_decode($getParams));
				$tempfSession->tempSession	= $emailRequests;
			}
			
			if(isset($tempfSession->tempSession['referral']) && $tempfSession->tempSession['referral'] != '') {
				$flag	= $this->insertFriend($tempfSession->tempSession['referral'], $userSession->userSession['_id']);
				if($flag) {
					$conn		= $this->connect();
					$collection	= $conn->snapstate->friend_list;
					$query		= array('user_id'	=> (string)$userSession->userSession['_id'],
										'friend_id'	=> (string)$tempfSession->tempSession['referral']);
					$results	= $collection->insert($query);
				}
				//	Destroy Temporary Session Vars
				$tempfSession	= new Container('fo_ftemp_session');
				$sessionArray	= array();
				foreach($tempfSession->getIterator() as $key => $value) {
					$sessionArray[]	= $key;
				}
				foreach($sessionArray as $key => $value) {
					$tempfSession->offsetUnset($value);
				}
				//	End
			}
			//	End
		}
		
		$result->setVariables(array('action'	=> $this->params('action'),
									'controller'=> $this->params('controller')));
		return $result;
    }
	/**************************************
	 *	Action: validate-registration      
	 *	Page: To register a new user	   
	 *************************************/
	
	public function validateRegistrationAction()
    {
		$userSession= new Container('fo_user');
		$request	= $this->getRequest();
		
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['first_name']) && trim($formData['first_name']) != '' && isset($formData['last_name']) && trim($formData['last_name']) != '' && isset($formData['email_address']) && trim($formData['email_address']) != '' && isset($formData['registration_password']) && $formData['registration_password'] != '' && isset($formData['registration_confirm_password']) && $formData['registration_confirm_password'] != '' && $formData['registration_confirm_password'] == $formData['registration_password']) {
				$results	= $this->checkEmail($formData, 1);
				
				if($results == 1) {	// Email Address - already exist condition
					$result	= '-1';
				} else {
					$result	= $this->registerUser($formData);
					
					//	Friend Invitation Acceptance Process
					$tempSession	= new Container('fo_ftemp_session');
					if(isset($tempSession->tempSession['referral']) && $tempSession->tempSession['referral'] != '') {
						$flag	= $this->insertFriend($tempSession->tempSession['referral'], $result);
						if($flag) {	// check whether the reference id exist
							$conn		= $this->connect();
							$collection	= $conn->snapstate->friend_list;
							$query		= array('user_id'	=> (string)$result,
												'friend_id'	=> (string)$tempSession->tempSession['referral']);
							$results	= $collection->insert($query);
						}
						//	Destroy Temporary Session Vars
						$tempSession	= new Container('fo_ftemp_session');
						$sessionArray	= array();
						foreach($tempSession->getIterator() as $key => $value) {
							$sessionArray[]	= $key;
						}
						foreach($sessionArray as $key => $value) {
							$tempSession->offsetUnset($value);
						}
						//	End
					}
					//	End
					
					//	Registration Mail has to be sent
					$emailaddress	= 'deepan@sdi.la';
					$link		= ACTIVATION_URL.base64_encode($result);
					$subject	= 'Snapstate - Confirmation Mail';
					$message	= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
									<html xmlns="http://www.w3.org/1999/xhtml">
									<head>
									<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
									<title>Congratulations</title>
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
									    <td style="text-align: justify; line-height:18px;color:#1868AE;">Welcome '.ucwords($formData['first_name']).' '.ucwords($formData['last_name']).', </td>
									 </tr>
									 <tr>
									    <td style="text-align: center; line-height:28px; padding-bottom:10px; padding-top: 10px; font-size:20px; color:#1868AE"><span class="quotes">“</span> Congratulations! Thank you very much for registering with Snapstate.com. <span class="quotes">”</span></td>
									 </tr>
									   <tr>
									    <td style="color: #147EC2;font-size: 14px;font-weight: normal;padding: 10px 0;">Please click the link below to activate your account:</td>
									  </tr>
									   <tr>
									    <td style="text-align: justify; line-height:18px; padding-bottom:10px"><a href="'.$link.'" title="Click here to activate your account">'.$link.'</a></td>
									 </tr>
									 <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px;padding-bottom:10px">Please find the login information below:</td>
									 </tr>
									  <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px; padding-bottom:10px">Email Address: ' . $formData['email_address'] . '</td>
									 </tr>
									 <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px; padding-bottom:10px">Passsword: '.$formData['registration_password'].'</td>
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
					$to		= 'To: ' . $formData['email_address'] . "\r\n";
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
						$mail->AddAddress($formData['email_address']);
						$mail->Send();
					}
				}
				echo $result;
				
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/**************************************
	 *	Action: validate-login			   
	 *	Page: Validate the authentication  
	 *************************************/
	
	public function validateLoginAction()
    {
		$userSession= new Container('fo_user');
		$request	= $this->getRequest();
		
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['login_email']) && trim($formData['login_email']) != '' && isset($formData['login_password']) && trim($formData['login_password']) != '') {
				$results	= $this->selectUser($formData['login_email'], $formData['login_password']);
				
				while($results->hasNext()) {
					$resultArray	= $results->getNext();
				}
				
				if(isset($resultArray) && is_array($resultArray) && count($resultArray) > 0) {	// Valid email address & password
					$groupResult	= $this->selectGroup($resultArray['user_group']);
					while($groupResult->hasNext()) {
						$groupArray	= $groupResult->getNext();
					}
					if(isset($groupArray['group_name']))
						$resultArray['user_groupname']	= $groupArray['group_name'];
					
					$userSession->userSession	= $resultArray;
					
					//	Friend Invitation Acceptance Process
					$tempSession	= new Container('fo_ftemp_session');
					if(isset($tempSession->tempSession['referral']) && $tempSession->tempSession['referral'] != '') {
						$flag	= $this->insertFriend($tempSession->tempSession['referral'], $userSession->userSession['_id']);
						if($flag) {
							$conn		= $this->connect();
							$collection	= $conn->snapstate->friend_list;
							$query		= array('user_id'	=> (string)$userSession->userSession['_id'],
												'friend_id'	=> (string)$tempSession->tempSession['referral']);
							$results	= $collection->insert($query);
						}
						//	Destroy Temporary Session Vars
						$tempSession	= new Container('fo_ftemp_session');
						$sessionArray	= array();
						foreach($tempSession->getIterator() as $key => $value) {
							$sessionArray[]	= $key;
						}
						foreach($sessionArray as $key => $value) {
							$tempSession->offsetUnset($value);
						}
						//	End
					}
					//	End
					
					$ratingArray['rating']		= $this->getRatedVideos();
					$ratingArray['friends']		= $this->getFriendsList();
					$ratingArray['videos']		= $this->getVideosList();
					//	Playlist Session
					$ratingArray['playlist']	= $this->getPlaylist();
					$userSession->mediaSession	= $ratingArray;
					
					$result	= '1';
				} else {	//	Authentication failed
					$result	= '-1';
				}
				echo $result;
				
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/**************************************
	 *	Action: validate-forget-password   
	 *	Page: Validate the Forget Password 
	 *************************************/
	
	public function validateForgetPasswordAction()
    {
		$userSession= new Container('fo_user');
		$request	= $this->getRequest();
		
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['forget_password_email']) && trim($formData['forget_password_email']) != '') {
				$results	= $this->selectUserByEmail($formData['forget_password_email']);
				
				while($results->hasNext()) {
					$resultArray	= $results->getNext();
				}
				if(isset($resultArray['_id']) && $resultArray['user_status'] == '1') {
					$password	= mt_rand();
					$document	= array('$set' => array('user_password' => md5($password)));
					$query		= array('user_email' => $resultArray['user_email']);
					$conn		= $this->connect();	
					$collection	= $conn->snapstate->users;
					$collection->update($query, $document);
					
					$emailaddress	= 'deepan@sdi.la';
					$username	= $resultArray['user_firstname'].' '.$resultArray['user_lastname'];
					$email		= $resultArray['user_email'];
					//	Forget Password Mailer
					$subject	= 'Snapstate - Forget Password';
					$message	= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
									<html xmlns="http://www.w3.org/1999/xhtml">
									<head>
									<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
									<title>Forget Password</title>
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
									    <td style="text-align: justify; line-height:18px;color:#1868AE;">Welcome '.ucwords($username).', </td>
									 </tr>
									 <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px;padding-bottom:10px; padding-top:10px;">Your existing password has been changed. Please find the login information below:</td>
									 </tr>
									  <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px; padding-bottom:10px">Email Address: ' . $email . '</td>
									 </tr>
									 <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px; padding-bottom:10px">Passsword: '.$password.'</td>
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
									    <td class="txt2" style="padding:10px 0;border:1px solid #DEDEDE; text-align:center;font-size: 11px; background:url('.DOMAINPATH.'/Front/img/mail/footer-bg.png) no-repeat; color:#fff;">© Copyright 2013 SnapState.com. All rights reserved. </td>
									  </tr>
									</table>
									
									</body>
									</html>';
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
					$headers .= 'From: Snapstate.com <admin@snapstate.com>' . "\r\n";
					$to		= 'To: ' . $email . "\r\n";
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
						$mail->AddAddress($email);
						$mail->Send();
					}
					$result	= '1';
				} else if(isset($resultArray['_id']) && $resultArray['user_status'] == '0') {	//	Account has not been activated. User has to activate.
					$password	= mt_rand();
					$document	= array('$set' => array('user_password' => md5($password)));
					$query		= array('user_email' => $resultArray['user_email']);
					$conn		= $this->connect();
					$collection	= $conn->snapstate->users;
					$collection->update($query, $document);
					
					$emailaddress	= 'deepan@sdi.la';
					$username	= $resultArray['user_firstname'].' '.$resultArray['user_lastname'];
					$email		= $resultArray['user_email'];
					//	Forget password mail with activate user account
					$link		= ACTIVATION_URL.base64_encode($resultArray['_id']);
					$subject	= 'Snapstate - Forget Password';
					$message	= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
									<html xmlns="http://www.w3.org/1999/xhtml">
									<head>
									<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
									<title>::Welcome::</title>
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
									    <td style="text-align: justify; line-height:18px;color:#1868AE;">Welcome '.ucwords($username).', </td>
									 </tr>
									 <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px;padding-bottom:10px; padding-top:10px;">Your existing password has been changed. Please find the login information below:</td>
									 </tr>
									  <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px; padding-bottom:10px">Email Address: ' . $email . '</td>
									 </tr>
									 <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px; padding-bottom:10px">Passsword: '.$password.'</td>
									 </tr>
									 <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px; padding-bottom:10px">Your account has not been activated yet. Please click the link below to activate your account:</td>
									 </tr>
									 <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px; padding-bottom:10px"><a href="'.$link.'" title="Click here to activate your account">'.$link.'</a></td>
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
									    <td class="txt2" style="padding:10px 0;border:1px solid #DEDEDE; text-align:center;font-size: 11px; background:url('.DOMAINPATH.'/Front/img/mail/footer-bg.png) no-repeat; color:#fff;">© Copyright 2013 SnapState.com. All rights reserved. </td>
									  </tr>
									</table>
									
									</body>
									</html>';
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
					$headers .= 'From: Snapstate.com <admin@snapstate.com>' . "\r\n";
					$to		= 'To: ' . $email . "\r\n";
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
						$mail->AddAddress($email);
						$mail->Send();
					}
					$result	= '1';
				} else {
					$result	= '-1';
				}
				echo $result;
				
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/**************************************
	 *	Action: validate-fblogin		   
	 *	Page: To login with fb account	   
	 *************************************/
	
	public function validateFbloginAction()
    {
		$userSession	= new Container('fo_user');
		$tempSession	= new Container('fo_temp_session');
		$request		= $this->getRequest();
		
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['user_firstname']) && trim($formData['user_firstname']) != '' && isset($formData['user_lastname']) && trim($formData['user_lastname']) != '' && isset($formData['user_email']) && trim($formData['user_email']) != '' && isset($formData['user_fbuid']) && $formData['user_fbuid'] != '') {
				$formData['email_address']	= $formData['user_email'];
				$formData['created_date']	= date('m/d/Y H:i:s');
				$results	= $this->checkEmail($formData, 1);
				
				if($results == 1) {	// Already Registered
					$results	= $this->selectFBUser($formData['user_fbuid'], $formData['email_address']);
					while($results->hasNext()) {
						$resultArray	= $results->getNext();
					}
					if(isset($resultArray) && is_array($resultArray) && count($resultArray) > 0) {	// Valid FBUID
						$groupResult	= $this->selectGroup($resultArray['user_group']);
						while($groupResult->hasNext()) {
							$groupArray	= $groupResult->getNext();
						}
						if(isset($groupArray['group_name']))
							$resultArray['user_groupname']	= $groupArray['group_name'];
						
						$userSession->userSession	= $resultArray;
						
						//	Friend Invitation Acceptance Process
						$tempSession	= new Container('fo_ftemp_session');
						if(isset($tempSession->tempSession['referral']) && $tempSession->tempSession['referral'] != '') {
							$flag	= $this->insertFriend($tempSession->tempSession['referral'], $userSession->userSession['_id']);
							if($flag) {
								$conn		= $this->connect();
								$collection	= $conn->snapstate->friend_list;
								$query		= array('user_id'	=> (string)$userSession->userSession['_id'],
													'friend_id'	=> (string)$tempSession->tempSession['referral']);
								$results	= $collection->insert($query);
							}
							//	Destroy Temporary Session Vars
							$tempSession	= new Container('fo_ftemp_session');
							$sessionArray	= array();
							foreach($tempSession->getIterator() as $key => $value) {
								$sessionArray[]	= $key;
							}
							foreach($sessionArray as $key => $value) {
								$tempSession->offsetUnset($value);
							}
							//	End
						}
						//	End
						
						$ratingArray['rating']		= $this->getRatedVideos();
						$ratingArray['friends']		= $this->getFriendsList();
						$ratingArray['videos']		= $this->getVideosList();
						//	Playlist Session
						$ratingArray['playlist']	= $this->getPlaylist();
						$userSession->mediaSession	= $ratingArray;
						
						$result	= '1';
					} else {
						$result	= $this->updateFBDetails($formData);
						$results= $this->selectFBUser($formData['user_fbuid'], $formData['email_address']);
						while($results->hasNext()) {
							$resultArray	= $results->getNext();
						}
						if(isset($resultArray) && is_array($resultArray) && count($resultArray) > 0) {	// Valid FBUID
							$groupResult	= $this->selectGroup($resultArray['user_group']);
							while($groupResult->hasNext()) {
								$groupArray	= $groupResult->getNext();
							}
							if(isset($groupArray['group_name']))
								$resultArray['user_groupname']	= $groupArray['group_name'];
							
							$userSession->userSession	= $resultArray;
							
							//	Friend Invitation Acceptance Process
							$tempSession	= new Container('fo_ftemp_session');
							if(isset($tempSession->tempSession['referral']) && $tempSession->tempSession['referral'] != '') {
								$flag	= $this->insertFriend($tempSession->tempSession['referral'], $userSession->userSession['_id']);
								if($flag) {
									$conn		= $this->connect();
									$collection	= $conn->snapstate->friend_list;
									$query		= array('user_id'	=> (string)$userSession->userSession['_id'],
														'friend_id'	=> (string)$tempSession->tempSession['referral']);
									$results	= $collection->insert($query);
								}
								//	Destroy Temporary Session Vars
								$tempSession	= new Container('fo_ftemp_session');
								$sessionArray	= array();
								foreach($tempSession->getIterator() as $key => $value) {
									$sessionArray[]	= $key;
								}
								foreach($sessionArray as $key => $value) {
									$tempSession->offsetUnset($value);
								}
								//	End
							}
							//	End
							
							$ratingArray['rating']		= $this->getRatedVideos();
							$ratingArray['friends']		= $this->getFriendsList();
							$ratingArray['videos']		= $this->getVideosList();
							//	Playlist Session
							$ratingArray['playlist']	= $this->getPlaylist();
							$userSession->mediaSession	= $ratingArray;
							
							$result	= '1';
						} else {	//	Authentication failed
							$result	= '-2';
						}
					}
				} else {
					$result		= $this->registerFBUser($formData);
					$results	= $this->selectFBUser($formData['user_fbuid'], $formData['email_address']);
					while($results->hasNext()) {
						$resultArray	= $results->getNext();
					}
					if(isset($resultArray) && is_array($resultArray) && count($resultArray) > 0) {	// Valid FBUID
						$groupResult	= $this->selectGroup($resultArray['user_group']);
						while($groupResult->hasNext()) {
							$groupArray	= $groupResult->getNext();
						}
						if(isset($groupArray['group_name']))
							$resultArray['user_groupname']	= $groupArray['group_name'];
						
						$userSession->userSession	= $resultArray;
						
						//	Friend Invitation Acceptance Process
						$tempSession	= new Container('fo_ftemp_session');
						if(isset($tempSession->tempSession['referral']) && $tempSession->tempSession['referral'] != '') {
							$flag	= $this->insertFriend($tempSession->tempSession['referral'], $userSession->userSession['_id']);
							if($flag) {
								$conn		= $this->connect();
								$collection	= $conn->snapstate->friend_list;
								$query		= array('user_id'	=> (string)$userSession->userSession['_id'],
													'friend_id'	=> (string)$tempSession->tempSession['referral']);
								$results	= $collection->insert($query);
							}
							//	Destroy Temporary Session Vars
							$tempSession	= new Container('fo_ftemp_session');
							$sessionArray	= array();
							foreach($tempSession->getIterator() as $key => $value) {
								$sessionArray[]	= $key;
							}
							foreach($sessionArray as $key => $value) {
								$tempSession->offsetUnset($value);
							}
							//	End
						}
						//	End
						
						$ratingArray['rating']		= $this->getRatedVideos();
						$ratingArray['friends']		= $this->getFriendsList();
						$ratingArray['videos']		= $this->getVideosList();
						//	Playlist Session
						$ratingArray['playlist']	= $this->getPlaylist();
						$userSession->mediaSession	= $ratingArray;
						
						$result	= '1';
					} else {	//	Authentication failed
						$result	= '-2';
					}
				}
				//	Friends Module Integration
				/*	if($result == 1 && isset($tempSession->tempSession['fbRequests']) && $tempSession->tempSession['fbRequests'] != '') {
					$requestIds		= str_replace('%2C', ',', $tempSession->tempSession['fbRequests']);
					$requestArray	= explode(',', $requestIds);
					if(is_array($requestArray) && count($requestArray) > 0) {
						foreach($requestArray as $key => $value) {
							$friendFlag	= $this->checkFriend($value);
							if(!$friendFlag) {
								$this->insertFriend($value);
							}
						}
					} else {
						$friendFlag	= $this->checkFriend($value);
						if(!$friendFlag) {
							$this->insertFriend($value);
						}
					}
				}	*/
				//	End
				echo $result;
				
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/***************************************************************************************
	 *	Action: Logout                                                                      
	 *	Note:	No view script for this action, it just clear the current user's credentials
	 **************************************************************************************/
	
	public function logoutAction()
	{
		//	Destroy Session Vars
		$userSession = new Container('fo_user');
		$sessionArray	= array();
		foreach($userSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$userSession->offsetUnset($value);
		}
		//	Destroy listing Session Vars
		$listingSession = new Container('fo_listing');
		$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}
		//	Destroy listing Session Vars
		$listingSession = new Container('fo_temp_session');
		$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}
		//	Destroy listing Session Vars
		$listingSession = new Container('fo_ftemp_session');
		$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}
		return $this->redirect()->toRoute('front', array('controller' => 'index', 'action' => 'index'));
	}
	/**************************************
	 *	Action: activateAction	   	       
	 *	Page: To activate user's account   
	 *************************************/
	
	public function activateAction()
    {
		$userSession= new Container('fo_user');
		$request	= $this->getRequest();
		$id			= $this->params()->fromRoute('id', 0);
		if(trim($id) != '') {
			$result	= $this->updateStatus(base64_decode($id));
			if($result['ok'] == 1) {
				$this->redirect()->toUrl('/?activate=1');
			}
		} else {
			$this->redirect()->toUrl('/?activate=0');
		}
		return $this->getResponse();
    }
	/**************************************
	 *	Action: fbreturnAction	   	       
	 *	Page: Blank page with JS		   
	 *************************************/
	
	public function fbreturnAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		return $result;
	}
	/*******************************
	 *	Action: list-videos         
	 *  Module: To list the videos  
	 *	Note:	AJAX call with view 
	 ******************************/
	
	public function listVideosAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		
		$matches	= $this->getEvent()->getRouteMatch();
		$page		= $matches->getParam('id', 1);
		$perPage	= $matches->getParam('perPage', '');
		
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
		$perPage		= PERPAGE;
		$message		= '';
		
		$recordsArray	= $this->listVideos($page, $perPage);
		$totalRecords	= $recordsArray->count();
		$resultArray	= array();
		$mediaIdArray	= array();	// Extended Friends Layer
		
		while($recordsArray->hasNext())
		{
			$tempResult		= $recordsArray->getNext();
			$resultArray[]	= $tempResult;
			$mediaIdArray[]	= (string)$tempResult['_id'];	// Extended Friends Layer
		}
		
		$userSession	= new Container('fo_user');	// Extended Friends Layer
		$tempUserSession= $userSession->mediaSession;
		$extFriendsArray= array();
		
		if(isset($tempUserSession['friends']) && is_array($tempUserSession['friends']) && count($tempUserSession['friends']) > 0 && count($mediaIdArray) > 0) {
			$extFriendsArray	= $this->getExtendedFriends($tempUserSession['friends'], $mediaIdArray);
		}
		
		$result->setVariables(array('records'		=> $resultArray,
									'extended'		=> $extFriendsArray,
									'message'		=> $message,
									'page'			=> $page,
									'perPage'		=> $perPage,
									'totalRecords'	=> $totalRecords,
									'action'		=> $this->params('action'),
									'controller'	=> $this->params('controller')));
		return $result;
    }
	/**************************************
	 *	Action: validate-editprofile       
	 *	Page: To update the user's details 
	 *************************************/
	
	public function validateEditprofileAction()
    {
		$userSession= new Container('fo_user');
		$request	= $this->getRequest();
		if(!isset($userSession->userSession['_id']) || $userSession->userSession['_id'] == '') {
			echo '2';
			die();
		}
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['user_fname']) && trim($formData['user_fname']) != '' && isset($formData['user_lname']) && trim($formData['user_lname']) != '' && isset($formData['user_email']) && trim($formData['user_email']) != '' && isset($formData['user_gender']) && $formData['user_gender'] != '') {
				$formData['email_address']	= $formData['user_email'];
				$formData['_id']			= $userSession->userSession['_id'];
				$results					= $this->checkEmail($formData, 2);
				
				if($results == 1) {	// Email Address - already exist condition
					$result	= '-1';
				} else {
					$formData['date_modified']	= date('m/d/Y H:i:s');
					$result	= $this->updateUserprofile($formData);
					if($result) {
						$tempArray	= $userSession->userSession;
						$tempArray['user_firstname']= $formData['user_fname'];
						$tempArray['user_lastname']	= $formData['user_lname'];
						$tempArray['user_email']	= $formData['user_email'];
						$tempArray['user_dob']		= $formData['user_dob'];
						$tempArray['user_gender']	= $formData['user_gender'];
						$tempArray['date_modified']	= $formData['date_modified'];
						if(isset($formData['user_photo_name']) && trim($formData['user_photo_name']) != '') {
							$tempArray['user_photo']	= $formData['user_photo_name'];
						}
						$userSession->userSession	= $tempArray;
						$result	= '1';
					}
				}
				echo $result;
				
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/**************************************
	 *	Action: profile-photo		       
	 *	Page: To update profile photo	   
	 *************************************/
	
	public function profilePhotoAction() {
		//$userSession		= new Container('fo_user');
		$error				= "";
		$msg				= "";
		$fileElementName	= 'user_photo';
		$filename			= '';
		if(!empty($_FILES[$fileElementName]['error'])) {
			switch($_FILES[$fileElementName]['error']) {
				case '1':
					$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
					break;
				case '2':
					$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
					break;
				case '3':
					$error = 'The uploaded file was only partially uploaded';
					break;
				case '4':
					$error = 'No file was uploaded.';
					break;
				case '6':
					$error = 'Missing a temporary folder';
					break;
				case '7':
					$error = 'Failed to write file to disk';
					break;
				case '8':
					$error = 'File upload stopped by extension';
					break;
				case '999':
				default:
					$error = 'No error code avaiable';
			}
		} else if(empty($_FILES['user_photo']['tmp_name']) || $_FILES['user_photo']['tmp_name'] == 'none') {
			$error = 'No file was uploaded..';
		} else {
			$msg .= " File Name: " . $_FILES['user_photo']['name'] . ", ";
			$msg .= " File Size: " . filesize($_FILES['user_photo']['tmp_name']);
			$filename			= time().'_'.str_replace(' ', '-', $_FILES['user_photo']['name']);
			if(move_uploaded_file($_FILES['user_photo']['tmp_name'], getcwd().'/public/Front/users/photo/'.$filename)) {
				$this->resizeAndFill(getcwd().'/public/Front/users/photo/'.$filename, getcwd().'/public/Front/users/photo/thumb/', $filename);
			}
		}
		echo "{";
		echo				"error: '" . $error . "',\n";
		echo				"msg: '" . $msg . "',\n";
		echo				"filename: '" . $filename . "'\n";
		echo "}";
		return $this->getResponse();
	}
	/**************************************
	 *	Action: validate-change-password   
	 *	Page: To update the password	   
	 *************************************/
	
	public function validateChangePasswordAction()
    {
		$userSession= new Container('fo_user');
		$request	= $this->getRequest();
		
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['current_password']) && $formData['current_password'] != '' && isset($formData['new_password']) && $formData['new_password'] != '' && isset($formData['new_confirm_password']) && $formData['new_confirm_password'] != '' && $formData['new_password'] == $formData['new_confirm_password'] && md5($formData['current_password']) == $userSession->userSession['user_password']) {
				$formData['_id']			= $userSession->userSession['_id'];
				$formData['new_password']	= md5($formData['new_password']);
				$results					= $this->updatePassword($formData);
				
				if($results) {
					$tempArray	= $userSession->userSession;
					$tempArray['user_password']	=	$formData['new_password'];
					$userSession->userSession	=	$tempArray;
					$result	= '1';	//	Success
				} else {
					echo "-1";	// Failed
				}
				echo $result;
				
			} else if(md5($formData['current_password']) != $userSession->userSession['user_password']) {
				echo '-1';	//	Current Password does not match
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/***************************************
	 *	Action: post-feedback				
	 *	Page: To post users feedback	   	
	 **************************************/
	
	public function postFeedbackAction()
    {
		$userSession	= new Container('fo_user');
		$request		= $this->getRequest();
		
		if($request->isPost()) {
			$formData					= $request->getPost();
			$formData['feedback_email']	= (isset($userSession->userSession['user_email']) && trim($userSession->userSession['user_email']) != '') ? $userSession->userSession['user_email'] : $formData['feedback_email'];
			
			if(isset($formData['feedback_email']) && trim($formData['feedback_email']) != '' && isset($formData['feedback_category']) && trim($formData['feedback_category']) != '' && isset($formData['feedback_message']) && trim($formData['feedback_message']) != '') {
				$result	= $this->postFeedback($formData);
				echo "1";	//	Success
				//	Feedback Mail has to be sent
				/*$emailaddress	= 'deepan@sdi.la';
				$subject	= 'Snapstate - Feedback';
				$message	= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
								<html xmlns="http://www.w3.org/1999/xhtml">
								<head>
								<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
								<title>Feedback</title>
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
									    <td style="text-align: justify; line-height:18px;color:#1868AE;">Welcome '.ucwords($formData['first_name']).' '.ucwords($formData['last_name']).', </td>
									 </tr>
									 <tr>
									    <td style="text-align: center; line-height:28px; padding-bottom:10px; padding-top: 10px; font-size:20px; color:#1868AE"><span class="quotes">“</span> Congratulations! Thank you very much for registering with Snapstate.com. <span class="quotes">”</span></td>
									 </tr>
									   <tr>
									    <td style="color: #147EC2;font-size: 14px;font-weight: normal;padding: 10px 0;">Please click the link below to activate your account:</td>
									  </tr>
									   <tr>
									    <td style="text-align: justify; line-height:18px; padding-bottom:10px"><a href="'.$link.'" title="Click here to activate your account">'.$link.'</a></td>
									 </tr>
									 <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px;padding-bottom:10px">Please find the login information below:</td>
									 </tr>
									  <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px; padding-bottom:10px">Email Address: ' . $formData['email_address'] . '</td>
									 </tr>
									 <tr>
									    <td style="color: #147EC2;text-align: justify; line-height:18px; padding-bottom:10px">Passsword: '.$formData['registration_password'].'</td>
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
				$headers .= 'From: '.$formData['feedback_email'].' <'.$formData['feedback_email'].'>' . "\r\n";
				$to		= 'To: ' . $emailaddress . "\r\n";
				$headersMessage	= $headers . $to;
				
				if(MAILER) {
					mail('', $subject, $message, $headersMessage);	
				}*/
				
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/***********************************************
	 *	Action: subscribe-newsletter				
	 *	Page: To register newsletter subscription	
	 **********************************************/
	
	public function subscribeNewsletterAction()
    {
		$request		= $this->getRequest();
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['newsletter_email']) && trim($formData['newsletter_email']) != '') {
				$formData['email_address']	= $formData['newsletter_email'];
				$results	= $this->checkSubscriptionEmail($formData, 1);
				if($results == 1) {
					echo '-1';
					die();
				}
				$result	= $this->registerNewsletter($formData);
				
				//	Confirmation Mail has to be sent
				$emailaddress	= 'deepan@sdi.la';
				$subject	= 'Snapstate - Newsletter Subscription';
				$message	= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
								<html xmlns="http://www.w3.org/1999/xhtml">
								<head>
								<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
								<title>Feedback</title>
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
									    <td style="text-align: justify; line-height:18px;color:#1868AE;">Hi, </td>
									 </tr>
									 <tr>
									    <td style="text-align: center; line-height:28px; padding-bottom:10px; padding-top: 10px; font-size:20px; color:#1868AE"><span class="quotes">“</span> Congratulations! Thank you very much for Newsletter Subscription. <span class="quotes">”</span></td>
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
				$to		= 'To: ' . $formData['newsletter_email'] . "\r\n";
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
						$mail->AddAddress($formData['newsletter_email']);
						$mail->Send();
				}
				echo "1";	//	Success
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/**************************************
	 *	Action: testAction	   	       	   
	 *	Page: Blank page with Session	   
	 *************************************/
	
	public function testAction()
    {
		echo "123";
		$userSession	= new Container('fo_user');
		echo '<pre>===>'; print_r($userSession->userSession); echo '</pre>';
		
		$listingSession = new Container('fo_listing');
		foreach($listingSession->getIterator() as $key => $value) {
			echo '<pre>===>'; print_r($key); echo '</pre>';
			echo '<pre>===>'; print_r($value); echo '</pre>';
		}
		return $this->getResponse();
	}
	/**************************************
	 *	Action: jstestAction	   	       
	 *	Page: Blank page				   
	 *************************************/
	
	public function jstestAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		return $result;
	}
	/**************************************
	 *	Method: isValidDate	   	 		   
	 *	Purpose: Validate Date			   
	 *************************************/
	
	function isValidDate($date)	{
		if(preg_match("/^(\d{2})-(\d{2})-(\d{4})$/", $date, $matches)) {
			if(checkdate($matches[2], $matches[1], $matches[3])) {
				return true;
			}
		}
	}
	/**************************************
	 *	Method: resizeAndFill	   	       
	 *	Purpose: Image Resize			   
	 *************************************/
	
	function resizeAndFill($file, $newimagepath, $name) {
		$newWidth	= 100;
		$newHeight	= 100;
		$max_height	= 100;
		$img		= getimagesize($file);
		$width		= $width = $img[0];
		$height		= $img[1];
		$fileinfo	= pathinfo($file);
		$file_type	= strtolower($fileinfo["extension"]);
		//$name		= $fileinfo["filename"];
		
		if($file_type == "jpg" || $file_type == "jpeg")
			$old		= imagecreatefromjpeg($file); // change according to your source type
		else if($file_type == "gif")
			$old		= imagecreatefromgif($file); // change according to your source type
		else if($file_type == "png")
		    $old		= imagecreatefrompng($file); // change according to your source type
		else if($file_type == "bmp")    
		    $old		= imagecreatefromwbmp($file); // change according to your source type
		
		$new		= imagecreatetruecolor($newWidth, $newHeight);
		$white		= imagecolorallocate($new, 255, 255, 255);
		imagefill($new, 0, 0, $white);
		
		if (($width / $height) >= ($newWidth / $newHeight)) {
		    // by width
		    $nw = $newWidth;
		    $nh = $height * ($newWidth / $width);
		    $nx = 0;
		    $ny = round(($newHeight - $nh) / 2);
		} else {
		    // by height
		    $nw = $width * ($newHeight / $height);
		    $nh = $max_height;
		    $nx = round(($newWidth - $nw) / 2);
		    $ny = 0;
		}
		imagecopyresized($new, $old, $nx, $ny, 0, 0, $nw, $nh, $width, $height);
		
		if($file_type == "jpg" || $file_type == "jpeg")
			imagejpeg($new, $newimagepath.$name, 100);
		else if($file_type == "gif")
			imagegif($new, $newimagepath.$name, 100);
		else if($file_type == "png")
		    imagepng($new, $newimagepath.$name, 9);
		else if($file_type == "bmp")
		    imagewbmp($new, $newimagepath.$name, 100);
	}
	
}
