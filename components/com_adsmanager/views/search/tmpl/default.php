<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
?>
<div class="container-fluid">
    <?php $link = TRoute::_("index.php?option=com_adsmanager&view=result"); ?>
    <form action="<?php echo $link; ?>" class="form-horizontal" id="advancedsearch-form" method="post">
        <fieldset>
        	<legend>
            <?php echo JText::_('ADSMANAGER_ADVANCED_SEARCH'); ?>
        	</legend>
            <div class="row-fluid">
            <?php if ($this->search_by_cat == 1){ ?>
            <div class="control-group">
                <label class="control-label" for="catid"></label>
                <div class="controls">
					<?php
                        switch($this->conf->single_category_selection_type) {
                            default:
                            case 'normal':
                                JHTMLAdsmanagerCategory::displayNormalCategories("search_catid",$this->cats,$this->catid);break;
                            case 'color':
                                JHTMLAdsmanagerCategory::displayColorCategories("search_catid",$this->cats,$this->catid);break;
                            case 'combobox':
                                JHTMLAdsmanagerCategory::displayComboboxCategories("search_catid",$this->cats,$this->catid);break;
                            case 'cascade':
                                $separator = "<br/>";
                                JHTMLAdsmanagerCategory::displaySplitCategories("search_catid",$this->cats,$this->catid,array('separator'=>$separator));break;
                        }
					?>
                </div>
            </div>
            <?php } ?>
            <div class="row-fluid">
			<?php 
                foreach($this->simple_fields as $fsearch) {
                    echo "<div id='searchfield_$fsearch->fieldid' class=\"span12\">";
                    echo "<div class=\"control-group\">";
                    echo "<label class=\"control-label\" for=\"{$fsearch->name}\">".JText::_($fsearch->title)."</label>";
                    echo "<div class=\"controls\">";
                    $this->field->showFieldSearch($fsearch,0,$this->defaultvalues,true);
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
			}?>			
