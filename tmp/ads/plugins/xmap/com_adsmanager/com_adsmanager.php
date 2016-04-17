<?php
/**
 *  @package	PaidSystem
 *  @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 *  @license    GNU General Public License version 3, or later
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

/** Adds support for Adsmanager categories to Xmap */
class xmap_com_adsmanager {
	
	static protected $done = false;

	/*
	* This function is called before a menu item is printed. We use it to set the
	* proper uniqueid for the item and indicate whether the node is expandible or not
	*/
	function prepareMenuItem(&$node,&$params) {
		$link_query = parse_url( $node->link );
		parse_str( html_entity_decode($link_query['query']), $link_vars);
		$catid = JArrayHelper::getValue($link_vars,'catid',0);
		$adid = JArrayHelper::getValue($link_vars,'id',0);
		$task = JArrayHelper::getValue($link_vars,'task','');
		if ( $adid && $catid ) {
			$node->uid = 'com_adsmanagera'.$sobi2Id;
			$node->expandible = false;
		} elseif ( $catid ) {
			$node->uid = 'com_adsmanagerc'.$catid;
			$node->expandible = true;
		} elseif ( $task ) {
			$node->uid = 'com_adsmanagert'.$task;
			$node->expandible = false;
		}
	}
	
	/** Get the content tree for this kind of content */
	function getTree( &$xmap, &$parent, &$params )
	{
		if (self::$done == true) {
			return false;
		}
		self::$done = true;
		
		$include_entries =JArrayHelper::getValue($params,'include_entries',1);
		$include_entries = ( $include_entries == 1
		                    || ( $include_entries == 2 && $xmap->view == 'xml')
				    		|| ( $include_entries == 3 && $xmap->view == 'html')
							||   $xmap->view == 'navigator');
		$params['include_entries'] = $include_entries;
		
		$priority =JArrayHelper::getValue($params,'cat_priority',$parent->priority);
        $changefreq =JArrayHelper::getValue($params,'cat_changefreq',$parent->changefreq);
		if ($priority  == '-1')
		$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['cat_priority'] = $priority;
		$params['cat_changefreq'] = $changefreq;

		$priority =JArrayHelper::getValue($params,'entry_priority',$parent->priority);
                $changefreq =JArrayHelper::getValue($params,'entry_changefreq',$parent->changefreq);
		if ($priority  == '-1')
			$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['entry_priority'] = $priority;
		$params['entry_changefreq'] = $changefreq;
	
		if ( $include_entries ) 
		{	
			$params['limit'] = '';
            		$limit = intval(JArrayHelper::getValue($params,'max_entries',''));

            		if ( $limit > 0 )
				$params['limit'] = ' LIMIT '.$limit;
        	}
		xmap_com_adsmanager::getCategoryTree($xmap, $parent, 0, $params);

		return true;
	}

	function getCategoryTree( &$xmap, &$parent,$catid, &$params)
	{
		$database =JFactory::getDBO();				
		$database->setQuery(" SELECT * FROM #__adsmanager_categories "
						   ." WHERE parent='$catid' and published = 1");
		$cats=$database->loadObjectList();

		$xmap->changeLevel(1);
		if(count($cats))
		{
			foreach($cats as $cat)
			{
				$node 			= 	new stdclass;
				$node->id 		= 	$parent->id;
				$node->uid 		= 	$parent->uid.'c'.$cat->id;
				$node->browserNav   = $parent->browserNav;
				$node->name 	    = stripslashes(JText::_($cat->name));			
				$node->priority 	= $params['cat_priority'];
				$node->changefreq 	= $params['cat_changefreq'];
				$node->link 		= 'index.php?option=com_adsmanager&view=list&catid='.$cat->id.'&Itemid='.$parent->id;
				$node->pid 		    = $cat->id;	
				$node->expandible   = true;		   			
	   			$pid = $cat->id;
	   	
	   			if ($xmap->printNode($node) !== FALSE)
	   			{  
	   				if ($params['include_entries']) { 				
	   					$database->setQuery(" SELECT a.id,a.name,a.ad_headline FROM #__adsmanager_ads AS a "
	   									   ." INNER JOIN #__adsmanager_adcat as adcat ON a.id = adcat.adid "
	   									   ." WHERE a.published = 1 AND adcat.catid = '{$cat->id}' ORDER BY a.id DESC " . $params['limit'] );
		                $ads=$database->loadObjectList();        		        			        	
		        		$xmap->changeLevel(1);    
						if (count($ads))
						{
				        	foreach($ads as $ad)
				        	{        		               
			            		$node             = new stdclass;
			            		$node->id         = $parent->id;
			            		$node->uid        = $parent->uid.'c'.$ad->id;
			            		$node->browserNav = $parent->browserNav;
			            		$node->name       = stripslashes($ad->ad_headline);            
			            		$node->priority   = $params['entry_priority'];
			            		$node->changefreq = $params['entry_changefreq'];
			            		$node->link       = 'index.php?option=com_adsmanager&view=details&id='.$ad->id.'&catid='.$cat->id.'&Itemid='.$parent->id; 
			            		$node->pid        = $ad->id;   
			            		$node->expandible = false;         
			               		$xmap->printNode($node);    
							}           		
						}	
		        		$xmap->changeLevel(-1);
	   				}
	   				
	   				// see children category recursiv...
	   				xmap_com_adsmanager::getCategoryTree( $xmap, $parent,$cat->id,$params);
	   			}		
	    	}
		}
		$xmap->changeLevel(-1);
		return true;
	}
}