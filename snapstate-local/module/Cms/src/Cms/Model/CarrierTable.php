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

//	Cache
use Zend\Cache\Storage\StorageInterface;

class CarrierTable extends AbstractTableGateway
{
    protected $table = 'carrier';
	protected $cache;
	
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Carrier());
        $this->initialize();
    }
	
	public function setCache(StorageInterface $cache)
    {
        $this->cache = $cache;
    }
	
	public function fetchCarriers()
    {
    	//if( ($resultSet = $this->cache->getItem('samplecache')) == FALSE) {
			//$rowset = $this->select(array('carrier_status' => 1));
			$rowset = $this->select();
			//$this->cache->setItem('samplecache',  $rowset);
		//}
		return $rowset;
    }
	
	public function updateCarrier($data, $carrier_id) {
		$this->update($data, array('carrier_id' => $carrier_id));
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
	
    public function getCarrier($id)
    {
        $id  = (int) $id;
        $rowset = $this->select(array('carrier_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
	
	public function getCarriersList()
	{
		//	Session for Carrier accessibility
		$whereClause	= '';
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();
		$whereClause	.= '';
		
		$userSession = new Container('user');
		if($userSession->offsetExists('carrier')) {
			$carrierFlag	= $userSession->carrier;
		}
		$listingSession = new Container('listing');
		if($listingSession->offsetExists('status') && $listingSession->status != '') {
			$whereClause	.= ' AND carrier_status = ' . $listingSession->status;
		}
		if($listingSession->offsetExists('keyword') && $listingSession->keyword != '') {
			$whereClause	.= ' AND carrier_name like "' . addslashes($listingSession->keyword) . '%"';
		}
		$orderClause	= '';
		if($listingSession->offsetExists('sortBy')) {
			$orderClause	.= ' ORDER BY '.$listingSession->sortBy;
		}
		if($listingSession->offsetExists('sortType') && $listingSession->sortType == 1) {
			$orderClause	.= ' DESC';
		}
		
		$sql	= 'SELECT * FROM carrier
					WHERE 1 ' . $whereClause . ' ' . $orderClause;
		
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		$result		= $this->resultSetPrototype->initialize($result);
		$result->buffer();
		$result->next();
		return $result;
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
	
	public function deleteCarrierUsers($id)
	{
		$sql		= 'DELETE FROM users WHERE user_carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
	public function getDefaultLanguage($id)
	{
		$sql		= 'SELECT carrier_language FROM carrier WHERE carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		if(!$result)
			return false;
		else
			return $result;
	}
	
	public function getLanguageVariables($id, $languageId)
	{
		$sql		= 'SELECT page_field, page_value FROM page_values WHERE carrier_id = '.trim($id).' and language_id = '.trim($languageId);
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		if(!$result)
			return false;
		else
			return $result;
	}
	
	public function getCarrierImage($id)
	{
		$sql		= 'SELECT carrier_logo, carrier_banner FROM carrier WHERE carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		if(!$result)
			return false;
		else
			return $result;
	}
	
	public function deleteTerms($id)
	{
		$sql		= 'DELETE FROM terms WHERE carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
	public function deleteLanguages($id)
	{
		$sql		= 'DELETE FROM languages WHERE carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
	public function deleteCategory($id)
	{
		$sql		= 'DELETE T FROM faq_category_values T
						JOIN faq_category on faq_category.category_id = T.category_id
						WHERE faq_category.carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		
		$sql		= 'DELETE FROM faq_category WHERE carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
	public function deletePages($id)
	{
		$sql		= 'DELETE T FROM page_values T
						JOIN pages on pages.page_id = T.page_id
						WHERE pages.carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		
		$sql		= 'DELETE FROM pages WHERE carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
	public function deleteFAQ($id)
	{
		$sql		= 'DELETE T FROM faq_values T
						JOIN faq on faq.faq_id = T.faq_id
						WHERE faq.carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		
		$sql		= 'DELETE FROM faq WHERE carrier_id = '.$id;
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
    public function saveCarrier(Carrier $carrier)
    {
        $data = array(
			'carrier_id'		=> $carrier->carrier_id,
            'carrier_name'		=> $carrier->carrier_name,
			'carrier_status'	=> $carrier->carrier_status,
        );
        $carrier_id = (int)$carrier->carrier_id;
        if ($carrier_id == 0) {
			$this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
			if ($this->getCarrier($carrier_id)) {
                $this->update($data, array('carrier_id' => $carrier_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
	
	public function getLanguages($id)
	{
		$sql		= 'SELECT language_id, language_title, language_code, language_flag FROM languages WHERE carrier_id='.$id.' AND language_status = 1';
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
		if(!$result)
			return false;
		else
			return $result;
	}
	
	public function updateSiteSettings($formData)
	{
		if(isset($formData['usermode']) && $formData['usermode'] != 3) {
			$sql		= 'UPDATE carrier SET 
						carrier_fbappid		= "'.addslashes($formData['carrier_fbappid']).'", 
						carrier_fbkey		= "'.addslashes($formData['carrier_fbkey']).'",
						carrier_fbapp_name	= "'.addslashes($formData['carrier_fbapp_name']).'",
						carrier_fb_page		= "'.addslashes($formData['carrier_fb_page']).'",
						carrier_font		= "'.addslashes($formData['carrier_font']).'", 
						carrier_themecolor	= "'.addslashes($formData['carrier_themecolor']).'", 
						carrier_backgroundcolor	= "'.addslashes($formData['carrier_backgroundcolor']).'", 
						carrier_buttoncolor	= "'.addslashes($formData['carrier_buttoncolor']).'", 
						carrier_buttonhighlightcolor= "'.addslashes($formData['carrier_buttonhighlightcolor']).'",
						carrier_fontcolor	= "'.addslashes($formData['carrier_fontcolor']).'", 
						carrier_timezone	= "'.addslashes($formData['carrier_timezone']).'", 
						carrier_language	= '.$formData['carrier_language'];
		} else {
			$sql		= 'UPDATE carrier SET 
						carrier_font		= "'.addslashes($formData['carrier_font']).'", 
						carrier_themecolor	= "'.addslashes($formData['carrier_themecolor']).'", 
						carrier_backgroundcolor	= "'.addslashes($formData['carrier_backgroundcolor']).'", 
						carrier_buttoncolor	= "'.addslashes($formData['carrier_buttoncolor']).'", 
						carrier_buttonhighlightcolor= "'.addslashes($formData['carrier_buttonhighlightcolor']).'",
						carrier_fontcolor	= "'.addslashes($formData['carrier_fontcolor']).'"';
		}
		// BANNER
		if(trim($formData['carrier_banner']) != '' && (($formData['banner_list'] == 3 && $formData['banner'] != '') || $formData['banner'] == '')) {
			$hiddenLogo	= (trim($formData['hidden_carrier_banner']) != '') ? '##'.trim($formData['hidden_carrier_banner']) : '';
			$sql	.= ',carrier_banner		= "'.$formData['carrier_banner'].$hiddenLogo.'"';
		} else if($formData['banner_list'] == 1) {
			$bannerArray	= explode('##', $formData['banner']);
			$bannerInput	= $bannerArray[1].'##'.$bannerArray[0];
			
			if(isset($bannerArray[2])) {
				$bannerInput	= $bannerInput.'##'.$bannerArray[2];
			}
			$sql	.= ',carrier_banner		= "'.$bannerInput.'"';
		} else if($formData['banner_list'] == 2) {
			$bannerArray	= explode('##', $formData['banner']);
			$bannerInput	= $bannerArray[2].'##'.$bannerArray[0].'##'.$bannerArray[1];
			$sql	.= ',carrier_banner		= "'.$bannerInput.'"';
		}
		// TOP BANNER
		if(trim($formData['carrier_topbanner']) != '') {
			$sql	.= ',carrier_topbanner		= "'.$formData['carrier_topbanner'].'"';
		} else if($formData['topbannerflag'] != '') {
			$sql	.= ',carrier_topbanner		= "'.$formData['topbannerflag'].'"';
		}
		// LOGO
		if(trim($formData['carrier_logo']) != '' && (($formData['logo_list'] == 3 && $formData['logo'] != '') || $formData['logo'] == '')) {
			$hiddenLogo	= (trim($formData['hidden_carrier_logo']) != '') ? '##'.trim($formData['hidden_carrier_logo']) : '';
			$sql	.= ',carrier_logo		= "'.$formData['carrier_logo'].$hiddenLogo.'"';
		} else if($formData['logo_list'] == 1) {
			$logoArray	= explode('##', $formData['logo']);
			$logoInput	= $logoArray[1].'##'.$logoArray[0];
			
			if(isset($logoArray[2])) {
				$logoInput	= $logoInput.'##'.$logoArray[2];
			}
			$sql	.= ',carrier_logo		= "'.$logoInput.'"';
		} else if($formData['logo_list'] == 2) {
			$logoArray	= explode('##', $formData['logo']);
			$logoInput	= $logoArray[2].'##'.$logoArray[0].'##'.$logoArray[1];
			$sql	.= ',carrier_logo		= "'.$logoInput.'"';
		}
		
		// FOR YOU LOGO
		if(trim($formData['carrier_foryou_logo']) != '' && (($formData['foryou_list'] == 3 && $formData['foryou'] != '') || $formData['foryou'] == '')) {
			$hiddenLogo	= (trim($formData['hidden_carrier_foryou']) != '') ? '##'.trim($formData['hidden_carrier_foryou']) : '';
			$sql	.= ',carrier_foryou_logo		= "'.$formData['carrier_foryou_logo'].$hiddenLogo.'"';
		} else if($formData['foryou_list'] == 1) {
			$logoArray	= explode('##', $formData['foryou']);
			$logoInput	= $logoArray[1].'##'.$logoArray[0];
			
			if(isset($logoArray[2])) {
				$logoInput	= $logoInput.'##'.$logoArray[2];
			}
			$sql	.= ',carrier_foryou_logo		= "'.$logoInput.'"';
		} else if($formData['foryou_list'] == 2) {
			$logoArray	= explode('##', $formData['foryou']);
			$logoInput	= $logoArray[2].'##'.$logoArray[0].'##'.$logoArray[1];
			$sql	.= ',carrier_foryou_logo		= "'.$logoInput.'"';
		}
		
		// FOR A FRIEND
		if(trim($formData['carrier_forafriend_logo']) != '' && (($formData['forafriend_list'] == 3 && $formData['forafriend'] != '') || $formData['forafriend'] == '')) {
			$hiddenLogo	= (trim($formData['hidden_carrier_forafriend']) != '') ? '##'.trim($formData['hidden_carrier_forafriend']) : '';
			$sql	.= ',carrier_forafriend_logo		= "'.$formData['carrier_forafriend_logo'].$hiddenLogo.'"';
		} else if($formData['forafriend_list'] == 1) {
			$logoArray	= explode('##', $formData['forafriend']);
			$logoInput	= $logoArray[1].'##'.$logoArray[0];
			
			if(isset($logoArray[2])) {
				$logoInput	= $logoInput.'##'.$logoArray[2];
			}
			$sql	.= ',carrier_forafriend_logo		= "'.$logoInput.'"';
		} else if($formData['forafriend_list'] == 2) {
			$logoArray	= explode('##', $formData['forafriend']);
			$logoInput	= $logoArray[2].'##'.$logoArray[0].'##'.$logoArray[1];
			$sql	.= ',carrier_forafriend_logo		= "'.$logoInput.'"';
		}
		
		// ASK
		if(trim($formData['carrier_ask_logo']) != '' && (($formData['ask_list'] == 3 && $formData['ask'] != '') || $formData['ask'] == '')) {
			$hiddenLogo	= (trim($formData['hidden_carrier_ask']) != '') ? '##'.trim($formData['hidden_carrier_ask']) : '';
			$sql	.= ',carrier_ask_logo		= "'.$formData['carrier_ask_logo'].$hiddenLogo.'"';
		} else if($formData['ask_list'] == 1) {
			$logoArray	= explode('##', $formData['ask']);
			$logoInput	= $logoArray[1].'##'.$logoArray[0];
			
			if(isset($logoArray[2])) {
				$logoInput	= $logoInput.'##'.$logoArray[2];
			}
			$sql	.= ',carrier_ask_logo		= "'.$logoInput.'"';
		} else if($formData['ask_list'] == 2) {
			$logoArray	= explode('##', $formData['ask']);
			$logoInput	= $logoArray[2].'##'.$logoArray[0].'##'.$logoArray[1];
			$sql	.= ',carrier_ask_logo		= "'.$logoInput.'"';
		}
		$sql	.= ' WHERE	carrier_id	= '.$formData['carrier_id'];
		
		/*	echo '<pre>'; print_r($formData); echo '</pre>';
		echo '<br>==>'.__LINE__.'==>'.__FILE__.'==>'.$sql;
		die('==>'.__LINE__.'==>testing==>'.__FILE__.'==>');	*/
		$statement	= $this->adapter->query($sql);
		$result		= $statement->execute();
	}
	
    public function deleteCarrier($id)
    {
        $this->delete(array('carrier_id' => $id));
    }
}