</div>
            <?php if(!empty($this->advanced_fields)){ ?>
            <div class="row-fluid">
                <?php 
                foreach($this->advanced_fields as $fsearch) {
                    echo "<div id='searchfield_$fsearch->fieldid' class=\"span12\">";
                    echo "<div class=\"control-group\">";
                    echo "<label class=\"control-label\" for=\"{$fsearch->name}\">".JText::_($fsearch->title)."</label>";
                    echo "<div class=\"controls\">";
                    $this->field->showFieldSearch($fsearch,0,$this->defaultvalues,true);
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                } ?>
</div>
            <?php } ?>
            <input type="submit" class="btn btn-primary" value="<?php echo JText::_('ADSMANAGER_SEARCH_TITLE'); ?>"/>
            <script type="text/javascript">
                function updateModFields() {
                    var form = document.advsearchmodform;
                    catid = jQ('#advancedsearch-form #search_catid').val();
                    <?php
                    $fields = array_merge($this->simple_fields,$this->advanced_fields);
                    foreach($fields as $field)
                    { 	
                        if (strpos($field->catsid, ",-1,") === false)
                        {
                        ?>
                        var field_condition = "<?php echo $field->catsid;?>";
                        var test = field_condition.indexOf( ","+catid+",", 0 );
                        var divfield = document.getElementById('searchfield_<?php echo $field->fieldid;?>');
                        if (test != -1) {
                            jQ('#searchfield_<?php echo $field->fieldid;?>').show();
			}
			else {
                            jQ('#searchfield_<?php echo $field->fieldid;?>').hide();
		} 
                    <?php
	}
			}
                    ?>
		}
                function checkdependency(child,parentname,parentvalue) {
                    //Simple checkbox
                    if (jQ('input[name="'+parentname+'"]').is(':checkbox')) {
                        //alert("test");
                        if (jQ('input[name="'+parentname+'"]').attr('checked')) {
                            jQ('#advancedsearch-form #'+child).show();
                            jQ('#advancedsearch-form #searchfield_'+child).show();
                        }
                        else {
                            jQ('#advancedsearch-form #'+child).hide();
                            jQ('#advancedsearch-form #searchfield_'+child).hide();

                            //cleanup child field 
                            if (jQ('#advancedsearch-form #'+child).is(':checkbox') || jQ('#advancedsearch-form #'+child).is(':radio')) {
                                jQ('#advancedsearch-form #'+child).attr('checked', false);
                            }
                            else {
                                jQ('#advancedsearch-form #'+child).val('');
                            }
                        } 
                    }
                    //If checkboxes or radio buttons, special treatment
                    else if (jQ('input[name="'+parentname+'"]').is(':radio')  || jQ('input[name="'+parentname+'[]"]').is(':checkbox')) {
                        var find = false;
                        var allVals = [];
                        jQ("input:checked").each(function() {
                            if (jQ(this).val() == parentvalue) {	
                                jQ('#advancedsearch-form #'+child).show();
                                jQ('#advancedsearch-form #searchfield_'+child).show();
                                find = true;
                            }
                        });

                        if (find == false) {
                            jQ('#advancedsearch-form #'+child).hide();
                            jQ('#advancedsearch-form #searchfield_'+child).hide();

                            //cleanup child field 
                            if (jQ('#advancedsearch-form #'+child).is(':checkbox') || jQ('#advancedsearch-form #'+child).is(':radio')) {
                                jQ('#advancedsearch-form #'+child).attr('checked', false);
                            }
                            else {
                                jQ('#advancedsearch-form #'+child).val('');
                            }
                        }

                    }
                    //simple text
                    else if (jQ('#advancedsearch-form #'+parentname).val() == parentvalue) {
                        jQ('#advancedsearch-form #'+child).show();
                        jQ('#advancedsearch-form #searchfield_'+child).show();
                    } 
                    else {
                        jQ('#advancedsearch-form #'+child).hide();
                        jQ('#advancedsearch-form #searchfield_'+child).hide();

                        //cleanup child field 
                        if (jQ('#advancedsearch-form #'+child).is(':checkbox') || jQ('#advancedsearch-form #'+child).is(':radio')) {
                            jQ('#advancedsearch-form #'+child).attr('checked', false);
                        }
                        else {
                            jQ('#advancedsearch-form #'+child).val('');
                        }
                    }
                }
                function dependency(child,parentname,parentvalue) {
                    //if checkboxes
                    jQ('input[name="'+parentname+'[]"]').change(function() {
                        checkdependency(child,parentname,parentvalue);
                    });
                    //if buttons radio
                    jQ('input[name="'+parentname+'"]').change(function() {
                        checkdependency(child,parentname,parentvalue);
                    });
                    jQ('#'+parentname).click(function() {
                        checkdependency(child,parentname,parentvalue);
                    });
                    checkdependency(child,parentname,parentvalue);
                }
                jQ(document).ready(function() {
                    updateModFields();

                    jQ('#advancedsearch-form #search_catid').change(function(){
                            updateModFields();
                    });
                    
                    <?php foreach($this->simple_fields as $field) { 
                        if (@$field->options->is_conditional_field == 1) { ?>
                        dependency('<?php echo $field->name?>',
                                   '<?php echo $field->options->conditional_parent_name?>',
                                   '<?php echo $field->options->conditional_parent_value?>');
                        <?php } 
                    }?>
                    
                    <?php if(!empty($this->advanced_fields)){ ?>
                    <?php foreach($this->advanced_fields as $field) { 
                        if (@$field->options->is_conditional_field == 1) { ?>
                        dependency('<?php echo $field->name?>',
                                   '<?php echo $field->options->conditional_parent_name?>',
                                   '<?php echo $field->options->conditional_parent_value?>');
                        <?php } 
                    }?>
                    <?php } ?>
                        
                    var updateCounter = function(id) {
                        return function(data, textStatus) {
                            jQ("#"+id).next().html("("+data.count+")");
                        };
                    };
});
            </script>
            <input type="hidden" value="1" name="new_search" />
            <?php if ($this->rootid != 0) {?>
            <input type="hidden" value="<?php echo $this->rootid?>" name="rootid"/>
            <?php } ?>
            </div>
        </fieldset>
    </form>
</div>