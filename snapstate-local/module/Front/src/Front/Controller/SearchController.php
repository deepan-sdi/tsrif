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

class SearchController extends AbstractActionController
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
	
	public function listVideos($page, $limit)
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
				$value	= strtolower($value);
				foreach($keywordArray as $key => $value) {
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
	/*************************************************
	 *	Method: getVideoDetails                       
	 *  Purpose: To fetch the video details		      
	 ************************************************/
	
	public function getVideoDetails($id) {
		$conn			= $this->connect();
		$collection		= $conn->snapstate->media;
		$userSession	= new Container('fo_user');
		$results		= $collection->find(array('_id' => new \MongoID(trim($id))));
		$resultArray	= array();
		while($results->hasNext())	{
			$resultArray	= $results->getNext();
		}
		return $resultArray;
	}
	/*************************************************
	 *	Method: getVideoLikes	                      
	 *  Purpose: To fetch the video likes		      
	 ************************************************/
	
	public function getVideoLikes($id) {
		$conn			= $this->connect();
		$collection		= $conn->snapstate->media_ratings;
		$userSession	= new Container('fo_user');
		$results		= $collection->find(array('media_id' => trim($id)));
		$resultArray	= array();
		while($results->hasNext())	{
			$resultArray[]	= $results->getNext();
		}
		return $resultArray;
	}
	/*************************************************
	 *	Method: getMediaTags                       	  
	 *  Purpose: To fetch the video tags		      
	 ************************************************/
	
	public function getMediaTags($array, $option) {
		$conn			= $this->connect();
		$collection		= $conn->snapstate->media_tags;
		$results		= $collection->find(array('media_id' => array('$in' => $array)));
		$resultArray	= array();
		while($results->hasNext())	{
			$tempArray		= $results->getNext();
			if($option == 1) { // Single dimensional array
				$resultArray[]	= $tempArray['tag_id'];
			} else {	// Multi dimentional array
				$resultArray[$tempArray['media_id']][$tempArray['tag_id']]	= $tempArray['tag_id'];
			}
		}
		return $resultArray;
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
	/*************************************************
	 *	Method: getRecommendedVideos              	  
	 *  Purpose: To fetch the recommended videos	  
	 ************************************************/
	
	public function getRecommendedVideos($page = 0, $limit = 0) {
		$conn			= $this->connect();
		
		//	Recommended Videos
		$videoSession	= new Container('fo_videos_recommended');
		$keywordQuery	= array();
		$categoryQuery	= array();
		$tagQuery		= array();
		$skip			= ($page - 1) * $limit;
		
		//	Title - Keywords
		if(isset($videoSession->videoSession['title']) && trim($videoSession->videoSession['title']) != '') {
			$keywordArray	= explode(' ', $videoSession->videoSession['title']);
			$keywords		= array();
			if(is_array($keywordArray) && count($keywordArray) > 0) {
				foreach($keywordArray as $key => $value) {
					if(trim($value) != '') {
						$keywords[]	= new \MongoRegex("/".trim($value)."/");
					}
				}
			}
			$keywordQuery	= array('$in' => $keywords);
		}
		
		//	Category
		$categoryQuery	= (isset($videoSession->videoSession['category']) && trim($videoSession->videoSession['category']) != '') ? $videoSession->videoSession['category'] : array();
		
		//	Tags
		$tagsMediaArray	= array();
		if(isset($videoSession->videoSession['tags']) && is_array($videoSession->videoSession['tags']) && count($videoSession->videoSession['tags']) > 0) {
			$tagQuery		= array('$in' => $videoSession->videoSession['tags']);
			$collection		= $conn->snapstate->media_tags;
			$doc			= array('tag_id' => $tagQuery);
			$results		= $collection->find($doc);
			$tagsMediaArray	= array();
			while($results->hasNext())	{
				$tempArray			= $results->getNext();
				if($videoSession->videoSession['id'] != $tempArray['media_id']) {
					$tagsMediaArray[]	= new \MongoId($tempArray['media_id']);
				}
			}
		}
		$mongoID	= new \MongoID(trim($videoSession->videoSession['id']));
		$document	= array('$or' => array(
							array('media_title' => $keywordQuery, 'media_status' => '1', 'media_approved' => '1', '_id'	=> array('$ne' => $mongoID)),
							array('media_category' => $categoryQuery, 'media_status' => '1', 'media_approved' => '1', '_id'	=> array('$ne' => $mongoID)),
							array('_id' => array('$in' => $tagsMediaArray), 'media_status' => '1', 'media_approved' => '1')
						));
		$collection		= $conn->snapstate->media;
		$sort			= array('date_approved' => 1);
		
		if($limit > 0)
			$results	= $collection->find($document)->skip($skip)->limit($limit)->sort($sort);
		else
			$results	= $collection->find($document)->sort($sort);
		return $results;
	}
	/*************************************************
	 *	Method: doVote		                       	  
	 *  Purpose: To up & down vote the video		  
	 ************************************************/
	
	public function doVote($type, $videoId) {
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$collection	= $conn->snapstate->media_ratings;
		$vote		= ($type == 1) ? 'like' : 'dislike';
		$query		= array('media_id'	=> (string)base64_decode($videoId),
							'user_id'	=> (string)$userSession->userSession['_id'],
							'date_voted'=> date('m/d/Y H:i:s'),
							'rating' 	=> $vote
							);
		$results	= $collection->insert($query);
	}
	/*************************************************
	 *	Method: insertSearchQuery                  	  
	 *  Purpose: To insert search query 			  
	 ************************************************/
	
	public function insertSearchQuery($string) {
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$userId		= (isset($userSession->userSession['_id'])) ? (string)$userSession->userSession['_id'] : 0;
		$collection	= $conn->snapstate->media_searches;
		$query		= array('query'			=> $string,
							'user_id'		=> $userId,
							'date_searched'	=> date('m/d/Y H:i:s'));
		$results	= $collection->insert($query);
	}
	/*************************************************
	 *	Method: createPlaylist                     	  
	 *  Purpose: To create video playlist			  
	 ************************************************/
	
	public function createPlaylist($array) {
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$collection	= $conn->snapstate->playlists;
		$playlistId	= new \MongoId();
		$query		= array('_id'			=> $playlistId,
							'name'			=> $array['playlist'],
							'user_id'		=> (string)$userSession->userSession['_id'],
							'date_created'	=> date('m/d/Y H:i:s'));
		$results	= $collection->insert($query);
		return $playlistId;
	}
	/*************************************************
	 *	Method: trackVideoViews                    	  
	 *  Purpose: To track video views                 
	 ************************************************/
	
	public function trackVideoViews($videoId) {
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$userID		= (isset($userSession->userSession['_id']) && trim($userSession->userSession['_id']) != '') ? (string)$userSession->userSession['_id'] : '0';
		$collection	= $conn->snapstate->media_views;
		$query		= array('media_id'		=> (string)$videoId,
							'user_id'		=> $userID,
							'date_viewed'	=> date('m/d/Y H:i:s')
							);
		$results	= $collection->insert($query);
	}
	/*************************************************
	 *	Method: addToPlaylist                    	  
	 *  Purpose: To add video to playlist             
	 ************************************************/
	
	public function addToPlaylist($array) {
		$conn		= $this->connect();
		//$userSession= new Container('fo_user');
		//$userID		= (isset($userSession->userSession['_id']) && trim($userSession->userSession['_id']) != '') ? (string)$userSession->userSession['_id'] : '0';
		$collection	= $conn->snapstate->playlist_media;
		$query		= array('media_id'		=> $array['video_id'],
							'playlist_id'	=> $array['playlist_id']);
		$results	= $collection->insert($query);
	}
	/*************************************************
	 *	Method: getWatchedVideos              	  	  
	 *  Purpose: To fetch the watched videos	  	  
	 ************************************************/
	
	public function getWatchedVideos($page = 0, $limit = 0) {
		$conn			= $this->connect();
		$userSession	= new Container('fo_user');
		//	Watched Videos
		$videoWatchedSession	= new Container('fo_videos_watched');
		$skip			= ($page - 1) * $limit;
		$collection		= $conn->snapstate->media_views;
		$sort			= array('date_viewed' => 1);
		$document		= array('user_id' => (string)$userSession->userSession['_id']);
		
		/*	if($limit > 0)
			$results	= $collection->find($document)->skip($skip)->limit($limit)->sort($sort);
		else
			$results	= $collection->find($document)->sort($sort);
		return $results;	*/
		
		/*	// use all fields
		$keys = array("media_id" => 1);;
		// set intial values
		$initial = array("items" => array());
		// JavaScript function to perform
		$reduce = "function (obj, prev) { prev.items.push(obj.date_viewed); }";
		$condition = array('condition' => $document);
		$results	= $collection->group($keys, $initial, $reduce, $condition);
		return $results;	
			*/
		
		$results	= $collection->aggregate(array(
						    array('$match' => $document),
						    array('$group' => array('_id' => '$media_id', 'views' => array('$sum' => 1))),
						));
		$resultArray['total']	= (isset($results['result'])) ? count($results['result']) : 0;
		$sort		= array('views' => -1);
		$results	= $collection->aggregate(array(
							array('$match' => $document),
						    array('$group' => array('_id' => '$media_id', 'date' => array('$max' => '$date_viewed'), 'views' => array('$sum' => 1))),
						    array('$sort' => $sort),
							array('$skip' => $skip),
							array('$limit' => $limit),
						));
		$resultArray['records']	= (isset($results['result'])) ? $results['result'] : array();
		return $resultArray;
		
	}
	/*******************************************
	 *	Method: getMediaDetails    				
	 *  Purpose: To fetch the media details		
	 ******************************************/
	
	public function getMediaDetails($array) {
		$conn		= $this->connect();
		$idsArray	= array();
		foreach($array as $key => $value) {
			$idsArray[]	= new \MongoID($value);
		}
		$document		= array('_id' => array('$in' => $idsArray), 'media_status' => '1', 'media_approved' => '1');
		$collection		= $conn->snapstate->media;
		$results		= $collection->find($document);
		$resultArray	= array();
		while($results->hasNext())
		{
			$resultArray[]	= $results->getNext();
		}
		return $resultArray;
	}
	/*************************************************
	 *	Method: getTopVotedVideos              	  	  
	 *  Purpose: To fetch the top voted videos	  	  
	 ************************************************/
	
	public function getTopVotedVideos($page = 0, $limit = 0) {
		$conn			= $this->connect();
		$userSession	= new Container('fo_user');
		//	Top voted Videos
		$videoVotedSession	= new Container('fo_videos_voted');
		$skip			= ($page - 1) * $limit;
		$collection		= $conn->snapstate->media_ratings;
		//	Count
		$results		= $collection->aggregate(array(
						    array('$match' => array('rating' => 'like')),
							array('$group' => array('_id' => '$media_id', 'votes' => array('$sum' => 1))),
						));
		$resultArray['total']	= (isset($results['result'])) ? count($results['result']) : 0;
		//	Records
		//$sort		= array('votes' => -1);
		$results	= $collection->aggregate(array(
							array('$match' => array('rating' => 'like')),
							array('$group' => array('_id' => '$media_id', 'votes' => array('$sum' => 1))),
						    //array('$sort' => $sort),
							array('$skip' => $skip),
							array('$limit' => $limit),
						));
		$resultArray['records']	= (isset($results['result'])) ? $results['result'] : array();
		return $resultArray;
		
	}
	/*************************************************
	 *	Method: contributeVideo                    	  
	 *  Purpose: To insert contributed video          
	 ************************************************/
	
	public function contributeVideo($formdata) {
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$collection	= $conn->snapstate->media;
		if(isset($userSession->userSession['_id']) && $userSession->userSession['_id'] != '' && isset($userSession->userSession['user_group']) && $userSession->userSession['user_group'] == CONTRIBUTOR_GROUP_ID) {
			$query		= array('_id'				=> $formdata['_id'],
								'user_id'			=> (string)$formdata['user_id'],
								'media_url'			=> $formdata['media_url'],
								'media_title'		=> $formdata['media_title'],
								'media_title_lower'	=> $formdata['media_title_lower'],
								'media_description'	=> $formdata['media_description'],
								'media_category'	=> $formdata['media_category'],
								'media_length'		=> $formdata['media_length'],
								'media_status'		=> '1',
								'media_approved'	=> '1',
								'approved_user_id'	=> (string)$formdata['user_id'],
								'date_approved'		=> date('m/d/Y H:i:s'),
								'date_added'		=> date('m/d/Y H:i:s')
								);
			$results	= $collection->insert($query);
		} else if(isset($userSession->userSession['_id']) && $userSession->userSession['_id'] != '') {
			$query		= array('_id'				=> $formdata['_id'],
							'user_id'			=> (string)$formdata['user_id'],
							'media_url'			=> $formdata['media_url'],
							'media_title'		=> $formdata['media_title'],
							'media_title_lower'	=> $formdata['media_title_lower'],
							'media_description'	=> $formdata['media_description'],
							'media_category'	=> $formdata['media_category'],
							'media_length'		=> $formdata['media_length'],
							'media_status'		=> '1',
							'media_approved'	=> '2',
							'approved_user_id'	=> '',
							'date_approved'		=> '',
							'date_added'		=> date('m/d/Y H:i:s')
							);
			$results	= $collection->insert($query);
		}
		
		// Tags insertion
		if(isset($userSession->userSession['_id']) && $userSession->userSession['_id'] != '' && isset($formdata['media_tags']) && is_array($formdata['media_tags']) && count($formdata['media_tags']) > 0) {
			$collection	= $conn->snapstate->media_tags;
			foreach($formdata['media_tags'] as $key => $value) {
				$document	= array('media_id'	=> (string)$formdata['_id'], 
									'user_id'	=> (string)$userSession->userSession['_id'],
									'tag_id'	=> $value);
				$results	= $collection->insert($document);
			}
		}
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
	/**********************************
	 *	Method: getPlaylistName		   
	 *  Module: To get Playlist name   
	 *********************************/
	
	public function getPlaylistName($id) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->playlists;
		$query		= array('_id' => new \MongoID($id));
		$cursor		= $collection->find($query);
		$resultArray= array();
		while($cursor->hasNext())
		{
			$resultArray	= $cursor->getNext();
		}
		return $resultArray;
	}
	/***********************************************
	 *	Method: listContributedVideos	  			
	 *  Purpose: To select the contributed videos	
	 **********************************************/
	
	public function listContributedVideos($page, $limit)
	{
		//	Session for listing
		$listingSession	= new Container('fo_listing');
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media;
		$skip		= ($page - 1) * $limit;
		$sort		= array('date_approved' => 0);
		$document	= array('media_status' => '1', 'media_approved' => '2');
		
		$cursor		= $collection->find($document)->skip($skip)->limit($limit)->sort($sort);
		return $cursor;
	}
	/*************************************************
	 *	Method: getUserDetails                    	  
	 *  Purpose: To fetch the user details		      
	 ************************************************/
	
	public function getUserDetails($array) {
		$conn			= $this->connect();
		$collection		= $conn->snapstate->users;
		$results		= $collection->find(array('_id' => array('$in' => $array)));
		$resultArray	= array();
		while($results->hasNext())	{
			$tempArray		= $results->getNext();
			$resultArray[(string)$tempArray['_id']]	= $tempArray['user_firstname'].' '.$tempArray['user_lastname'];
		}
		return $resultArray;
	}
	/*******************************
	 *	Method: approveVideo	   	
	 *  Purpose: To approve video	
	 ******************************/
	
	public function approveVideo($userId, $mediaId, $option)
	{
		$conn		= $this->connect();
		$document	= array('$set' => array('media_approved' => (string)$option, 'date_approved' => date('m/d/Y H:i:s'), 'approved_user_id' => $userId));
		$query		= array('_id' => new \MongoID($mediaId));
		$collection	= $conn->snapstate->media;
		$result		= $collection->update($query, $document);
		//return $result;
		//return $conn->lastError();
	}
	/*************************************************
	 *	Method: updateFlag	                    	  
	 *  Purpose: To update media flag                 
	 ************************************************/
	
	public function updateFlag($videoId, $userId, $flag) {
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$collection	= $conn->snapstate->media_flags;
		$query		= array('media_id'	=> (string)$videoId,
							'user_id'	=> $userId,
							'flag'		=> $flag
							);
		$results	= $collection->insert($query);
	}
	/*************************************************
	 *	Method: deletePlaylist                   	  
	 *  Purpose: To delete a playlist			      
	 ************************************************/
	
	public function deletePlaylist($array) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->playlists;
		$query		= array('_id' => new \MongoID($array['playlist_id']));
		$results	= $collection->remove($query);
		
		$collection	= $conn->snapstate->playlist_media;
		$query		= array('playlist_id' => $array['playlist_id']);
		$results	= $collection->remove($query);
	}
	/*************************************************
	 *	Method: deletePlaylistVideo                	  
	 *  Purpose: To delete a playlist video		      
	 ************************************************/
	
	public function deletePlaylistVideo($array) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->playlist_media;
		$query		= array('_id' => new \MongoID($array['id']));
		$results	= $collection->remove($query);
	}
	/*************************************************
	 *	Method: getMediaUser                    	  
	 *  Purpose: To fetch the user details		      
	 ************************************************/
	
	public function getMediaUser($mediaId) {
		$conn			= $this->connect();
		$collection		= $conn->snapstate->media;
		$results		= $collection->find(array('_id' => new \MongoID($mediaId)));
		$resultArray	= array();
		while($results->hasNext())	{
			$tempArray	= $results->getNext();
			$userId		= $tempArray['user_id'];
			$collection	= $conn->snapstate->users;
			$results	= $collection->find(array('_id' => new \MongoID($userId)));
			while($results->hasNext())	{
				$tempUserArray	= $results->getNext();
				//$userArray		= $tempUserArray['user_firstname'].' '.$tempUserArray['user_lastname'];
				return $tempUserArray;
			}
			
		}
	}
	/***************************************
	 *	Method: listPlaylistVideos	      	
	 *  Purpose: To list the playlsit videos
	 **************************************/
	
	public function listPlaylistVideos($playlist, $page, $limit)
	{
		$userSession	= new Container('fo_user');
		//	Session for listing
		$listingSession	= new Container('fo_listing');
		$conn			= $this->connect();
		//	Fetch Medias Id from Playlist
		$collection		= $conn->snapstate->playlist_media;
		$cursor			= $collection->find(array('playlist_id' => $playlist));
		$tempMediaArray	= array();
		$mediaArray		= array();
		$mediaDetails	 = array();
		$tempStringMediaArray	= array();
		$entireMediaDetail	= array();
		
		while($cursor->hasNext())	{
			$playlistResult		= $cursor->getNext();
			$tempMediaArray[]	= new \MongoID($playlistResult['media_id']);
			$tempStringMediaArray[]	= $playlistResult['media_id'];
		}
		//	Check whether the medias are approved
		if(count($tempMediaArray) > 0) {
			$collection	= $conn->snapstate->media;
			$mediaResult= $collection->find(array('_id' => array('$in' => $tempMediaArray), 'media_approved' => '1', 'media_status' => '1'));
			while($mediaResult->hasNext())	{
				$playlistResult	= $mediaResult->getNext();
				$mediaArray[]	= (string)$playlistResult['_id'];
				$entireMediaDetail[(string)$playlistResult['_id']]	= $playlistResult;
			}
		}
		$playlistIdArray	= array();
		//	Iterate Playlist array for media repeatation
		if(count($mediaArray) > 0) {
			foreach($tempStringMediaArray as $key => $value) {
				if(in_array($value, $mediaArray)) {
					$playlistIdArray[]	= $value;
				}
			}
		}
		//	Paginate the playlist media
		$collection	= $conn->snapstate->playlist_media;
		$skip		= ($page - 1) * $limit;
		$document	= array('media_id' => array('$in' => $playlistIdArray));
		//	Fetch media records
		$cursor		= $collection->find($document)->skip($skip)->limit($limit);
		while($cursor->hasNext())	{
			$temp	= $cursor->getNext();
			$mediaDetails[(string)$temp['_id']]	= $temp['media_id'];
		}
		
		//entireMediaDetail
		$totalRecords	= $cursor->count();
		$playlistMediaResult	= array();
		if(count($mediaDetails) > 0) {
			foreach($mediaDetails as $key => $value) {
				$playlistMediaResult[$key]	= $entireMediaDetail[$value];
			}
		}
		
		$tempUserSession= $userSession->mediaSession;
		$extFriendsArray= array();
		
		if(isset($tempUserSession['friends']) && is_array($tempUserSession['friends']) && count($tempUserSession['friends']) > 0 && count($mediaDetails) > 0) {
			$extFriendsArray	= $this->getExtendedFriends($tempUserSession['friends'], $mediaDetails);
		}
		$result	= array('records' => $playlistMediaResult, 'total' => $totalRecords, 'extended' => $extFriendsArray);
		return $result;
	}
	/********************************************************************************************
	 *	Action: search                                                                           
	 *	Page: It acts as a default page.                                                         
	 *******************************************************************************************/
	
	public function searchAction()
	{
		$userSession = new Container('fo_user');
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
	/********************************************************************************************
	 *	Action: view-video                                                                       
	 *	Page: video detail page			                                                         
	 *******************************************************************************************/
	
	public function viewVideoAction()
	{
		$userSession = new Container('fo_user');
		$pageSession = new Container('fo_page');
		$this->layout('frontend');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		$request	= $this->getRequest();
		$id			= $this->params()->fromRoute('id', 0);
		$originalID	= base64_decode($id);
		$videoArray	= $this->getVideoDetails($originalID);
		
		//	Track Video Views
		if(!isset($pageSession->pageSession['last_video']) || (isset($pageSession->pageSession['last_video']) && $pageSession->pageSession['last_video'] != $originalID)) {
			$this->trackVideoViews($originalID);
			$pageSession->pageSession	= array('last_video' => $originalID);
			
			//	Assign the recently viewed video in session
			$mediaArray						= $userSession->mediaSession;
			$videosArray					= $mediaArray['videos'];
			$videosArray[strtotime('now')]	= $originalID;
			$mediaArray['videos']			= $videosArray;
			$userSession->mediaSession		= $mediaArray;
		}
		//	Media Tags
		$mediaIdArray[]	= (string)$videoArray['_id'];
		$mediaTags		= $this->getMediaTags($mediaIdArray, 1);
		
		//echo '<pre>===>'; print_r($mediaTags); echo '</pre>';
		//	Recommended Videos
		$videoSession 		= new Container('fo_videos_recommended');
		$recommendedArray	= array('title'		=> $videoArray['media_title'],
									'category'	=> $videoArray['media_category'],
									'tags'		=> $mediaTags,
									'id'		=> new \MongoId($videoArray['_id']));
		$videoSession->videoSession	= $recommendedArray;
		
		$tempUserSession= $userSession->mediaSession;
		$extFriendsArray= array();
		
		if(isset($tempUserSession['friends']) && is_array($tempUserSession['friends']) && count($tempUserSession['friends']) > 0 && count($mediaIdArray) > 0) {
			$extFriendsArray	= $this->getExtendedFriends($tempUserSession['friends'], $mediaIdArray);
		}
		
		$likes		= $this->getVideoLikes((string)$videoArray['_id']);
		$like	= 0;
		$dislike= 0;
		if(is_array($likes) && count($likes) > 0) {
			foreach($likes as $lkey => $lvalue) {
				if($lvalue['rating'] == 'like') {
					$like++;
				} else if($lvalue['rating'] == 'dislike') {
					$dislike++;
				}
			}
		}
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
			'extended'		=> $extFriendsArray,
			'videoArray'	=> $videoArray,
			'like'			=> $like,
			'dislike'		=> $dislike,
			'action'		=> $this->params('action'),
			'controller'	=> $this->params('controller'),
		));
    }
	/********************************************************************************************
	 *	Action: recommended-videos                                                               
	 *	Page: display recommended videos via AJAX                                                
	 *******************************************************************************************/
	
	public function recommendedVideosAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		$message			= '';
		$resultArray		= array();
		$recommendedVideos	= $this->getRecommendedVideos();
		$mediaArray			= array();
		while($recommendedVideos->hasNext())	{
			$tempArray		= $recommendedVideos->getNext();
			$resultArray[]	= $tempArray;
			$mediaArray[]	= (string)$tempArray['_id'];
		}
		$userSession		= new Container('fo_user');	// Extended Friends Layer
		$tempUserSession	= $userSession->mediaSession;
		$extFriendsArray	= array();
		
		if(isset($tempUserSession['friends']) && is_array($tempUserSession['friends']) && count($tempUserSession['friends']) > 0 && count($mediaArray) > 0) {
			$extFriendsArray	= $this->getExtendedFriends($tempUserSession['friends'], $mediaArray);
		}
		$result->setVariables(array('records'		=> $resultArray,
									'message'		=> $message,
									'extended'		=> $extFriendsArray,
									'totalRecords'	=> $recommendedVideos->count(),
									'action'		=> $this->params('action'),
									'controller'	=> $this->params('controller')));
		return $result;
	}
	/********************************************************************************************
	 *	Action: view-recommended                                                                 
	 *	Page: It loads the videos via AJAX call                                                  
	 *******************************************************************************************/
	
	public function viewRecommendedAction()
	{
		if(!isset($_SERVER['HTTP_REFERER'])) {
			return $this->redirect()->toRoute('front', array('controller' => 'index', 'action' => 'index'));
		}
		$userSession = new Container('fo_user');
		$this->layout('frontend');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
			'controller'	=> $this->params('controller'),
			'action'		=> $this->params('action'),
		));
    }
	/*******************************
	 *	Action: list-videos         
	 *  Module: To list the videos  
	 *	Note:	AJAX call with view 
	 ******************************/
	
	public function listRecommendedAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		
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
		$perpage		= PERPAGE;
		$recordsArray	= $this->getRecommendedVideos($page, $perPage);
		$totalRecords	= $recordsArray->count();
		$resultArray	= array();
		$mediaIdArray	= array();
		
		while($recordsArray->hasNext())
		{
			$tempRecordArray	= $recordsArray->getNext();
			$resultArray[]		= $tempRecordArray;
			$mediaIdArray[]		= (string)$tempRecordArray['_id'];
		}
		$userSession	= new Container('fo_user');	// Extended Friends Layer
		$tempUserSession= $userSession->mediaSession;
		$extFriendsArray= array();
		
		if(isset($tempUserSession['friends']) && is_array($tempUserSession['friends']) && count($tempUserSession['friends']) > 0 && count($mediaIdArray) > 0) {
			$extFriendsArray	= $this->getExtendedFriends($tempUserSession['friends'], $mediaIdArray);
		}
		
		$result->setVariables(array('records'		=> $resultArray,
									'message'		=> $message,
									'page'			=> $page,
									'perPage'		=> $perPage,
									'extended'		=> $extFriendsArray,
									'totalRecords'	=> $totalRecords,
									'action'		=> $this->params('action'),
									'controller'	=> $this->params('controller')));
		return $result;
    }
	/***************************************************************************************
	 *	Action: vote-video                                                                  
	 *	Page: AJAX page for up & down voting												
	 **************************************************************************************/
	
	public function voteVideoAction()
	{
		$userSession	= new Container('fo_user');
		$request		= $this->getRequest();
		
		if(isset($userSession->userSession['_id']) && trim($userSession->userSession['_id']) != '') {
			if($request->isPost()) {
				$formData	= $request->getPost();
				if(!isset($userSession->mediaSession['rating'][base64_decode($formData['videoId'])]) && isset($formData['type']) && ($formData['type'] == '1' || $formData['type'] == '2')) {
					$this->doVote($formData['type'], $formData['videoId']);
					$typeText	= ($formData['type'] == 1) ? 'like' : 'dislike';
					if(isset($userSession->mediaSession['rating'])) {
						$tempArray['rating']										= $userSession->mediaSession['rating'];
						//$tempArray['rating'][base64_decode($formData['videoId'])]	= base64_decode($formData['videoId']);
						$tempArray['rating'][base64_decode($formData['videoId'])]	= $typeText;
						$userSession->mediaSession									= $tempArray;
					} else {
						//$tempArray['rating'][base64_decode($formData['videoId'])]	= base64_decode($formData['videoId']);
						$tempArray['rating'][base64_decode($formData['videoId'])]	= $typeText;
						$userSession->mediaSession									= $tempArray;
					}
					echo trim($formData['type']);
				} else if(isset($userSession->mediaSession['rating'][base64_decode($formData['videoId'])])) {
					echo "-2";	// Voted
				} else {
					echo "-1";	//	improper data
				}
				
			} else {
				echo "-1";	//	improper data
			}
		} else {
			echo "0";	//	user session is in-active
		}
		return $this->getResponse();
	}
	/********************************************************************************************
	 *	Action: view-watched	                                                                 
	 *	Page: It loads the watched videos via AJAX call                                          
	 *******************************************************************************************/
	
	public function viewWatchedAction()
	{
		$userSession	= new Container('fo_user');
		if(!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('front', array('controller' => 'index', 'action' => 'index'));
		}
		$this->layout('frontend');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
			'controller'	=> $this->params('controller'),
			'action'		=> $this->params('action'),
		));
    }
	/***************************************
	 *	Action: list-watched    	    	
	 *  Module: To list the watched videos  
	 *	Note:	AJAX call with view 		
	 **************************************/
	
	public function listWatchedAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		
		$matches	= $this->getEvent()->getRouteMatch();
		$page		= $matches->getParam('id', 1);
		$pageNav	= $matches->getParam('perPage', '1');
		
		//	Session for listing
		$listingSession = new Container('fo_listing');
		if($page == '0') {
			$listingSession->page	= 1;
			$page	= 1;
		} else if($listingSession->offsetExists('page')) {
			$page	= ($pageNav == 1) ? $listingSession->page-1 : $listingSession->page+1;
			$listingSession->page	= $page;
		} else {
			$page	= 1;
		}
		$message	= '';
		$perpage	= PERPAGE;
		//	Fetch Media Ids
		$recordsArray	= $this->getWatchedVideos($page, 2);
		$totalRecords	= (isset($recordsArray['total'])) ? $recordsArray['total'] : 0;
		
		//	Fetch Media Details
		$mediaIdArray	= array();
		$tempIdArray	= array();
		
		if(isset($recordsArray['records']) && is_array($recordsArray['records']) && count($recordsArray['records']) > 0) {
			foreach($recordsArray['records'] as $pkey => $pvalue) {
				$mediaIdArray[$pvalue['_id']]['id']		= $pvalue['_id'];
				$mediaIdArray[$pvalue['_id']]['views']	= $pvalue['views'];
				$mediaIdArray[$pvalue['_id']]['date']	= $pvalue['date'];
				$tempIdArray[]							= $pvalue['_id'];
			}
			$mediaDetails	= $this->getMediaDetails($tempIdArray);
		}
		
		$resultArray	= $mediaDetails;
		
		$userSession	= new Container('fo_user');	// Extended Friends Layer
		$tempUserSession= $userSession->mediaSession;
		$extFriendsArray= array();
		
		if(isset($tempUserSession['friends']) && is_array($tempUserSession['friends']) && count($tempUserSession['friends']) > 0 && count($tempIdArray) > 0) {
			$extFriendsArray	= $this->getExtendedFriends($tempUserSession['friends'], $tempIdArray);
		}
		$result->setVariables(array('records'		=> $resultArray,
									'message'		=> $message,
									'page'			=> $page,
									'perPage'		=> $perPage,
									'extended'		=> $extFriendsArray,
									'mediaArray'	=> $mediaIdArray,
									'totalRecords'	=> $totalRecords,
									'action'		=> $this->params('action'),
									'controller'	=> $this->params('controller')));
		return $result;
    }
	/***********************************
	 *	Action: top-videos        		
	 *  Module: To list the top videos  
	 *	Note:	AJAX call with view 	
	 **********************************/
	
	public function topVotedVideosAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		
		$matches	= $this->getEvent()->getRouteMatch();
		$page		= $matches->getParam('id', 1);
		$pageNav	= $matches->getParam('perPage', '1');
		
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
		$message	= '';
		//	Fetch Media Ids
		$recordsArray	= $this->getTopVotedVideos($page, 18);
		$totalRecords	= (isset($recordsArray['total'])) ? $recordsArray['total'] : 0;
		
		//	Fetch Media Details
		$mediaIdArray	= array();
		$tempIdArray	= array();
		
		if(isset($recordsArray['records']) && is_array($recordsArray['records']) && count($recordsArray['records']) > 0) {
			foreach($recordsArray['records'] as $pkey => $pvalue) {
				$mediaIdArray[$pvalue['_id']]['id']		= $pvalue['_id'];
				$mediaIdArray[$pvalue['_id']]['votes']	= $pvalue['votes'];
				$tempIdArray[]							= $pvalue['_id'];
			}
			$mediaDetails	= $this->getMediaDetails($tempIdArray);
		}
		$resultArray	= $mediaDetails;
		
		$userSession	= new Container('fo_user');	// Extended Friends Layer
		$tempUserSession= $userSession->mediaSession;
		$extFriendsArray= array();
		
		if(isset($tempUserSession['friends']) && is_array($tempUserSession['friends']) && count($tempUserSession['friends']) > 0 && count($tempIdArray) > 0) {
			$extFriendsArray	= $this->getExtendedFriends($tempUserSession['friends'], $tempIdArray);
		}
		$result->setVariables(array('records'		=> $resultArray,
									'message'		=> $message,
									'page'			=> $page,
									'perPage'		=> $perPage,
									'extended'		=> $extFriendsArray,
									'mediaArray'	=> $mediaIdArray,
									'totalRecords'	=> $totalRecords,
									'action'		=> $this->params('action'),
									'controller'	=> $this->params('controller')));
		return $result;
    }
	/********************************************************************************************
	 *	Action: top-videos                                                                       
	 *	Page: Displays the top videos via AJAX call                                              
	 *******************************************************************************************/
	
	public function topVideosAction()
	{
		$this->layout('frontend');
		$result = new ViewModel();
		$result->setVariables(array('action'	=> $this->params('action'),
									'controller'=> $this->params('controller')));
		return $result;
    }
	/********************************************************************************************
	 *	Action: filter                                                                           
	 *	Page: Filters the vidoes based on the criteria                                           
	 *******************************************************************************************/
	
	public function filterAction()
	{
		$userSession = new Container('fo_user');
		$this->layout('frontend');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		//	Destroy listing Session Vars
		$listingSession = new Container('fo_listing');
		/*	$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}	*/
		
		if ($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['search'])) {
				//	Keyword
				if($formData['search'] != '')
					$listingSession->keyword	= $formData['search'];
				else
					$listingSession->keyword	= '';
				
				//	Track Search Keyword Query
				$this->insertSearchQuery($listingSession->keyword);
				
			} else {
				//	Category
				if(isset($formData['category']) && $formData['category'] != '')
					$listingSession->category	= $formData['category'];
				else
					$listingSession->category	= '';
				//	Ranking
				if(isset($formData['ranking']) && $formData['ranking'] != '')
					$listingSession->ranking	= $formData['ranking'];
				else
					$listingSession->ranking	= '';
				//	Length
				if(isset($formData['length']) && $formData['length'] != '')
					$listingSession->length	= $formData['length'];
				else
					$listingSession->length	= '';
				//	Friend
				if(isset($formData['friend']) && $formData['friend'] != '')
					$listingSession->friend	= $formData['friend'];
				else
					$listingSession->friend	= '';
				//	Seen
				if(isset($formData['seen']) && $formData['seen'] != '')
					$listingSession->seen	= $formData['seen'];
				else
					$listingSession->seen	= '';
			}
		}
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
			'action'		=> $this->params('action'),
			'controller'	=> $this->params('controller'),
		));
    }
	/********************************************************************************************
	 *	Action: contributed-videos                                                               
	 *	Page: It loads the contributed videos via AJAX call                                      
	 *******************************************************************************************/
	
	public function contributedVideosAction()
	{
		$userSession = new Container('fo_user');
		$this->layout('frontend');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		if(!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('front', array('controller' => 'index', 'action' => 'index'));
		}
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
			'controller'	=> $this->params('controller'),
			'action'		=> $this->params('action'),
		));
    }
	/********************************************
	 *	Action: list-contributed-videos          
	 *  Module: To list the videos  			 
	 *	Note:	AJAX call with view 			 
	 *******************************************/
	
	public function listContributedAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		
		$matches	= $this->getEvent()->getRouteMatch();
		$page		= $matches->getParam('id', 1);
		$pageNav	= $matches->getParam('perPage', '1');
		
		//	Session for listing
		$listingSession = new Container('fo_listing');
		if($page == '0') {
			$listingSession->page	= 1;
			$page	= 1;
		} else if($listingSession->offsetExists('page')) {
			$page	= ($pageNav == 1) ? $listingSession->page-1 : $listingSession->page+1;
			$listingSession->page	= $page;
		} else {
			$page	= 1;
		}
		$message		= '';
		$perpage		= PERPAGE;
		
		$recordsArray	= $this->listContributedVideos($page, 2);
		$totalRecords	= $recordsArray->count();
		$resultArray	= array();
		$userNameArray	= array();
		$tagNameArray	= array();
		$userArray		= array();
		
		while($recordsArray->hasNext())
		{
			$contributedVideoResult	= $recordsArray->getNext();
			$resultArray[]			= $contributedVideoResult;
			//	Get User Info
			$userArray[]			= new \MongoID($contributedVideoResult['user_id']);
			//	Get Media Tags
			$mediaArray[]			= (string)$contributedVideoResult['_id'];
		}
		if(count($userArray) > 0) {
			$userNameArray		= $this->getUserDetails($userArray);
			$tagNameArray		= $this->getMediaTags($mediaArray, 2);
		}
		//echo '<pre>===>'; print_r($tagNameArray); echo '</pre>';
		$result->setVariables(array('records'		=> $resultArray,
									'message'		=> $message,
									'page'			=> $page,
									'perPage'		=> 4,
									'totalRecords'	=> $totalRecords,
									'tagNameArray'	=> $tagNameArray,
									'userNameArray'	=> $userNameArray,
									'action'		=> $this->params('action'),
									'controller'	=> $this->params('controller')));
		return $result;
    }
	/***********************************************
	 *	Action: post-contributed-video	   			
	 *	Page: AJAX call to post contributed videos	
	 **********************************************/
	
	public function postContributedVideoAction() {
		$userSession= new Container('fo_user');
		$request	= $this->getRequest();
		
		if(!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			echo '-1';
			die();
		}
		
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['contribute_video_url']) && $formData['contribute_video_url'] != '' && 
				isset($formData['contribute_video_title']) && $formData['contribute_video_title'] != '' && 
				isset($formData['contribute_video_category']) && $formData['contribute_video_category'] != '') {
				$formData['_id']				= new \MongoId();
				$formData['user_id']			= $userSession->userSession['_id'];
				$formData['media_title']		= $formData['contribute_video_title'];
				$formData['media_title_lower']	= strtolower($formData['contribute_video_title']);
				$formData['media_category']		= $formData['contribute_video_category'];
				$formData['media_description']	= $formData['contribute_video_desc'];
				$formData['media_url']			= $formData['contribute_video_url'];
				$formData['media_tags']			= $formData['contribute_video_tags'];
				
				$youtubeUrl	= parse_url($formData['media_url'], PHP_URL_QUERY);
				parse_str($youtubeUrl, $params);
			    $videoId	= $params['v'];
				$feedURL	= 'http://gdata.youtube.com/feeds/api/videos/' . $videoId;
				$entry		= \simplexml_load_file($feedURL);
				if($entry === false) {
					$time	= '0';
				} else {
					$video	= $this->parseVideoEntry($entry);
					$time	= sprintf("%0.2f", $video->length/60);
				}
				if($time <= 10) {
					$formData['media_length']	= $time;
					$results	= $this->contributeVideo($formData);
					echo "1";	//	Success
				} else {
					echo "3";	// Time exceeded
				}
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
	}
	/***************************************************************************************
	 *	Action: video-approval                                                              
	 *	Page: AJAX page for video approval													
	 **************************************************************************************/
	
	public function videoApprovalAction()
	{
		$userSession	= new Container('fo_user');
		$request		= $this->getRequest();
		
		if(isset($userSession->userSession['_id']) && trim($userSession->userSession['_id']) != '') {
			if($request->isPost()) {
				$formData	= $request->getPost();
				if(isset($formData['option']) && isset($formData['mediaId']) && ($formData['option'] == '1' && $formData['mediaId'] != '')) {
					$userData	= $this->getMediaUser($formData['mediaId']);
					$this->approveVideo((string)$userSession->userSession['_id'], $formData['mediaId'], 1);
					
					//	Registration Mail has to be sent
					$emailaddress	= 'deepan@sdi.la';
					$link		= DOMAINPATH.'/video/'.base64_encode($formData['mediaId']);
					$subject	= 'Snapstate - Video Approval';
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
									    <td style="text-align: justify; line-height:18px;color:#1868AE;">Hello '.ucwords($userData['user_firstname']).' '.ucwords($userData['user_lastname']).', </td>
									 </tr>
									 <tr>
									    <td style="text-align: center; line-height:28px; padding-bottom:10px; padding-top: 10px; font-size:20px; color:#1868AE"><span class="quotes">“</span> Congratulations! Your video has been approved. <span class="quotes">”</span></td>
									 </tr>
									   <tr>
									    <td style="color: #147EC2;font-size: 14px;font-weight: normal;padding: 10px 0;">Please click the link below to view your video:</td>
									  </tr>
									   <tr>
									    <td style="text-align: justify; line-height:18px; padding-bottom:10px"><a href="'.$link.'" title="Please click the link below to view your video">'.$link.'</a></td>
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
					$to		= 'To: ' . $userData['user_email'] . "\r\n";
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
						$mail->AddAddress($userData['user_email']);
						$mail->Send();
					}
					echo '1';
				} else if(isset($formData['option']) && isset($formData['mediaId']) && ($formData['option'] == '2' && $formData['mediaId'] != '')) {
					$this->approveVideo((string)$userSession->userSession['_id'], $formData['mediaId'], 3);
					$userData	= $this->getMediaUser($formData['mediaId']);
					if(isset($formData['flag']) && trim($formData['flag']) != '') {
						$this->updateFlag($formData['mediaId'], (string)$userSession->userSession['_id'], $formData['flag']);
					}
					//	Registration Mail has to be sent
					$emailaddress	= 'deepan@sdi.la';
					$link		= DOMAINPATH.'/video/'.base64_encode($formData['mediaId']);
					$subject	= 'Snapstate - Video Disapproval';
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
									    <td style="text-align: justify; line-height:18px;color:#1868AE;">Hello '.ucwords($userData['user_firstname']).' '.ucwords($userData['user_lastname']).', </td>
									 </tr>
									 <tr>
									    <td style="text-align: center; line-height:28px; padding-bottom:10px; padding-top: 10px; font-size:20px; color:#1868AE"><span class="quotes">“</span> Congratulations! Your video has been disapproved. <span class="quotes">”</span></td>
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
					$to		= 'To: ' . $userData['user_email'] . "\r\n";
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
						$mail->AddAddress($userData['user_email']);
						$mail->Send();
					}
					echo '2';
				} else {
					echo "-1";	//	improper data
				}
				
			} else {
				echo "-1";	//	improper data
			}
		} else {
			echo "0";	//	user session is in-active
		}
		return $this->getResponse();
	}
	/***********************************************
	 *	Action: create-playlist						
	 *	Page: To create a playlist					
	 **********************************************/
	
	public function createPlaylistAction()
    {
		$userSession	= new Container('fo_user');
		$request		= $this->getRequest();
		if(!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			echo "-1";
			die();
		}
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['playlist']) && trim($formData['playlist']) != '' && isset($formData['video_id']) && trim($formData['video_id']) != '') {
				$result	= $this->createPlaylist($formData);
				$formData['playlist_id']	= (string)$result;
				$result	= $this->addToPlaylist($formData);
				
				$tempArray	= $userSession->mediaSession;
				
				//	Playlist Session
				$tempArray['playlist'][$formData['playlist_id']]	= $formData['playlist'];
				$userSession->mediaSession	= $tempArray;
				
				echo $formData['playlist_id'];	//	Success
			} else if(isset($formData['playlist']) && trim($formData['playlist']) != '') {
				$result	= $this->createPlaylist($formData);
				
				$tempArray	= $userSession->mediaSession;
				
				//	Playlist Session
				$tempArray['playlist'][(string)$result]	= $formData['playlist'];
				$userSession->mediaSession	= $tempArray;
				
				echo (string)$result;	//	Success
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/***********************************************
	 *	Action: add-to-playlist						
	 *	Page: To add a video to playlist			
	 **********************************************/
	
	public function addToPlaylistAction()
    {
		$userSession	= new Container('fo_user');
		$request		= $this->getRequest();
		if(!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			echo "-1";
			die();
		}
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['playlist_id']) && trim($formData['playlist_id']) != '' && isset($formData['video_id']) && trim($formData['video_id']) != '') {
				$result	= $this->addToPlaylist($formData);
				echo "1";	//	Success
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/***********************************************
	 *	Action: delete-playlist						
	 *	Page: To delete a playlist					
	 **********************************************/
	
	public function deletePlaylistAction()
    {
		$userSession	= new Container('fo_user');
		$request		= $this->getRequest();
		if(!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			echo "-1";
			die();
		}
		if($request->isPost()) {
			$formData	= $request->getPost();
			if(isset($formData['playlist_id']) && trim($formData['playlist_id']) != '') {
				$result	= $this->deletePlaylist($formData);
				
				$tempArray	= $userSession->mediaSession;
				if(isset($tempArray['playlist'][$formData['playlist_id']])) {
					unset($tempArray['playlist'][$formData['playlist_id']]);
				}
				$userSession->mediaSession	= $tempArray;
				echo "1";	//	Success
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/********************************************************************************************
	 *	Action: playlist                                                                         
	 *	Page: List the videos from the playlist                                                  
	 *******************************************************************************************/
	
	public function playlistAction()
	{
		$this->layout('frontend');
		$result = new ViewModel();
		$userSession = new Container('fo_user');
		if(!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('front', array('controller' => 'index', 'action' => 'index'));
		}
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
		$listingSession 			= new Container('fo_listing');
		$id							= $this->params()->fromRoute('id', 0);
		$listingSession->playlist	= $id;
		$playlistName				= $this->getPlaylistName($id);
		
		$result->setVariables(array('playlist'	=> $playlistName,
									'action'	=> $this->params('action'),
									'controller'=> $this->params('controller')));
		return $result;
    }
	/*******************************
	 *	Action: list-playlist-videos
	 *  Module: To list the videos  
	 *	Note:	AJAX call with view 
	 ******************************/
	
	public function listPlaylistVideosAction()
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
		$recordsArray	= $this->listPlaylistVideos($listingSession->playlist, $page, $perPage);
		
		$result->setVariables(array('records'		=> $recordsArray['records'],
									'message'		=> $message,
									'page'			=> $page,
									'perPage'		=> $perPage,
									'extended'		=> $recordsArray['extended'],
									'totalRecords'	=> $recordsArray['total'],
									'action'		=> $this->params('action'),
									'controller'	=> $this->params('controller')));
		return $result;
    }
	/***********************************************
	 *	Action: delete-playlist-videos				
	 *	Page: To delete a video from playlist		
	 **********************************************/
	
	public function deletePlaylistVideosAction()
    {
		$userSession	= new Container('fo_user');
		$request		= $this->getRequest();
		if(!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			echo "-1";
			die();
		}
		if($request->isPost()) {
			$formData	= $request->getPost();
			if(isset($formData['id']) && trim($formData['id']) != '') {
				$result	= $this->deletePlaylistVideo($formData);
				echo "1";	//	Success
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/***********************************************
	 *	Action: show-extended-friends				
	 *	Page: To show the extended friends layer	
	 **********************************************/
	
	public function showExtendedFriendsAction()
    {
		$userSession	= new Container('fo_user');
		$request		= $this->getRequest();
		if(!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			echo "-1";
			die();
		}
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['media_id']) && trim($formData['media_id']) != '') {
				
				$tempUserSession= $userSession->mediaSession;
				$extFriendsArray= array();
				$mediaDetails	= array($formData['media_id']);
				if(isset($tempUserSession['friends']) && is_array($tempUserSession['friends']) && count($tempUserSession['friends']) > 0 && count($mediaDetails) > 0) {
					$extFriendsArray	= $this->getExtendedFriends($tempUserSession['friends'], $mediaDetails);
				}
				$output	= '';
				if(count($extFriendsArray) > 0) {
					$inc	= 0;
					foreach($extFriendsArray as $key => $value) {
						if($inc&1 == 0 || $inc == 0) {	// even or 0
							$output	.= '<div class="row">';
						}
						$output	.= '<div class="col-md-6 col-sm-6">
										<div class="fri-ali clearfix">
											<img src="/Front/img/avatar-img.png" class="img-responsive" alt="">
							                <h2>Raj Sharma</h2>
										</div>
									</div>';
						if($inc&1 == 1 && $inc != 0) {	// odd
							$output	.= '</div>';
						}
						$inc++;
					}
					if(count($extFriendsArray)&1) {
						$output	.= '</div>';
					}
				}
				
				echo $output;	//	Success
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
		return $this->getResponse();
	}
	
	/***************************************
	 *	Method: parseVideoEntry				
	 *	Purpose: Fetch video duration		
	 **************************************/
	
	function parseVideoEntry($entry) {      
    	$obj				= new \stdClass;
		$media				= $entry->children('http://search.yahoo.com/mrss/');
        $obj->title			= $media->group->title;
        $obj->description	= $media->group->description;
		
		$yt = $media->children('http://gdata.youtube.com/schemas/2007');
        $attrs = $yt->duration->attributes();
        $obj->length = $attrs['seconds']; 
        return $obj;      
	}
}
