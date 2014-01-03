<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Cms\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

//	Session
use Zend\Session\Container;

//	Auth
use Zend\Authentication,
	Zend\Authentication\Result,
	Zend\Authentication\AuthenticationService;

//	Forms
use Cms\Form\CreateCategoryForm;
use Cms\Form\CreateTagForm;
use Cms\Form\CreateMediaForm;
use Cms\Form\FilterForm;

//	Models
use Cms\Model\Group;
use Cms\Model\Users;
use Cms\Model\MyAuthenticationAdapter;

class MediaController extends AbstractActionController
{
	
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
	/************************************************
	 *	Method: checkCategoryName  	                 
	 *  Purpose: To validate the category existence  
	 ***********************************************/
	
	public function checkCategoryName($formData, $opt) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->categories;
		$userSession = new Container('user');
		if($opt == 1) {
			$results	= $collection->find(array('category_name' => trim($formData['category_name'])));
		} else if($opt == 2) {
			$mongoID	= new \MongoID(trim($formData['_id']));
			$document	= array('_id'	=> array('$ne' => $mongoID), 'category_name' => trim($formData['category_name']));
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
	/***********************************
	 *	Method: saveCategory  	    	
	 *  Purpose: To insert a category	
	 **********************************/
	
	public function saveCategory($formData) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->categories;
		$results	= $collection->insert(array('category_name' => trim($formData['category_name']), 'category_status' => $formData['category_status']));
		if($results) {
			return 1;
		} else {
			return 0;
		}
	}
	/***************************************
	 *	Method: selectCategorybyId      	
	 *  Purpose: To select category by _id  
	 **************************************/
	
