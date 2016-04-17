<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
?>
<script type="text/javascript">
function tableOrdering( order, dir, task )
{
        var form = document.adminForm;
 
        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        document.adminForm.submit( task );
}
</script>
<?php
$conf= $this->conf;
?>
<div class="row-fluid">
    <fieldset>
        <legend>
            <?php echo JText::_($this->list_name); ?>
        </legend>
    </fieldset>
</div>
<script type="text/JavaScript">
<!--
function jumpmenu(target,obj){
  eval(target+".location='"+obj.options[obj.selectedIndex].value+"'");	
  obj.options[obj.selectedIndex].innerHTML="<?php echo JText::_('ADSMANAGER_WAIT');?>";			
}		

jQ(function() {
	jQ('#order').change(function() {
		order = jQ(this).val();
		orderdir = jQ(":selected",this).attr('dir');
		var form= document.createElement('form');
        form.method= 'post';
        form.action= '<?php echo TRoute::_("index.php?option=com_adsmanager&view=result") ?>';  
        var input= document.createElement('input');
        input.type= 'hidden';
        input.name= "order";
        input.value= order;
        form.appendChild(input);
        var input2= document.createElement('input');
        input2.type= 'hidden';
        input2.name= "orderdir";
        input2.value= orderdir;
        form.appendChild(input2);
    	document.body.appendChild(form);
    	form.submit();
	});
});

		
//-->
</script>
<?php if (($conf->display_list_sort == 1)||($conf->display_list_search == 1)) { ?>
<div class="row-fluid">
    <div class="span12">
	<?php if ($conf->display_list_search == 1) { ?>
            <div class="span6">
			<a href="<?php echo TRoute::_("index.php?option=com_adsmanager&view=search&catid=".$this->catid);?>"><?php echo JText::_('ADSMANAGER_ADVANCED_SEARCH'); ?></a>
		</div>
	<?php } ?>
	<?php if ($conf->display_list_sort == 1) { ?>
		<?php if (isset($this->orders)) { ?>
                <div class="span6 text-right">
		<?php echo JText::_('ADSMANAGER_ORDER_BY_TEXT'); ?>
		<select name="order" size="1" id="order">
				<option value="0" dir="DESC" <?php if ($this->order == "0") { echo "selected='selected'"; } ?>><?php echo JText::_('ADSMANAGER_DATE'); ?></option>
			   <?php foreach($this->orders as $o)
			   {
	               ?>
				<option value="<?php echo $o->fieldid ?>" dir="DESC" <?php if (($this->orderdir == "DESC") && ($this->order == $o->fieldid)) { echo "selected='selected'"; } ?>><?php echo sprintf(JText::_('ADSMANAGER_ORDER_BY_DESC'),JText::_($o->title))?></option>
				<option value="<?php echo $o->fieldid ?>" dir="ASC" <?php if (($this->orderdir == "ASC") && ($this->order == $o->fieldid)) { echo "selected='selected'"; } ?>><?php echo sprintf(JText::_('ADSMANAGER_ORDER_BY_ASC'),JText::_($o->title))?></option>
				<?php
			   }
			 ?>
		</select>	
                </div>
		<?php } ?>
	<?php } ?>			  
</div>
</div>
<?php } ?>
<?php $this->general->showGeneralLink() ?>
<?php
if ($this->pagination->total == 0 ) 
{
	echo JText::_('ADSMANAGER_NOENTRIES'); 
}
else
{
	?>
	<?php 
        if(@$conf->display_map_list == 1){
            echo TTools::loadModule('mod_adsmanager_adsmap', 'AdsMap');
        }
    ?>
	<br/><br/>
	<form name="adminForm" id="adminForm" method="post" action="<?php echo $this->requestURL; ?>" >
	<input type="hidden" id="mode" name="mode" value="<?php echo $this->mode; ?>"/>
	</form>
	<?php
        if ($this->mode == 0) { 
            echo $this->loadTemplate('list');
        } elseif($this->mode == 1) { 
            echo $this->loadTemplate('expand');
        } else {
            echo $this->loadTemplate('grid');
        }
    ?>
	<div class="row-fluid">
        <div class="span12">
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span6"><?php echo $this->pagination->getResultsCounter(); ?></div>
	<?php if ($this->conf->display_expand == 2) { ?>
	<script type="text/javascript">
		function changeMode(mode)
		{
			element = document.getElementById("mode");
			element.value = mode;
			form = document.getElementById("adminForm");
			form.submit();
		}
		</script>
            <div class="span6 text-right">
		<?php 
		/* Display SubTitle */
			echo '<a href="javascript:changeMode(0)">'.JText::_('ADSMANAGER_MODE_TEXT')." ".JText::_('ADSMANAGER_SHORT_TEXT').'</a>';
		    echo " / ";
		    echo '<a href="javascript:changeMode(1)">'.JText::_('ADSMANAGER_EXPAND_TEXT').'</a>';
            echo " / ";
		    echo '<a href="javascript:changeMode(3)">'.JText::_('ADSMANAGER_GRID_TEXT').'</a>';
        ?>
		</div>
        <?php } ?>
    </div>
<?php 
} $this->general->endTemplate();