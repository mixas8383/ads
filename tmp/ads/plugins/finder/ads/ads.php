<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.Ads
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');
jimport('joomla.application.categories');

// Load the base adapter.
require_once JPATH_SITE  . '/components/com_adsmanager/lib/core.php';
require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';
require_once JPATH_ADMINISTRATOR . '/components/com_adsmanager/models/content.php';
require_once JPATH_SITE  . '/components/com_adsmanager/lib/route.php';

/**
 * Finder adapter for Joomla AdsManager.
 *
 * @package     Joomla.Plugin
 * @subpackage  Finder.Ads
 * @since       2.5
 */
class plgFinderAds extends FinderIndexerAdapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'Ads';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $extension = 'com_adsmanager';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $layout = 'ads';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type_title = 'Ads';

	/**
	 * The table name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $table = '#__adsmanager_ads';

	/**
	 * The field the published state is stored in.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $state_field = 'published';

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   2.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Method to remove the link information for items that have been deleted.
	 *
	 * @param   string  $context  The context of the action being performed.
	 * @param   JTable  $table    A JTable object containing the record to be deleted
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderDelete($context, $table)
	{
		if ($context == 'com_adsmanager.adsmanager')
		{
			$id = $table->id;
		}
		elseif ($context == 'com_finder.index')
		{
			$id = $table->link_id;
		}
		else
		{
			return true;
		}
		// Remove the items.
		return $this->remove($id);
	}

	/**
	 * Method to determine if the access level of an item changed.
	 *
	 * @param   string   $context  The context of the content passed to the plugin.
	 * @param   JTable   $row      A JTable object
	 * @param   boolean  $isNew    If the content has just been created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		// We only want to handle categories here
		if ($context == 'com_adsmanager.adsmanager')
		{
			// Check if the access levels are different
			if (!$isNew && $this->old_access != $row->access)
			{
				// Process the change.
				$this->itemAccessChange($row);
			}

			// Reindex the item
			$this->reindex($row->id);
		}
		return true;
	}

	/**
	 * Method to reindex the link information for an item that has been saved.
	 * This event is fired before the data is actually saved so we are going
	 * to queue the item to be indexed later.
	 *
	 * @param   string   $context  The context of the content passed to the plugin.
	 * @param   JTable   $row     A JTable object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderBeforeSave($context, $row, $isNew)
	{
		// We only want to handle categories here
		if ($context == 'com_adsmanager.adsmanager')
		{
			// Query the database for the old access level if the item isn't new
			if (!$isNew)
			{
				$this->checkItemAccess($row);
			}
		}

		return true;
	}

	/**
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   string   $context  The context for the content passed to the plugin.
	 * @param   array    $pks      A list of primary key ids of the content that has changed state.
	 * @param   integer  $value    The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// We only want to handle categories here
		if ($context == 'com_adsmanager.adsmanager')
		{
			$this->itemStateChange($pks, $value);
		}

		// Handle when the plugin is disabled
		if ($context == 'com_plugins.plugin' && $value === 0)
		{
			$this->pluginDisable($pks);
		}
	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult  $item    The item to index as an FinderIndexerResult object.
	 * @param   string               $format  The item format
	 *
	 * @return  void
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function index(FinderIndexerResult $item, $format = 'html')
	{
		// Check if the extension is enabled
		if (JComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		// Initialize the item parameters.
		$registry = new JRegistry;
		$registry->loadString($item->params);
		$item->params = $registry;

		$registry = new JRegistry;
		$registry->loadString($item->metadata);
		$item->metadata = $registry;

		// Build the necessary route and path information.
		$item->url = $this->getURL($item->id, $this->extension, $this->layout);
		
		if ($item->slug instanceof JCategoryNode)
			$ads_id = $item->slug->id;
		else
			$ads_id = (int) $item->slug;
			
		$adsmodelcontent = new AdsmanagerModelContent();
		
		$ads_catid = $adsmodelcontent->getContent($ads_id, true)->catid;
		
		
		$url = "index.php?option=com_adsmanager&view=details&id=" . $ads_id . "&catid=" . $ads_catid;
		$xhtml = true;
		$ssl = null;
		
		$item->route = TRoute::_($url);	
		//$item->route = AdsmanagerHelperRoute::getAdsRoute($item->slug, $item->catslug);
		//$item->route = AdsmanagerHelperRoute::_("index.php?option=com_adsmanager&view=details&id=".$row->id."&catid=".$row->catid);
		$item->path = FinderIndexerHelper::getContentPath($item->route);

		/*
		 * Add the meta-data processing instructions based on the newsfeeds
		 * configuration parameters.
		 */
		// Add the meta-author.
		$item->metaauthor = $item->metadata->get('author');

		// Handle the link to the meta-data.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'link');

		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'created_by_alias');

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Ads');

		// Add the category taxonomy data.
		$item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);

		// Add the language taxonomy data.
		$item->addTaxonomy('Language', $item->language);

		// Get content extras.
		FinderIndexerHelper::getContentExtras($item);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	protected function setup()
	{
		// Load com_content route helper as it is the fallback for routing in the indexer in this instance.
		//include_once JPATH_SITE . '/components/com_adsmanager/helpers/route.php';

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $sql  A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getListQuery($sql = null)
	{
		$db = JFactory::getDbo();
		// Check if we can use the supplied SQL query.
		$sql = $sql instanceof JDatabaseQuery ? $sql : $db->getQuery(true);
/*
		$sql->select('a.id, a.title, a.alias, a.introtext AS summary, a.fulltext AS body');
		$sql->select('a.state, a.catid, a.created AS start_date, a.created_by');
		$sql->select('a.created_by_alias, a.modified, a.modified_by, a.attribs AS params');
		$sql->select('a.metakey, a.metadesc, a.metadata, a.language, a.access, a.version, a.ordering');
		$sql->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date');
		$sql->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');
*/	

		$sql->select("a.id, a.ad_headline AS title, '*' as language, LOWER(REPLACE(a.ad_headline,' ','-')) AS alias, a.ad_text AS summary, a.ad_text AS body");
		$sql->select('a.userid AS created_by, a.userid AS modified_by, 1 AS access');
		$sql->select('a.metadata_keywords AS metakey, a.metadata_description AS metadesc');
		$sql->select('a.date_created AS start_date, 1 AS state, c.id AS catid, c.name AS category, c.published AS cat_state, c.published AS cat_access');

		// Handle the alias CASE WHEN portion of the query
		$case_when_item_alias = ' CASE WHEN ';
		$case_when_item_alias .= $sql->charLength("LOWER(REPLACE(a.ad_headline,' ','-'))");
		$case_when_item_alias .= ' THEN ';
		$a_id = $sql->castAsChar('a.id');
		$case_when_item_alias .= $sql->concatenate(array($a_id, "LOWER(REPLACE(a.ad_headline,' ','-'))"), ':');
		$case_when_item_alias .= ' ELSE ';
		$case_when_item_alias .= $a_id.' END as slug';
		$sql->select($case_when_item_alias);
		
		$case_when_category_alias = ' CASE WHEN ';
		$case_when_category_alias .= $sql->charLength("LOWER(REPLACE(c.name,' ','-'))");
		$case_when_category_alias .= ' THEN ';
		$c_id = $sql->castAsChar('c.id');
		$case_when_category_alias .= $sql->concatenate(array($c_id, "LOWER(REPLACE(c.name,' ','-'))"), ':');
		$case_when_category_alias .= ' ELSE ';
		$case_when_category_alias .= $c_id.' END as catslug';
		$sql->select($case_when_category_alias);
		
		$sql->select('u.name AS author');
		$sql->from('#__adsmanager_ads AS a');
		$sql->join('LEFT', '#__adsmanager_adcat AS ac ON ac.adid = a.id');
		$sql->join('LEFT', '#__adsmanager_categories AS c ON ac.catid = c.id');
		$sql->join('LEFT', '#__users AS u ON u.id = a.userid');
		$sql->where($db->quoteName('a.id') . ' > 1');

		return $sql;
	}

}