	public function selectCategorybyId($id) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->categories;
		$results	= $collection->find(array('_id' => new \MongoId($id)));
		while($results->hasNext())
		{
			$resultArray	= $results->getNext();
		}
		if(isset($resultArray)) {
			return $resultArray;
		} else {
			return 0;
		}
	}
	/**************************************
	 *	Method: updateCategory     	       
	 *  Purpose: To update category by _id 
	 *************************************/
	
	public function updateCategory($formData) {
		$document	= array('$set' => array('category_status' => $formData['category_status'], 'category_name' => trim($formData['category_name'])));
		$mongoID	= new \MongoID(trim($formData['_id']));
		$query		= array('_id' => $mongoID);
		$conn		= $this->connect();
		$collection	= $conn->snapstate->categories;
		$results	= $collection->update($query, $document);
		if($results) {
			return 1;
		} else {
			return 0;
		}
	}
	/*******************************
	 *	Method: listCategory        
	 *  Purpose: To list category   
	 ******************************/
	
	public function listCategory($page, $limit) {
		$conn			= $this->connect();
		$collection		= $conn->snapstate->categories;
		$skip			= ($page - 1) * $limit;
		$next			= ($page + 1);
		$prev			= ($page - 1);
		$userSession	= new Container('user');
		$listingSession = new Container('listing');
		
		$query	= array();
		if($listingSession->offsetExists('status') && $listingSession->status != '') {
			$query['category_status']	= $listingSession->status;
		}
		if($listingSession->offsetExists('keyword') && $listingSession->keyword != '') {
			$query['category_name']	= 	new \MongoRegex('/' . preg_quote(trim($listingSession->keyword)) . '/i');
		}
		if($listingSession->offsetExists('sortBy') && $listingSession->offsetExists('sortType') && $listingSession->sortType == 1) {
			$sort	= array($listingSession->sortBy => 1);
		} else if($listingSession->offsetExists('sortBy') && $listingSession->offsetExists('sortType') && $listingSession->sortType == 0) {
			$sort	= array($listingSession->sortBy => -1);
		} else {
			$sort	= array('category_name' => 1);
		}
		$cursor		= $collection->find($query)->skip($skip)->limit($limit)->sort($sort);
		return $cursor;
	}
	/*******************************
	 *	Method: deleteCategory      
	 *  Module: To Delete Category  
	 ******************************/
	
	public function deleteCategory($id) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->categories;
		$collection->remove(array('_id' => new \MongoId($id)));
	}
	/*******************************************
	 *	Method: checkTagName  	                
	 *  Purpose: To validate the tag existence  
	 ******************************************/
	
	public function checkTagName($formData, $opt) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->tags;
		$userSession = new Container('user');
		if($opt == 1) {
			$results	= $collection->find(array('tag_name' => trim($formData['tag_name'])));
		} else if($opt == 2) {
			$mongoID	= new \MongoID(trim($formData['_id']));
			$document	= array('_id'	=> array('$ne' => $mongoID), 'tag_name' => trim($formData['tag_name']));
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
	/*******************************
	 *	Method: saveTag  	    	
	 *  Purpose: To insert a tag 	
	 ******************************/
	
	public function saveTag($formData) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->tags;
		$results	= $collection->insert(array('tag_name' => trim($formData['tag_name']), 'tag_status' => $formData['tag_status']));
		if($results) {
			return 1;
		} else {
			return 0;
		}
	}
	/***************************************
	 *	Method: selectTagbyId      			
	 *  Purpose: To select tag by _id  		
	 **************************************/
	
	public function selectTagbyId($id) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->tags;
		$results	= $collection->find(array('_id' => new \MongoId($id)));
		while($results->hasNext())
		{
			$resultArray	= $results->getNext();
		}
		if(isset($resultArray)) {
			return $resultArray;
		} else {
			return 0;
		}
	}
	/**************************************
	 *	Method: updateTag        	       
	 *  Purpose: To update tag by _id	   
	 *************************************/
	
	public function updateTag($formData) {
		$document	= array('$set' => array('tag_status' => $formData['tag_status'], 'tag_name' => trim($formData['tag_name'])));
		$mongoID	= new \MongoID(trim($formData['_id']));
		$query		= array('_id' => $mongoID);
		$conn		= $this->connect();
		$collection	= $conn->snapstate->tags;
		$results	= $collection->update($query, $document);
		if($results) {
			return 1;
		} else {
			return 0;
		}
	}
	/*******************************
	 *	Method: listTag		        
	 *  Purpose: To list tag		
	 ******************************/
	
	public function listTag($page, $limit) {
		$conn			= $this->connect();
		$collection		= $conn->snapstate->tags;
		$skip			= ($page - 1) * $limit;
		$next			= ($page + 1);
		$prev			= ($page - 1);
		$userSession	= new Container('user');
		$listingSession = new Container('listing');
		
		$query	= array();
		if($listingSession->offsetExists('status') && $listingSession->status != '') {
			$query['tag_status']	= $listingSession->status;
		}
		if($listingSession->offsetExists('keyword') && $listingSession->keyword != '') {
			$query['tag_name']	= 	new \MongoRegex('/' . preg_quote(trim($listingSession->keyword)) . '/i');
		}
		if($listingSession->offsetExists('sortBy') && $listingSession->offsetExists('sortType') && $listingSession->sortType == 1) {
			$sort	= array($listingSession->sortBy => 1);
		} else if($listingSession->offsetExists('sortBy') && $listingSession->offsetExists('sortType') && $listingSession->sortType == 0) {
			$sort	= array($listingSession->sortBy => -1);
		} else {
			$sort	= array('tag_name' => 1);
		}
		$cursor		= $collection->find($query)->skip($skip)->limit($limit)->sort($sort);
		return $cursor;
	}
	/*******************************
	 *	Method: deleteTag		    
	 *  Module: To Delete Tag		
	 ******************************/
	
	public function deleteTag($id) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->tags;
		$collection->remove(array('_id' => new \MongoId($id)));
	}
	/***********************************
	 *	Method: deleteMediaTagsRecords	
	 *  Module: To Delete Media Tag 	
	 **********************************/
	
	public function deleteMediaTagsRecords($id) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media_tags;
		$collection->remove(array('tag_id' => (string)$id));
	}
	/**********************************
	 *	Method: listMediaCategory      
	 *  Module: To List Media Category 
	 *********************************/
	
	public function listMediaCategory() {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->categories;
		$query		= array('category_status' => '1');
		$sort		= array('category_name' => 1);
		$cursor		= $collection->find($query)->sort($sort);
		$resultArray= array();
		
		while($cursor->hasNext())
		{
			$resultArray[]	= $cursor->getNext();
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
	/************************************************
	 *	Method: checkMediaName  	                 
	 *  Purpose: To validate the media name existence
	 ***********************************************/
	
	public function checkMediaName($formData, $opt) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media;
		$userSession = new Container('user');
		if($opt == 1) {
			$results	= $collection->find(array('media_title_lower' => strtolower(trim($formData['media_title']))));
		} else if($opt == 2) {
			$mongoID	= new \MongoID(trim($formData['_id']));
			$document	= array('_id'	=> array('$ne' => $mongoID), 'media_title_lower' => strtolower(trim($formData['media_title'])));
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
	/*******************************
	 *	Method: saveMedia  	    	
	 *  Purpose: To insert a media 	
	 ******************************/
	
	public function saveMedia($formData) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media;
		
		$document	= array('media_title'		=> trim($formData['media_title']), 
							'media_title_lower'	=> strtolower(trim($formData['media_title'])), 
							'media_url'			=> trim($formData['media_url']),
							'media_category'	=> $formData['media_category'],
							'media_description'	=> trim($formData['media_description']), 
							'media_approved'	=> $formData['media_approved'],
							'media_status'		=> $formData['media_status'],
							'user_id'			=> ADMIN_USER_ID,
							'approved_user_id'	=> $formData['approveduser'],
							'date_added'		=> date('m/d/Y H:i:s'),
							'media_length'		=> trim($formData['video-length']), 
							'date_approved' 	=> $formData['dateapproved']);
		$results	= $collection->insert($document);
		if($results) {
			return $document['_id'];
		} else {
			return 0;
		}
	}
	/*******************************
	 *	Method: saveMediaTags    	
	 *  Purpose: To insert media tag
	 ******************************/
	
	public function saveMediaTags($formData, $mediaId) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media_tags;
		$inc		= 0;
		foreach($formData as $key => $value) {
			$document	= array('media_id'	=> (string)$mediaId, 
								'user_id'	=> '0',
								'tag_id'	=> $value,);
			$inc++;
			$results	= $collection->insert($document);
		}
		if($results) {
			return $inc;
		} else {
			return 0;
		}
	}
	/***************************************
	 *	Method: selectMediabyId      		
	 *  Purpose: To select media by _id  	
	 **************************************/
	
	public function selectMediabyId($id) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media;
		$results	= $collection->find(array('_id' => new \MongoId($id)));
		while($results->hasNext())
		{
			$resultArray	= $results->getNext();
		}
		if(isset($resultArray)) {
			return $resultArray;
		} else {
			return 0;
		}
	}
	/***************************************
	 *	Method: selectMediaTagsbyId    		
	 *  Purpose: To select media tags by _id
	 **************************************/
	
	public function selectMediaTagsbyId($id) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media_tags;
		$results	= $collection->find(array('media_id' => $id));
		while($results->hasNext())
		{
			$resultArray[]	= $results->getNext();
		}
		if(isset($resultArray)) {
			return $resultArray;
		} else {
			return 0;
		}
	}
	/**************************************
	 *	Method: updateMedia     	       
	 *  Purpose: To update media by _id    
	 *************************************/
	
	public function updateMedia($formData) {
		
		if(isset($formData['approvedDate']) && $formData['approvedDate'] == '00/00/0000 00:00:00' && isset($formData['media_approved']) && $formData['media_approved'] == 1) {
			$document	= array('$set' => array('media_title' => $formData['media_title'], 'media_title_lower' => strtolower(trim($formData['media_title'])), 'media_url' => trim($formData['media_url']),
											'media_category' => trim($formData['media_category']), 'media_description' => trim($formData['media_description']),
											'media_length'	=> $formData['video-length'],
											'media_approved' => trim($formData['media_approved']), 'media_status' => trim($formData['media_status']),
											'approved_user_id' => ADMIN_USER_ID, 'date_approved' => date('m/d/Y H:i:s')));
											
		} else if(isset($formData['approvedDate']) && $formData['approvedDate'] != '00/00/0000 00:00:00' && isset($formData['media_approved']) && $formData['media_approved'] == 0) {
			$document	= array('$set' => array('media_title' => $formData['media_title'], 'media_title_lower' => strtolower(trim($formData['media_title'])), 'media_url' => trim($formData['media_url']),
											'media_category' => trim($formData['media_category']), 'media_description' => trim($formData['media_description']),
											'media_length'	=> $formData['video-length'],
											'media_approved' => trim($formData['media_approved']), 'media_status' => trim($formData['media_status']),
											'approved_user_id' => '', 'date_approved' => '00/00/0000 00:00:00'));
		} else {
			$document	= array('$set' => array('media_title' => $formData['media_title'], 'media_title_lower' => strtolower(trim($formData['media_title'])), 'media_url' => trim($formData['media_url']),
											'media_category' => trim($formData['media_category']), 'media_description' => trim($formData['media_description']),
											'media_length'	=> $formData['video-length'],
											'media_status' => trim($formData['media_status'])));
		}
		
		$mongoID	= new \MongoID(trim($formData['_id']));
		$query		= array('_id' => $mongoID);
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media;
		$results	= $collection->update($query, $document);
		if($results) {
			return 1;
		} else {
			return 0;
		}
	}
	/*******************************
	 *	Method: deleteMediaTags	    
	 *  Module: To Delete Media Tags
	 ******************************/
	
	public function deleteMediaTags($id) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media_tags;
		$collection->remove(array('media_id' => $id));
	}
	/*******************************
	 *	Method: listMedia	        
	 *  Purpose: To list media		
	 ******************************/
	
	public function listMedia($page, $limit) {
		$conn			= $this->connect();
		$collection		= $conn->snapstate->media;
		$skip			= ($page - 1) * $limit;
		$next			= ($page + 1);
		$prev			= ($page - 1);
		$userSession	= new Container('user');
		$listingSession = new Container('listing');
		
		$query				= array();
		$categoryCollection	= $conn->snapstate->categories;
		$query				= array('category_status' => '1');
		$cursor				= $categoryCollection->find($query);
		$categoryArray		= array();
		
		while($cursor->hasNext())
		{
			$categoryArray[]	= $cursor->getNext();
		}
		if(count($categoryArray) > 0) {
			foreach($categoryArray as $key => $value) {
				$categoryIdArray[]	= (string)$value['_id'];
			}
			$query	= array('media_category' => array('$in' => $categoryIdArray));
		}
		if($listingSession->offsetExists('status') && $listingSession->status != '') {
			$query['media_status']	= $listingSession->status;
		}
		if($listingSession->offsetExists('keyword') && $listingSession->keyword != '') {
			$query[$listingSession->field]	= 	new \MongoRegex('/' . preg_quote(trim($listingSession->keyword)) . '/i');
		}
		if($listingSession->offsetExists('approval') && $listingSession->approval != '') {
			$query['media_approved']= $listingSession->approval;
		}
		if($listingSession->offsetExists('category') && $listingSession->category != '') {
			$query['media_category']= $listingSession->category;
		}
		if($listingSession->offsetExists('sortBy') && $listingSession->offsetExists('sortType') && $listingSession->sortType == 1) {
			$sort	= array($listingSession->sortBy => 1);
		} else if($listingSession->offsetExists('sortBy') && $listingSession->offsetExists('sortType') && $listingSession->sortType == 0) {
			$sort	= array($listingSession->sortBy => -1);
		} else {
			$sort	= array('media_title_lower' => 1);
		}
		$cursor		= $collection->find($query)->skip($skip)->limit($limit)->sort($sort);
		return $cursor;
	}
	/**********************************
	 *	Method: listUsers			   
	 *  Module: To List Users		   
	 *********************************/
	
	public function listUsers() {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		//$query		= array('user_status' => '1');
		$query		= array();
		$sort		= array('user_firstname' => 1);
		$cursor		= $collection->find($query)->sort($sort);
		$resultArray= array();
		
		while($cursor->hasNext())
		{
			$resultArray[]	= $cursor->getNext();
		}
		return $resultArray;
	}
	/*******************************
	 *	Method: deleteVideo		    
	 *  Module: To Delete Video		
	 ******************************/
	
	public function deleteVideo($id) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media;
		$collection->remove(array('_id' => new \MongoId($id)));
	}
	/***************************************
	 *	Method: listMediaTagsById 			
	 *  Purpose: To select tags by media_id 
	 **************************************/
	
	public function listMediaTagsById($mediaIdArray) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media_tags;
		$query		= array('media_id' => array('$in' => $mediaIdArray));
		$results	= $collection->find($query);
		while($results->hasNext())
		{
			$resultArray[]	= $results->getNext();
		}
		if(isset($resultArray)) {
			return $resultArray;
		} else {
			return 0;
		}
	}
	/***************************************
	 *	Method: deleteVideosByCategory      
	 *  Module: To Delete Category  		
	 **************************************/
	
	public function deleteVideosByCategory($id) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media;
		$collection->remove(array('media_category' => (string)$id));
	}
	/*******************************
	 *	Action: create-category     
	 *  Module: Manage Media        
	 ******************************/
	
	public function createCategoryAction()
    {
		//	Validate Authentication
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
		}
		$createCategoryForm	= new CreateCategoryForm();
		$createCategoryForm->get('category_status')->setLabelAttributes(array('class' => 'radio inline'));
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		//	Category creation goes here
		if ($request->isPost()) {
			$formPostData	= $request->getPost();
			$access_pages	= '';
			
			//Check whether the Category already exists in the database or not
			$results	= $this->checkCategoryName($formPostData, 1);
			
			if($results == 1) {	// Category Name already exist
				$message		= 'Category Name already exist.';
				$errorMessage	= '1';
			} else {
				$results	= $this->saveCategory($formPostData);
				if($results) {
					return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'list-category', 'id' => 1));
				}
			}
		}
		
		$id = (int) $this->params()->fromRoute('id', 0);
		if(trim($id) == 1)
			$message	= 'Category added successfully.';
		else if($message == '')
			$message	= '';
		
		return new ViewModel(array(
			'userObject'		=> $userSession->userSession,
			'createCategoryForm'=> $createCategoryForm,
			'message'			=> $message,
			'errorMessage'		=> $errorMessage,
		));
    }
	/*******************************
	 *	Action: edit-category       
	 *  Module: Manage Media        
	 ******************************/
	
	public function editCategoryAction()
    {
		//	Validate Authentication
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
		}
		//	Check whether the group id exist or not
		$id = $this->params()->fromRoute('id', 0);
        $categoryid	= $id;
		if (!$id) {
            return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'create-category'));
        }
		
		$createCategoryForm	= new CreateCategoryForm();
		$createCategoryForm->get('category_status')->setLabelAttributes(array('class' => 'radio inline'));
		$category = $this->selectCategorybyId($id);
		
		if(isset($category['_id'])) {
			$createCategoryForm->get('_id')->setAttribute('value', $category['_id']);
			$createCategoryForm->get('category_name')->setAttribute('value', $category['category_name']);
			$createCategoryForm->get('category_status')->setAttribute('value', $category['category_status']);
		}
		$createCategoryForm->get('submit')->setAttribute('value', 'Save Changes');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		//	Category creation goes here
		if ($request->isPost()) {
			$formPostData	= $request->getPost();
			$access_pages	= '';
			
			//Check whether the Category already exists in the database or not
			$results	= $this->checkCategoryName($formPostData, 2);
			
			if($results == 1) {
				$message		= 'Category Name already exist.';
				$errorMessage	= '1';
			} else {
				$results	= $this->updateCategory($formPostData);
				if($results) {
					return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'list-category', 'id' => 1));
				}
			}
		}
		
		$id = (int) $this->params()->fromRoute('id', 0);
		if(trim($id) == 1)
			$message	= 'Category updated successfully.';
		else if($message == '')
			$message	= '';
		
		return new ViewModel(array(
			'userObject'		=> $userSession->userSession,
			'createCategoryForm'=> $createCategoryForm,
			'message'			=> $message,
			'errorMessage'		=> $errorMessage,
			'categoryid'		=> $categoryid,
		));
    }
	/*******************************
	 *	Action: list-category       
	 *  Module: Manage Media        
	 ******************************/
	
	public function listCategoryAction()
	{
		//	Validate Authentication
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
		}
		//	Destroy listing Session Vars
		$listingSession = new Container('listing');
		$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}
		//	For Filter form
		$filterForm	= new FilterForm();
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			$filterForm->setData($request->getPost());
			$formData	= $request->getPost();
			
			if(isset($formData['selectStatus']) && $formData['selectStatus'] != 2)
				$listingSession->status	= $formData['selectStatus'];
			else
				$listingSession->status	= '';
			
			if(isset($formData['keyword']) && $formData['keyword'] != '')
				$listingSession->keyword	= $formData['keyword'];
			else
				$listingSession->keyword	= '';
		}
		
		$message	= '';
		$id = (int) $this->params()->fromRoute('id', 0);
		if(trim($id) == 1)
			$message	= 'Category updated successfully.';
		else if(trim($id) == 2)
			$message	= 'Category added successfully.';
		else if($message == '')
			$message	= '';
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'filterForm'	=> $filterForm,
			'message'		=> $message,
		));
	}
	/*******************************
	 *	Action: view-category       
	 *  Module: To list category    
	 *	Note:	AJAX call with view 
	 ******************************/
	
	public function viewCategoryAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		
		$matches	= $this->getEvent()->getRouteMatch();
		$page		= $matches->getParam('id', 1);
		$sortBy		= $matches->getParam('sortBy', '');
		$sortType	= $matches->getParam('sortType', '');
		$perPage	= $matches->getParam('perPage', '');
		
		//	Session for listing
		$listingSession = new Container('listing');
		$columnFlag	= 0;
		if($sortBy != '') {
			if($listingSession->sortBy == $sortBy)
				$columnFlag	= 1;
			$listingSession->sortBy	= $sortBy;
		} else if($listingSession->offsetExists('sortBy')) {
			$sortBy	= $listingSession->sortBy;
		}
		if($sortType != '') {
			if($listingSession->sortType == $sortType && $columnFlag == 1)
				$listingSession->sortType	= ($sortType == 1) ? 0 : 1;
			else
				$listingSession->sortType	= $sortType;
		} else if($listingSession->offsetExists('sortType')) {
			$sortType	= $listingSession->sortType;
		}
		if($perPage != '') {
			$listingSession->perPage	= $perPage;
		} else if($listingSession->offsetExists('perPage')) {
			$perPage	= $listingSession->perPage;
		} else {
			$perPage	= 10;
		}
		
		$message		= '';
		$recordsArray	= $this->listCategory($page, $perPage);
		$totalRecords	= $recordsArray->count();
		$resultArray	= array();
		
		while($recordsArray->hasNext())
		{
			$resultArray[]	= $recordsArray->getNext();
		}
		$result->setVariables(array('records'		=> $resultArray,
									'message'		=> $message,
									'page'			=> $page,
									'sortBy'		=> $sortBy,
									'perPage'		=> $perPage,
									'totalRecords'	=> $totalRecords,
									'controller'	=> $this->params('controller')));
		return $result;
    }
	/********************************
	 *	Action: delete-category      
	 *  Module: To delete category   
	 *	Note:	AJAX call with view  
	 *******************************/
	
	public function deleteCategoryAction()
    {
		$id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'list-category'));
        }
        $this->deleteCategory($id);
		$this->deleteVideosByCategory($id);
        return $this->getResponse();
    }
	/*******************************
	 *	Action: create-tag          
	 *  Module: Manage Media        
	 ******************************/
	
	public function createTagAction()
    {
		//	Validate Authentication
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
		}
		
		$createTagForm	= new CreateTagForm();
		$createTagForm->get('tag_status')->setLabelAttributes(array('class' => 'radio inline'));
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		//	Category creation goes here
		if ($request->isPost()) {
			$formPostData	= $request->getPost();
			$access_pages	= '';
			
			//Check whether the Tag already exists in the database or not
			$results	= $this->checkTagName($formPostData, 1);
			
			if($results == 1) {	// Tag already exist
				$message		= 'Tag already exist.';
				$errorMessage	= '1';
			} else {
				$results	= $this->saveTag($formPostData);
				if($results) {
					return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'list-tag', 'id' => 1));
				}
			}
		}
		
		$id = (int) $this->params()->fromRoute('id', 0);
		if(trim($id) == 1)
			$message	= 'Tag added successfully.';
		else if($message == '')
			$message	= '';
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'createTagForm'	=> $createTagForm,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
		));
    }
	/*******************************
	 *	Action: edit-tag            
	 *  Module: Manage Media        
	 ******************************/
	
	public function editTagAction()
    {
		//	Validate Authentication
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
		}
		//	Check whether the tag id exist or not
		$id 	= $this->params()->fromRoute('id', 0);
        $tagid	= $id;
		if (!$id) {
            return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'create-tag'));
        }
		
		$createTagForm	= new CreateTagForm();
		$createTagForm->get('tag_status')->setLabelAttributes(array('class' => 'radio inline'));
		$tag			= $this->selectTagbyId($id);
		
		if(isset($tag['_id'])) {
			$createTagForm->get('_id')->setAttribute('value', $tag['_id']);
			$createTagForm->get('tag_name')->setAttribute('value', $tag['tag_name']);
			$createTagForm->get('tag_status')->setAttribute('value', $tag['tag_status']);
		}
		$createTagForm->get('submit')->setAttribute('value', 'Save Changes');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		//	tag creation goes here
		if ($request->isPost()) {
			$formPostData	= $request->getPost();
			$access_pages	= '';
			
			//Check whether the tag already exists in the database or not
			$results	= $this->checkTagName($formPostData, 2);
			
			if($results == 1) {
				$message		= 'Tag Name already exist.';
				$errorMessage	= '1';
			} else {
				$results	= $this->updateTag($formPostData);
				if($results) {
					return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'list-tag', 'id' => 1));
				}
			}
		}	
		
		$id = (int) $this->params()->fromRoute('id', 0);
		if(trim($id) == 1)
			$message	= 'Tag updated successfully.';
		else if($message == '')
			$message	= '';
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'createTagForm'	=> $createTagForm,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
			'tagid'			=> $tagid,
		));
    }
	/*******************************
	 *	Action: list-tag		    
	 *  Module: Manage Media        
	 ******************************/
	
	public function listTagAction()
	{
		//	Validate Authentication
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
		}
		//	Destroy listing Session Vars
		$listingSession = new Container('listing');
		$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}
		//	For Filter form
		$filterForm	= new FilterForm();
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			$filterForm->setData($request->getPost());
			$formData	= $request->getPost();
			
			if(isset($formData['selectStatus']) && $formData['selectStatus'] != 2)
				$listingSession->status	= $formData['selectStatus'];
			else
				$listingSession->status	= '';
			
			if(isset($formData['keyword']) && $formData['keyword'] != '')
				$listingSession->keyword	= $formData['keyword'];
			else
				$listingSession->keyword	= '';
		}
		
		$message	= '';
		$id = (int) $this->params()->fromRoute('id', 0);
		if(trim($id) == 1)
			$message	= 'Tag updated successfully.';
		else if(trim($id) == 2)
			$message	= 'Tag added successfully.';
		else if($message == '')
			$message	= '';
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'filterForm'	=> $filterForm,
			'message'		=> $message,
		));
	}
	/*******************************
	 *	Action: view-tag       		
	 *  Module: To list tag			
	 *	Note:	AJAX call with view 
	 ******************************/
	
	public function viewTagAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		
		$matches	= $this->getEvent()->getRouteMatch();
		$page		= $matches->getParam('id', 1);
		$sortBy		= $matches->getParam('sortBy', '');
		$sortType	= $matches->getParam('sortType', '');
		$perPage	= $matches->getParam('perPage', '');
		
		//	Session for listing
		$listingSession = new Container('listing');
		$columnFlag	= 0;
		if($sortBy != '') {
			if($listingSession->sortBy == $sortBy)
				$columnFlag	= 1;
			$listingSession->sortBy	= $sortBy;
		} else if($listingSession->offsetExists('sortBy')) {
			$sortBy	= $listingSession->sortBy;
		}
		if($sortType != '') {
			if($listingSession->sortType == $sortType && $columnFlag == 1)
				$listingSession->sortType	= ($sortType == 1) ? 0 : 1;
			else
				$listingSession->sortType	= $sortType;
		} else if($listingSession->offsetExists('sortType')) {
			$sortType	= $listingSession->sortType;
		}
		if($perPage != '') {
			$listingSession->perPage	= $perPage;
		} else if($listingSession->offsetExists('perPage')) {
			$perPage	= $listingSession->perPage;
		} else {
			$perPage	= 10;
		}
		
		$message		= '';
		$recordsArray	= $this->listTag($page, $perPage);
		$totalRecords	= $recordsArray->count();
		$resultArray	= array();
		
		while($recordsArray->hasNext())
		{
			$resultArray[]	= $recordsArray->getNext();
		}
		$result->setVariables(array('records'		=> $resultArray,
									'message'		=> $message,
									'page'			=> $page,
									'sortBy'		=> $sortBy,
									'perPage'		=> $perPage,
									'totalRecords'	=> $totalRecords,
									'controller'	=> $this->params('controller')));
		return $result;
    }
	/********************************
	 *	Action: delete-tag		     
	 *  Module: To delete tag		 
	 *	Note:	AJAX call with view  
	 *******************************/
	
	public function deleteTagAction()
    {
		$id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'list-tag'));
        }
        $this->deleteTag($id);
		$this->deleteMediaTagsRecords($id);
        return $this->getResponse();
    }
	/*******************************
	 *	Action: create-media	    
	 *  Module: Manage Media        
	 ******************************/
	
	public function createMediaAction()
    {
		ini_set('max_execution_time', '1000');
		//	Validate Authentication
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
		}
		$createMediaForm	= new CreateMediaForm();
		$createMediaForm->get('media_status')->setLabelAttributes(array('class' => 'radio inline'));
		$request 			= $this->getRequest();
		$message			= '';
		$errorMessage		= '';
		
		//	Media creation goes here
		if ($request->isPost()) {
			$formPostData	= $request->getPost();
			$access_pages	= '';
			
			//Check whether the Media already exists in the database or not
			$results	= $this->checkMediaName($formPostData, 1);
			
			if($results == 1) {
				$message		= 'Video Title already exist.';
				$errorMessage	= '1';
			} else {
				$formPostData['dateapproved']	= (isset($formPostData['media_approved']) && $formPostData['media_approved'] == 1) ? date('m/d/Y H:i:s') : '00/00/0000 00:00:00';
				$formPostData['approveduser']	= (isset($formPostData['media_approved']) && $formPostData['media_approved'] == 1) ? ADMIN_USER_ID : '';
				$formPostData['_id']			= new \MongoId();
				
				$youtubeUrl	= parse_url($formPostData['media_url'], PHP_URL_QUERY);
				parse_str($youtubeUrl, $params);
			    $videoId	= $params['v'];
				$feedURL	= 'http://gdata.youtube.com/feeds/api/videos/' . $videoId;
				$entry		= \simplexml_load_file($feedURL);
				if($entry === false) {
					$time	= '0';
				} else {
					$video = $this->parseVideoEntry($entry);
					$time	= sprintf("%0.2f", $video->length/60);
				}
				$formPostData['video-length']	= $time;
					
					if($time <= 10) {
					$results						= $this->saveMedia($formPostData);
					$mediaId						= (string)$results;
					
					if($mediaId != '' && isset($formPostData['media_tags']) && is_array($formPostData['media_tags']) && count($formPostData['media_tags']) > 0) {
						$tagsCount	= $this->saveMediaTags($formPostData['media_tags'], $mediaId);
					}
					if($results) {
						return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'list-media', 'id' => 1));
					}
				} else {
					$message		= 'Video Length is exceeded the Max. duration(10 mins)';
					$errorMessage	= '1';
				}
			}
		}
		
		$id = (int) $this->params()->fromRoute('id', 0);
		if(trim($id) == 1)
			$message	= 'Video added successfully.';
		else if($message == '')
			$message	= '';
		
		$tagsArray		= $this->listMediaTags();
		$categoryArray	= $this->listMediaCategory();
		return new ViewModel(array(
			'userObject'		=> $userSession->userSession,
			'createMediaForm'	=> $createMediaForm,
			'message'			=> $message,
			'errorMessage'		=> $errorMessage,
			'categoryArray'		=> $categoryArray,
			'tagsArray'			=> $tagsArray,
		));
    }
	/*******************************
	 *	Action: edit-media	  		
	 *  Module: Manage Media        
	 ******************************/
	
	public function editMediaAction()
    {
		ini_set('max_execution_time', '1000');
		//	Validate Authentication
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
		}
		//	Check whether the media id exist or not
		$id 	= $this->params()->fromRoute('id', 0);
        $mediaid= $id;
		if (!$id) {
            return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'create-media'));
        }
		
		$createMediaForm	= new CreateMediaForm();
		$createMediaForm->get('media_status')->setLabelAttributes(array('class' => 'radio inline'));
		$request 			= $this->getRequest();
		$message			= '';
		$errorMessage		= '';
		$media				= $this->selectMediabyId($id);
		$mediaTags			= $this->selectMediaTagsbyId($id);
		
		$selectedMediaTags	= array();
		if(is_array($mediaTags) && count($mediaTags) > 0) {
			foreach($mediaTags as $pkey => $pvalue) {
				$selectedMediaTags[]	= $pvalue['tag_id'];
			}
		}
		
		if(isset($media['_id'])) {
			$createMediaForm->get('_id')->setAttribute('value', $media['_id']);
			$createMediaForm->get('media_title')->setAttribute('value', $media['media_title']);
			//$createMediaForm->get('media_title_lower')->setAttribute('value', $media['media_title_lower']);
			$createMediaForm->get('media_url')->setAttribute('value', $media['media_url']);
			$createMediaForm->get('media_category')->setAttribute('value', $media['media_category']);
			$createMediaForm->get('media_description')->setAttribute('value', $media['media_description']);
			$createMediaForm->get('media_tags')->setAttribute('value', $selectedMediaTags);
			$createMediaForm->get('media_approved')->setAttribute('value', $media['media_approved']);
			$createMediaForm->get('media_status')->setAttribute('value', $media['media_status']);
			$createMediaForm->get('approvedDate')->setAttribute('value', $media['date_approved']);
		}
		$createMediaForm->get('submit')->setAttribute('value', 'Save Changes');
		//	Media creation goes here
		if ($request->isPost()) {
			$formPostData	= $request->getPost();
			$access_pages	= '';
			
			//Check whether the Media already exists in the database or not
			$results	= $this->checkMediaName($formPostData, 2);
			
			if($results == 1) {
				$message		= 'Video Title already exist.';
				$errorMessage	= '1';
			} else {
				$formPostData['dateapproved']	= (isset($formPostData['media_approved']) && $formPostData['media_approved'] == 1) ? date('m/d/Y H:i:s') : '00/00/0000 00:00:00';
				$formPostData['approveduser']	= (isset($formPostData['media_approved']) && $formPostData['media_approved'] == 1) ? ADMIN_USER_ID : '';
				
				
				$youtubeUrl	= parse_url($formPostData['media_url'], PHP_URL_QUERY);
				parse_str($youtubeUrl, $params);
			    $v = $params['v'];
				$feedURL = 'http://gdata.youtube.com/feeds/api/videos/' . $v;
				$entry = @\simplexml_load_file($feedURL);
				if($entry === false) {
					$time	= '0';
				} else {
					$video = $this->parseVideoEntry($entry);
					$time	= sprintf("%0.2f", $video->length/60);
				}
				$formPostData['video-length']	= $time;
				if($time <= 10) {
					$results	= $this->updateMedia($formPostData);
					$this->deleteMediaTags($mediaid);
					
					if($mediaid != '' && isset($formPostData['media_tags']) && is_array($formPostData['media_tags']) && count($formPostData['media_tags']) > 0) {
						$this->saveMediaTags($formPostData['media_tags'], $mediaid);
					}
					if($results) {
						return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'list-media', 'id' => 1));
					}
				} else {
					$message		= 'Video Length is exceeded the Max. duration(10 mins)';
					$errorMessage	= '1';
				}
			}
		}
		
		$id = (int) $this->params()->fromRoute('id', 0);
		if(trim($id) == 1)
			$message	= 'Video added successfully.';
		else if($message == '')
			$message	= '';
		
		$tagsArray		= $this->listMediaTags();
		$categoryArray	= $this->listMediaCategory();
		return new ViewModel(array(
			'userObject'		=> $userSession->userSession,
			'createMediaForm'	=> $createMediaForm,
			'message'			=> $message,
			'errorMessage'		=> $errorMessage,
			'categoryArray'		=> $categoryArray,
			'tagsArray'			=> $tagsArray,
			'mediaid'			=> $mediaid,
		));
    }
	/*******************************
	 *	Action: list-media		    
	 *  Module: Manage Media        
	 ******************************/
	
	public function listMediaAction()
	{
		//	Validate Authentication
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
		}
		//	Destroy listing Session Vars
		$listingSession = new Container('listing');
		$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}
		//	For Filter form
		$filterForm	= new FilterForm();
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			$filterForm->setData($request->getPost());
			$formData	= $request->getPost();
			//	Status
			if(isset($formData['selectStatus']) && $formData['selectStatus'] != 2)
				$listingSession->status	= $formData['selectStatus'];
			else
				$listingSession->status	= '';
			//	Keyword
			if(isset($formData['keyword']) && $formData['keyword'] != '')
				$listingSession->keyword	= $formData['keyword'];
			else
				$listingSession->keyword	= '';
			//	Approval
			if(isset($formData['approvalStatus']) && $formData['approvalStatus'] != '2')
				$listingSession->approval	= $formData['approvalStatus'];
			else
				$listingSession->approval	= '';
			//category
			if(isset($formData['categoryFilter']) && $formData['categoryFilter'] != '0')
				$listingSession->category	= $formData['categoryFilter'];
			else
				$listingSession->category	= '';
			//	Field
			if(isset($formData['selectOption']) && $formData['selectOption'] != '')
				$listingSession->field	= $formData['selectOption'];
			else
				$listingSession->field	= '';
		}
		
		$message	= '';
		$id = (int) $this->params()->fromRoute('id', 0);
		if(trim($id) == 1)
			$message	= 'Video updated successfully.';
		else if(trim($id) == 2)
			$message	= 'Video added successfully.';
		else if($message == '')
			$message	= '';
		
		$categoryArray	= $this->listMediaCategory();
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'filterForm'	=> $filterForm,
			'message'		=> $message,
			'categoryArray'	=> $categoryArray,
		));
	}
	/*******************************
	 *	Action: view-media	        
	 *  Module: To list media	    
	 *	Note:	AJAX call with view 
	 ******************************/
	
	public function viewMediaAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		
		$matches	= $this->getEvent()->getRouteMatch();
		$page		= $matches->getParam('id', 1);
		$sortBy		= $matches->getParam('sortBy', '');
		$sortType	= $matches->getParam('sortType', '');
		$perPage	= $matches->getParam('perPage', '');
		
		//	Session for listing
		$listingSession = new Container('listing');
		$columnFlag	= 0;
		if($sortBy != '') {
			if($listingSession->sortBy == $sortBy)
				$columnFlag	= 1;
			$listingSession->sortBy	= $sortBy;
		} else if($listingSession->offsetExists('sortBy')) {
			$sortBy	= $listingSession->sortBy;
		}
		if($sortType != '') {
			if($listingSession->sortType == $sortType && $columnFlag == 1)
				$listingSession->sortType	= ($sortType == 1) ? 0 : 1;
			else
				$listingSession->sortType	= $sortType;
		} else if($listingSession->offsetExists('sortType')) {
			$sortType	= $listingSession->sortType;
		}
		if($perPage != '') {
			$listingSession->perPage	= $perPage;
		} else if($listingSession->offsetExists('perPage')) {
			$perPage	= $listingSession->perPage;
		} else {
			$perPage	= 10;
		}
		
		$message		= '';
		$recordsArray	= $this->listMedia($page, $perPage);
		$totalRecords	= $recordsArray->count();
		$resultArray	= array();
		
		$tagMediaIdArray	= array();
		while($recordsArray->hasNext())
		{
			$tempresultArray	= $recordsArray->getNext();
			$resultArray[]		= $tempresultArray;
			$tagMediaIdArray[]	= (string)$tempresultArray['_id'];
		}
		$usersArray		= $this->listUsers();
		$mediaTagsArray	= $this->listMediaTagsById($tagMediaIdArray);
		$categoryArray	= $this->listMediaCategory();
		$tagsArray		= $this->listMediaTags();
		
		$result->setVariables(array('records'		=> $resultArray,
									'message'		=> $message,
									'categoryArray'	=> $categoryArray,
									'usersArray'	=> $usersArray,
									'mediaTagsArray'=> $mediaTagsArray,
									'tagsArray'		=> $tagsArray,
									'page'			=> $page,
									'sortBy'		=> $sortBy,
									'perPage'		=> $perPage,
									'totalRecords'	=> $totalRecords,
									'controller'	=> $this->params('controller')));
		return $result;
    }
	/********************************
	 *	Action: delete-media	     
	 *  Module: To delete video		 
	 *	Note:	AJAX call with view  
	 *******************************/
	
	public function deleteMediaAction()
    {
		$id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('cms', array('controller' => 'media', 'action' => 'list-media'));
        }
        $this->deleteVideo($id);
        return $this->getResponse();
    }
	/********************************
	 *	Action: fetch-video-length	 
	 *  Module: To fetch video length
	 *******************************/
	
	public function fetchVideoLengthAction()
    {
		$v	= 'qhqtTvLQtVA';
	    $feedURL = 'http://gdata.youtube.com/feeds/api/videos/' . $v;
		
	    $entry = @\simplexml_load_file($feedURL);
		if($entry === false) {
			echo '0';
			die();
		}
		$video = $this->parseVideoEntry($entry);
		echo sprintf("%0.2f", $video->length/60);
        return $this->getResponse();
    }
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
