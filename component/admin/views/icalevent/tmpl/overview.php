<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: overview.php 3576 2012-05-01 14:11:04Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C)  2008-2016 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\String\StringHelper;

// we would use this to add custom data to the output here
//JEVHelper::onDisplayCustomFieldsMultiRow($this->rows);

JHTML::_('behavior.tooltip');

$db = JFactory::getDBO();
$user = JFactory::getUser();

// get configuration object
$cfg = JEVConfig::getInstance();
$this->_largeDataSet = $cfg->get('largeDataSet', 0);
$orderdir = JFactory::getApplication()->getUserStateFromRequest("eventsorderdir", "filter_order_Dir", 'asc');
$order = JFactory::getApplication()->getUserStateFromRequest("eventsorder", "filter_order", 'start');
$pathIMG = JURI::root() . 'administrator/images/';
$mainspan = 10;
$fullspan = 12;

//Custom Toolbar Load
$bar     = JToolBar::getInstance('newtoolbar');
$toolbar = $bar->getItems() ? $bar->render() : "";

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <!-- New FLAT Admin UI -->
    <div id="jev_adminui" class="jev_adminui skin-blue sidebar-mini">
        <header class="main-header">
            <?php echo JEventsHelper::addAdminHeader($items = array(), $toolbar); ?>
        </header>
        <!-- =============================================== -->
        <!-- Left side column. contains the sidebar -->
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <?php echo JEventsHelper:: addAdminSidebar($toolbar); ?>
            <!-- /.sidebar -->
        </aside>
        <!-- =============================================== -->
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper" style="min-height: 1096px;">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    <?php echo JText::_("JEV_ADMIN_ICAL_EVENTS"); ?>
                    <small><?php echo JText::_("JEV_SELECT_EVENT_TO_EDIT");?></small>
                </h1>
                <section class="content-filters">
                    <?php
                    if ($this->filters) { echo $this->filters; }?>
                </section>
            </section>

            <!-- Main content -->
            <section class="content events_list">
                <!-- Default box -->
                <div class="box">
                    <!-- /.box-header -->
                    <div class="box-body no-padding">
                        <table class="table table-striped">
                            <tbody>
                            <tr>
                                <th style="width: 10px"><?php echo JHtml::_('grid.checkall'); ?></th>
                                <th><?php echo JHTML::_('grid.sort', 'JEV_ADMIN_EVENT_TITLE', 'title', $orderdir, $order, "icalevent.list"); ?></th>
                                <th><?php echo JText::_('JEV_AUTHOR'); ?></th>
                                <th><?php echo JText::_('JEV_EVENT_DATE_TIME'); ?></th>
                            </tr>
                            <?php
                            $k = 0;
                            $nullDate = $db->getNullDate();

                            for ($i = 0, $n = count($this->rows); $i < $n; $i++) {
                                $row = &$this->rows[$i];
                                ?>
                                <tr class="row<?php echo $k; ?>">
                                    <td width="20" class="jev_gr_cb">
                                        <?php echo JHtml::_('grid.id', $i, $row->ev_id()); ?>
                                    </td>
                                    <td class="summary">
                                        <a href="#edit"
                                           onclick="return listItemTask('cb<?php echo $i; ?>','icalevent.edit')"
                                           title="<?php echo JText::_('JEV_CLICK_TO_EDIT'); ?>"><?php echo $row->title(); ?></a>
                                        <small>( <?php echo $row->_groupname; ?> )</small>
                                        <?php
                                        if ($row->hasrepetition()) {
                                        ?>
                                        <a href="javascript: void(0);"
                                           onclick="return listItemTask('cb<?php echo $i; ?>','icalrepeat.list')" class="ashow_repeats">
                                            <i class="fa fa-plus"> </i> <small><?php echo JText::_("JEV_VIEW_REPEATS"); ?></small>
                                        </a>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                    <!--<td align="center">
                                        <?php
                                        if ($row->hasrepetition()) {
                                            ?>
                                            <a href="javascript: void(0);"
                                               onclick="return listItemTask('cb<?php echo $i; ?>','icalrepeat.list')"
                                               class="btn btn-micro">
                                                <span class="icon-list"> </span>
                                            </a>
                                        <?php } ?>
                                    </td>-->

                                    <td align="center"><?php echo $row->creatorName(); ?></td>
                                    <td align="center">
                                        <dl class="dl-horizontal admin_d_and_t">

                                        <?php
                                        if ($this->_largeDataSet)
                                        {
                                            echo '<dt>' . JText::_('JEV_FROM') . ':</dt> ' . $row->publish_up();
                                        }
                                        else
                                        {
                                            $times = '<dt>' . JText::_('JEV_START_FROM') . ':</dt> <dd>' . ($row->alldayevent() ? JString::substr($row->publish_up(), 0, 10) : JString::substr($row->publish_up(),0,16)) . '</dd>';
                                            $times .= '<dt>' . JText::_('JEV_TO_END') . ':</dt> <dd>' . (($row->noendtime() || $row->alldayevent()) ? JString::substr($row->publish_down(), 0, 10) : JString::substr($row->publish_down(),0,16)) . '</dd>';
                                            echo $times;
                                        }
                                        ?>
                                        </dl>
                                    </td>
                                    <!-- <td align="center"><?php echo $row->modified; ?></td> -->
                                </tr>
                                <?php
                                $k = 1 - $k;
                            } ?>
                            </tbody>
                            <tr>
                                <th align="center" colspan="10"><?php echo $this->pageNav->getListFooter(); ?></th>
                            </tr>
                        </table>
                        <input type="hidden" name="option" value="<?php echo JEV_COM_COMPONENT; ?>"/>
                        <input type="hidden" name="task" value="icalevent.list"/>
                        <input type="hidden" name="boxchecked" value="0"/>
                        <input type="hidden" name="filter_order" value="<?php echo $order; ?>"/>
                        <input type="hidden" name="filter_order_Dir" value="<?php echo $orderdir; ?>"/>
                    </div><!-- /.box-body -->
                    <div class="box-footer">

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
</form>
<br />
