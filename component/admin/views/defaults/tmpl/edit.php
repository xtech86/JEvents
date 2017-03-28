<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: edit.php 2768 2011-10-14 08:43:42Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C)  2008-2017 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.filesystem.file' );

if ($this->item->name == "month.calendar_cell" || $this->item->name == "month.calendar_tip" || $this->item->name == "icalevent.edit_page")
{
	$editor =  JEditor::getInstance("none");
}
else
{
	$editor = JFactory::getConfig()->get('editor');
	$editor =  JEditor::getInstance($editor);
}

if (strpos($this->item->name, "com_") === 0)
{
	$lang = JFactory::getLanguage();
	$parts = explode(".", $this->item->name);
	$lang->load($parts[0]);
}


if (JevJoomlaVersion::isCompatible("3.0.0"))
{
	if ($this->item->value == "" && file_exists(dirname(__FILE__) . '/' . $this->item->name . ".3.html"))
		$this->item->value = file_get_contents(dirname(__FILE__) . '/' . $this->item->name . ".3.html");
}
if ($this->item->value == "" && file_exists(dirname(__FILE__) . '/' . $this->item->name . ".html"))
	$this->item->value = file_get_contents(dirname(__FILE__) . '/' . $this->item->name . ".html");

//Float layout check to load default value
if ($this->item->name == 'icalevent.list_block1' && $this->item->value == "" && Jfile::exists(JPATH_SITE . '/components/com_jevents/views/float/defaults/icalevent.list_block1.html')) {
	$this->item->value = file_get_contents(JPATH_SITE . '/components/com_jevents/views/float/defaults/icalevent.list_block1.html');
}
if ($this->item->name == 'icalevent.list_block2' && $this->item->value == "" && Jfile::exists(JPATH_SITE . '/components/com_jevents/views/float/defaults/icalevent.list_block2.html')) {
	$this->item->value = file_get_contents(JPATH_SITE . '/components/com_jevents/views/float/defaults/icalevent.list_block2.html');
}

echo $this->sidebar;

$this->replaceLabels($this->item->value);
$bar     = JToolBar::getInstance('newtoolbar');
$toolbar = $bar->getItems() ? $bar->render() : "";
?>
<div id="jev_adminui" class="jev_adminui skin-blue sidebar-mini">
    <header class="main-header">
		<?php echo JEventsHelper::addAdminHeader($items = array(), $toolbar); ?>
    </header>
    <!-- =============================================== -->
    <!-- Left side column. contains the sidebar -->
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
		<?php
		echo $this->sidebar;
		?>

        <!-- /.sidebar -->
    </aside>
    <!-- =============================================== -->
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="min-height: 1096px;">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
				<?php echo JText::_("JEV_LAYOUT_DEFAULTS"); ?>
                <small><?php echo JText::_("JEV_LAYOUT_DEFAULTS_STRAPLINE"); ?></small>
            </h1>
        </section>

        <!-- Main content -->
        <section class="content ov_info">

            <!-- Default box -->
            <div class="box">
                <div class="box-body custom_layouts">
					<script type="text/javascript" >
						<!--//
						Joomla.submitbutton = function(pressbutton) {
							var form = document.adminForm;
<?php
// in case editor is toggled off - needed for TinyMCE
echo $editor->save('value');
?>
							submitform(pressbutton);
						}
