<?php
/**
* Tab to display recently posted classified ads in the AdsManager component in a Community Builder profile
* Author: Thomas PAPIN (support@juloa.com)
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();



class UserGroupTab extends cbTabHandler {
			
  function getDisplayTab($tab,$user,$ui) {

	jimport( 'joomla.access.access' );
	$groups = JAccess::getGroupsByUser($user->id);
	$return = print_r($groups,true);

	return $return;
  }
}
