<?php
/**
 * @package		PaidSystem
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 *  @license GNU General Public License version 3, or later
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

require_once(JPATH_ROOT."/components/com_adsmanager/lib/core.php");

// no direct access
defined('_JEXEC') or die('Restricted access');

class AdsManagerReservitPlugin {

	var $_db;
	
	function getListDisplay($content,$field)
	{
		return AdsManagerImagePlugin::getDetailsDisplay($content,$field);
	}

	function getDetailsDisplay($content,$field)
	{
		$fieldid = $field->fieldid;
		
		$fieldname = $field->name;
		$value = @$content->$fieldname;
		
		$return ="FORMULAIRE $value";
		
		
           
		return $return;
	}

	function getFormDisplay($content,$field,$default=null)
	{
		$fieldid = $field->fieldid;
		
		$fieldname = $field->name;
		$value = @$content->$fieldname;
		
       	$return = '<div id="'.$field->name.'"><input type="text" size="30" mosReq="'.$field->required.'" mosLabel="'.htmlspecialchars($field->title).'" name="reservit_'.$fieldid.'" value="'.$value.'" /></div>';
		return $return;
	}

	function onFormSave(&$content,$field)
	{
		$url = JRequest::getVar("reservit_".$field->fieldid,0);
		return $url;
        }

	function onDelete($directory,$contentid = -1)
	{
	}

	function getEditFieldJavaScriptDisable()
	{
		return "";
	}
	
	function getEditFieldJavaScriptActive() {
		return "";
	}

	function getEditFieldOptions($fieldid) {
		return "";
	}

	function saveFieldOptions($field)
	{
		return;
	}
	
	function getFieldName()
	{
		return "ReservIt.com";
	}
	
    	function install(){}
    
	function uninstall(){}
	
	function __construct()
	{
		$db	= JFactory::getDBO();
		$this->_db = $db;
	}
}

$plugins["reservit"] = new AdsManagerReservitPlugin();
?>
