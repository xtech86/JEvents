<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: overview.php 3548 2012-04-20 09:25:43Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C)  2008-2017 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');

$db   = JFactory::getDbo();
$user = JFactory::getUser();

// get configuration object
$cfg = JEVConfig::getInstance();

$pathIMG        = JURI::root() . 'administrator/images/';
$pathJeventsIMG = JURI::root() . 'administrator/components/' . JEV_COM_COMPONENT . '/assets/images/';
global $task;
JHTML::_('behavior.tooltip');

$bar     = JToolBar::getInstance('newtoolbar');
$toolbar = $bar->getItems() ? $bar->render() : '';

JEVHelper::script('select2.full.min.js', 'administrator/components/' . JEV_COM_COMPONENT . '/assets/js/plugins/select2/');


?>

<div id="jev_adminui" class="jev_adminui skin-blue sidebar-mini">
    <header class="main-header">
		<?php echo JEventsHelper::addAdminHeader($items = array(), $toolbar); ?>
    </header>
    <!-- =============================================== -->
    <!-- Left side column. contains the sidebar -->
    <!-- sidebar: style can be found in sidebar.less -->
	<?php echo $this->sidebar; ?>
    <!-- /.sidebar -->
    <!-- =============================================== -->
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="min-height: 1096px;">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
				<?php echo JText::_('ICALS'); ?>
                <small><?php echo JText::_('JEV_ICALS_DESC'); ?></small>
            </h1>
        </section>

        <!-- Main content -->
        <section class="content ov_info">

            <!-- Default box -->
            <div class="box">
                <div class="box-body">
                    <div id="jevents">
                        <form action="index.php" method="post" name="adminForm" id="adminForm">
                            <fieldset id="filter-bar">
                                <div class="span12 filter-select fltrt form-group">
                                    <div class="span3 fltlh"></div>
                                    <div class="span3 fltlh"></div>
                                    <div class="span3 fltrt">
                                        <div class="form-group select2">
                                            <?php echo $this->clist; ?>
                                        </div>
                                    </div>
                                    <div class="span3 fltrt">
                                        <div class="form-group">
                                            <input type="text" name="search" value="<?php echo $this->search; ?>"
                                                   class="inputbox" onChange="document.adminForm.submit();"
                                                   placeholder="<?php echo JText::_('JEV_SEARCH'); ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <table cellpadding="4" cellspacing="0" border="0" width="100%"
                                   class="adminlist  table table-striped">
                                <tr>
                                    <th width="20" nowrap="nowrap">
										<?php echo JHtml::_('grid.checkall'); ?>
                                    </th>
                                    <th class="title" width="20%"
                                        nowrap="nowrap"><?php echo JText::_('JEV_ICAL_SUMMARY'); ?></th>
	                                <th class="refreshed" width="10%"
                                        nowrap="nowrap"><?php echo JText::_('JEV_ICAL_REFRESHED'); ?></th>
                                    <th width="10%" nowrap="nowrap"><?php echo JText::_('JEV_ICAL_TYPE'); ?></th>
                                    <th width="10%"
                                        nowrap="nowrap"><?php echo JText::_('JEV_CATEGORY_NAME'); ?></th>
                                    <th width="10%"
                                        nowrap="nowrap"><?php echo JText::_('JEV_ADMIN_REFRESH'); ?></th>
                                    <th width="10%" nowrap="nowrap"><?php echo JText::_('JEV_PUBLISHED'); ?></th>
                                    <th width="10%"
                                        nowrap="nowrap"><?php echo JText::_('JEV_EVENT_ANONREFRESH'); ?></th>
                                    <th width="10%"
                                        nowrap="nowrap"><?php echo JText::_('JEV_EVENT_ISDEFAULT'); ?></th>
                                    <th width="10%" nowrap="nowrap"><?php echo JText::_('JEV_ACCESS'); ?></th>
                                    <th width="10%" nowrap="nowrap"><?php echo JText::_('JEV_ICAL_ID'); ?></th>
                                </tr>

								<?php
								$k        = 0;
								$nullDate = $db->getNullDate();

								for ($i = 0, $n = count($this->rows); $i < $n; $i++)
								{
									$row = &$this->rows[$i];
									?>
                                    <tr class="row<?php echo $k; ?>">
                                        <td width="20">
                                            <input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]"
                                                   value="<?php echo $row->ics_id; ?>"
                                                   onclick="Joomla.isChecked(this.checked);"/>
                                        </td>
                                        <td>
                                            <a href="#edit"
                                               onclick="return listItemTask('cb<?php echo $i; ?>','icals.edit')"
                                               title="<?php echo JText::_('JEV_CLICK_TO_EDIT'); ?>"><?php echo $row->label; ?></a>
                                        </td>
	                                    <td><?php echo $row->refreshed; ?></td>
                                        <td align="center">
											<?php
											$types = array(JText::_('COM_JEVENTS_MANAGE_CALENDARS_OVERVIEW_REMOTE'),
                                                JText::_('COM_JEVENTS_MANAGE_CALENDARS_OVERVIEW_UPLOADED_FILE'),
                                                JText::_('COM_JEVENTS_MANAGE_CALENDARS_OVERVIEW_NATIVE'),
                                                JText::_('JEV_FACEBOOK_FEED'));
											echo $types[$row->icaltype];
											?>
                                        </td>
                                        <td align="center"><?php echo $row->category; ?></td>
                                        <td align="center">
											<?php
											// only offer reload for URL based ICS
											if ($row->srcURL != "" || (int) $row->icaltype === 3)
											{
												?>
                                                <a href="javascript: void(0);"
                                                   onclick="return listItemTask('cb<?php echo $i; ?>','icals.reload')">
                                                    <img src="<?php echo $pathJeventsIMG . "reload.png"; ?>"
                                                         border="0" alt="reload"/>
                                                </a>
												<?php
											}
											?>

                                        </td>
                                        <td align="center">
											<?php
											$img = $row->state ? JHTML::_('image', 'admin/tick.png', '', array('title' => ''), true) : JHTML::_('image', 'admin/publish_x.png', '', array('title' => ''), true);
											?>
                                            <a href="javascript: void(0);"
                                               onclick="return listItemTask('cb<?php echo $i; ?>','<?php echo $row->state ? 'icals.unpublish' : 'icals.publish'; ?>')">
												<?php echo $img; ?>
                                            </a>
                                        </td>
                                        <td align="center">
											<?php
											if ((int) $row->icaltype === 0 || (int) $row->icaltype === 3)
											{
												$img = $row->autorefresh ? JHTML::_('image', 'admin/tick.png', '', array('title' => ''), true) : JHTML::_('image', 'admin/publish_x.png', '', array('title' => ''), true);
												?>
                                                <a href="javascript: void(0);"
                                                   onclick="return listItemTask('cb<?php echo $i; ?>','<?php echo $row->autorefresh ? 'icals.noautorefresh' : 'icals.autorefresh'; ?>')">
													<?php echo $img; ?>
                                                </a>
												<?php
												if ($row->autorefresh)
												{
													?>
                                                    <br/><a
                                                        href="<?php echo JURI::root() . "index.php?option=" . JEV_COM_COMPONENT . "&icsid=" . $row->ics_id . "&task=icals.reload"; ?>"
                                                        title="<?php echo JText::_("JEV_AUTOREFRESH_LINK") ?>"><?php echo JText::_("JEV_AUTOREFRESH_LINK") ?></a>
													<?php
												}
											}
											else
											{
												echo " - ";
											}
											?>
                                        </td>
                                        <td align="center">
											<?php
											if ($row->icaltype == 2)
											{
												$img = $row->isdefault ? JHTML::_('image', 'admin/tick.png', '', array('title' => ''), true) : JHTML::_('image', 'admin/publish_x.png', '', array('title' => ''), true);
												?>
                                                <a href="javascript: void(0);"
                                                   onclick="return listItemTask('cb<?php echo $i; ?>','<?php echo $row->isdefault ? 'icals.notdefault' : 'icals.isdefault'; ?>')">
													<?php echo $img; ?>
                                                </a>
												<?php
											}
											else
											{
												echo " - ";
											}
											?>
                                        </td>
                                        <td align="center"><?php echo $row->_groupname; ?></td>
                                        <td align="center"><?php echo $row->ics_id; ?></td>
                                    </tr>
									<?php
									$k = 1 - $k;
								}
								?>
                                <tr>
                                    <th align="center"
                                        colspan="12"><?php echo $this->pageNav->getListFooter(); ?></th>
                                </tr>
                            </table>
							<?php echo JHtml::_('form.token'); ?>
                            <input type="hidden" name="option" value="<?php echo $this->option; ?>"/>
                            <input type="hidden" name="task" value="icals.list"/>
                            <input type="hidden" name="boxchecked" value="0"/>
                        </form>
                    </div>
                </div><!-- /.box-body -->
        </section><!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
	<?php echo JEventsHelper::addAdminFooter(); ?>
    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
		   immediately after the control sidebar -->
    <div class="control-sidebar-bg" style="position: fixed; height: auto;"></div>
</div>