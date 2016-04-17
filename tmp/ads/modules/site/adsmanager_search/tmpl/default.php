<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

if($type == "horizontal") {
    $class = 'floatleft';
} else {
    $class = 'span12';
}
?>
<?php $link = TRoute::_("index.php?option=com_adsmanager&view=result"); ?>
<form action="<?php echo $link; ?>" method="post">
<div class="row-fluid adsmanager_search_module<?php echo $moduleclass_sfx; ?>">
<?php if ($search_by_text == 1){ ?>
            <div class="<?php echo $class; ?>">
                <div class="control-group">
                    <label class="control-label" for="tsearch"></label>
                    <div class="controls">
                        <input type="text" name="tsearch" placeholder="<?php echo JText::_('ADSMANAGER_LIST_SEARCH'); ?>" value="<?php echo $text_search; ?>" />
                    </div>
                </div>
            </div>
<?php }?>
        <?php if ($search_by_cat == 1) { ?>
            <div class="<?php echo $class; ?>">
                <div class="control-group">
                    <?php if($display_cat_label == 1): ?>
                        <label class="control-label" for="catid"><?php echo JText::_('ADSMANAGER_SELECT_CATEGORY_LABEL') ?></label>
                    <?php endif; ?>
                    <div class="controls">
	<?php 
	switch(@$conf->single_category_selection_type) {
		default:
		case 'normal':
			JHTMLAdsmanagerCategory::displayNormalCategories("catid",$cats,$catid,array("allow_empty"=>true));break;
		case 'color':
			JHTMLAdsmanagerCategory::displayColorCategories("catid",$cats,$catid,array("allow_empty"=>true));break;
		case 'combobox':
			JHTMLAdsmanagerCategory::displayComboboxCategories("catid",$cats,$catid,array("allow_empty"=>true));break;
			break;
		case 'cascade':
			if ($type == "horizontal") 
				$separator = "";
			else
				$separator = "<br/>";
			JHTMLAdsmanagerCategory::displaySplitCategories("catid",$cats,$catid,array('separator'=>$separator));break;
	}
	?>
					</div>
				</div>	
			</div>
            <?php
}
        ?>      
<?php
if (isset($searchfields)) {
	foreach($searchfields as $fsearch) {
		if (($catid == 0)||(strpos($fsearch->catsid, ",$catid,") !== false)||(strpos($fsearch->catsid, ",-1,") !== false))
		{
			$currentvalue = JRequest::getVar($fsearch->name, "" );
			?>
			<div class="<?php echo $class; ?> mod_adsmanager_search_field">
                <div class="control-group">
                    <label class="control-label">
                    <?php 
                    $title = $field->showFieldTitle($catid,$fsearch);
					echo htmlspecialchars($title)."&nbsp;";
					?></label>
                    <div class="controls">
					<?php 
					$field->showFieldSearch($fsearch,$catid,$defaultvalues);
					?>
					</div>
				</div>
			</div>
			<?php 
		}
	}
}
?>

<?php if ($rootid != 0) {?>
<input type="hidden" value="<?php echo $rootid ?>" name="rootid" />
<?php } ?>
<input type="hidden" value="1" name="new_search" />
<div class="<?php echo $class; ?>">
	<div class="control-group">
    	<label class="control-label">
        </label>
        <div class="controls">
			<input type="submit" class="btn btn-primary" value="<?php echo JText::_('ADSMANAGER_SEARCH_BUTTON'); ?>"/>
		</div>
	</div>
</div>
<?php if ($advanced_search == 1)
{
?>
<div class="<?php echo $class; ?>">
	<div class="control-group">
    	<label class="control-label">
        </label>
        <div class="controls">
			<a href="<?php echo TRoute::_("index.php?option=com_adsmanager&view=search&catid=$catid");?>"><?php echo JText::_('ADSMANAGER_ADVANCED_SEARCH'); ?></a>
       	</div>
	</div>
</div>
<?php } ?>
</div>
</form>