//-->
					</script>
                    <form action="index.php" method="post" name="adminForm" id="adminForm">
					<div class="adminform row-fluid" align="left">
                        <div class="form-group span3">
                            <label for="title"><?php echo JText::_('TITLE'); ?>:</label>
                            <input readonly class="inputbox form-control" type="text" id="title" size="50" maxlength="100" value="<?php echo htmlspecialchars(JText::_($this->item->title), ENT_QUOTES, 'UTF-8'); ?>" />
                        </div>
                        <div class="form-group span3">
                            <label for="language"><?php echo JText::_('JFIELD_LANGUAGE_LABEL'); ?>:</label>
                            <input readonly class="inputbox form-control" type="text" id="language" size="50" maxlength="100" value="<?php echo $this->item->language == "*" ? JText::alt('JALL', 'language') : $this->item->language; ?>" />
                        </div>
						<div class="form-group span3">
                            <label for="category"><?php echo JText::_('JCATEGORY'); ?>:</label>
                            <input readonly class="inputbox form-control" type="text" id="language" size="50" maxlength="100" value="<?php echo $this->item->catid == "0" ? JText::alt('JALL', 'language') : $this->item->category_title; ?>" />
                        </div>
						<div class="form-group span3">
                            <label for="name"><?php echo JText::_('NAME'); ?></label>
                            <input readonly class="inputbox form-control" type="text" id="name" size="50" maxlength="100" value="<?php echo htmlspecialchars($this->item->name, ENT_QUOTES, 'UTF-8'); ?>" />
                        </div>
                        <div class="form-group jevpublished span3">
                            <label for="published"><?php echo JText::_("JSTATUS"); ?></label>
                            <?php
                            $poptions = array();
                            $poptions[] = JHTML::_('select.option', 0, JText::_("JUNPUBLISHED"));
                            $poptions[] = JHTML::_('select.option', 1, JText::_("JPUBLISHED"));
                            $poptions[] = JHTML::_('select.option', -1, JText::_("JTRASHED"));
                            echo JHTML::_('select.genericlist', $poptions, 'state', 'class="inputbox form-control"', 'value', 'text', $this->item->state);
                            ?>
                        </div>
                         <div class="form-group span6">
	                         <?php
	                         $pattern = "#.*([0-9]*).*#";
	                         $name = preg_replace("#\.[0-9]+#", "", $this->item->name);
	                         $selectbox = $this->loadTemplate($name);
	                         echo $selectbox;
	                         ?>
                         </div>
                         <div class="form-group span12">
                             <label for="value"> <?php echo JText::_('JEV_LAYOUT'); ?></label>
                             <?php
                             // parameters : areaname, content, hidden field, width, height, rows, cols
                             echo $editor->display('value', htmlspecialchars($this->item->value, ENT_QUOTES, 'UTF-8'), 700, 450, '70', '15', false);
                              ?>
                         </div>
                    </div>

					<?php
					if ($this->item->name != "month.calendar_tip" && $this->item->name != "icalevent.edit_page" && strpos($this->item->name, "com_jevpeople")===false && strpos($this->item->name, "com_jevlocations")===false)
					{
						?>
					<h3><?php echo JText::_("JEV_DEFAULTS_CUSTOM_MODULES");?></h3>
					<?php

					$params = new JRegistry($this->item->params);
					$modids = $params->get("modid", array());
					$modvals = $params->get("modval", array());
					// not sure how this can arise :(
					if (is_object($modvals)){
						$modvals = get_object_vars($modvals);
					}
					$modids = array_values($modids);
					$modvals = array_values($modvals);

					$count = 0;
					$conf = JFactory::getConfig();
					$modeditor =  $editor;

					foreach ($modids as $modid)
					{
						if (trim($modid)=="") {
							$count ++;
							continue;
						}
						?>
						<table cellpadding="5" cellspacing="0" border="0" >
							<tr>
								<td align="left" ><?php echo JText::_('JEV_DEFAULTS_MODULE_ID'); ?>:</td>
								<td align="left" colspan="2"><input name="params[modid][]" id="modid<?php echo $count;?>" type="text" size="40" value="<?php echo $modid?>" /></td>
							</tr>
							<tr>
								<td align="left" ><?php echo JText::_('JEV_DEFAULTS_MODULE_OUTPUT'); ?>:</td>
								<td align="left"><?php echo $modeditor->display('params[modval]['.$count."]", htmlspecialchars($modvals[$count], ENT_QUOTES, 'UTF-8'), 700, 450, '70', '15', false,'modval'.$count );?></td>
								<td align="left" valign="top"><?php echo str_replace("value", "modval".$count, str_replace("jevdefaults", "jevmods".$count, $selectbox));?></td>
							</tr>
						</table>
						<?php
						$count ++;
					}
					// plus one extra one
					?>
					<table cellpadding="5" cellspacing="0" border="0" >
						<tr>
							<td align="left" ><?php echo JText::_('JEV_DEFAULTS_MODULE_ID'); ?>:</td>
							<td align="left" colspan="2"><input name="params[modid][]" id="modid<?php echo $count;?>" type="text" size="40" /></td>
						</tr>
						<tr>
							<td align="left" ><?php echo JText::_('JEV_DEFAULTS_MODULE_OUTPUT'); ?>:</td>
							<td align="left"><?php echo $modeditor->display('params[modval]['.$count."]", htmlspecialchars("", ENT_QUOTES, 'UTF-8'), 700, 450, '70', '15', false,'modval'.$count );?></td>
							<td align="left" valign="top"><?php echo str_replace("value", "modval".$count, str_replace("jevdefaults", "jevmods".$count, $selectbox));?></td>
						</tr>
					</table>
					<?php
					}
					?>
                    <input type="hidden" name="boxchecked" value="0" />
                    <input type="hidden" name="task" value="defaults.edit" />
                    <input type="hidden" name="act" value="" />
                    <input type="hidden" name="option" value="<?php echo JEV_COM_COMPONENT; ?>" />
                    <input type="hidden" name="name" value="<?php echo $this->item->name; ?>">
                    <input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
                    <input type="hidden" name="language" value="<?php echo $this->item->language; ?>">
                    <input type="hidden" name="catid" value="<?php echo $this->item->catid; ?>">
                    </form>
				</div><!-- /.box-body -->
                <div class="box-footer">
                    <p class="text-muted well well-sm no-shadow success" style="margin-top: 10px;">
			            <?php echo JText::_('JEV_CUSTOM_LAYOUTS_FOOTER_INFO'); ?>
                    </p>
                </div><!-- /.box-footer-->
            </div><!-- /.box -->

        </section><!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <footer class="main-footer">
		<?php echo JEventsHelper::addAdminFooter(); ?>
    </footer>
    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
		   immediately after the control sidebar -->
    <div class="control-sidebar-bg" style="position: fixed; height: auto;"></div>
</div>