<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: cpanel.php 3119 2011-12-20 14:34:33Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C)  2008-2017 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\String\StringHelper;

$params = JComponentHelper::getParams(JEV_COM_COMPONENT);
$version = JEventsVersion::getInstance();

JEVHelper::stylesheet('jev_cp.css', 'administrator/components/' . JEV_COM_COMPONENT . '/assets/css/');

//Custom Toolbar Load
$bar     = JToolBar::getInstance('newtoolbar');
$toolbar = $bar->getItems() ? $bar->render() : "";

//Global limit
$limit = 4;

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
				Dashboard
				<small>Lets get started.</small>
			</h1>
		</section>

		<!-- Main content -->
		<section class="content ov_info cpanel_cpanel">
			<!-- Default box -->
			<div class="row">
				<div class="span12">
					<div class="box box-primary">
						<div class="box-header with-border">
							<h3 class="box-title">Welcome to JEvents</h3>
							<span class="label label-primary pull-right"></span>
						</div><!-- /.box-header -->
						<div class="box-body">
							Here you can see an overview
						</div><!-- /.box-body -->
					</div>
				</div>

			</div><!-- /.box -->

			<div class="row">
				<div class="span6">
					<div class="box box-primary">
						<div class="box-header with-border">
							<h3 class="box-title"><?php echo JText::_('JEV_CPANEL_LATEST_EVENTS_ADDED'); ?></h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                                </button>
                            </div>
							<span class="label label-primary pull-right"></span>
						</div><!-- /.box-header -->
						<div class="box-body">
							<p><?php echo JText::_("JEV_CPANEL_LATEST_EVENTS_ADDED_DESC"); ?> </p>
							<?php
							// lets get the most recently created events
							// TODO remove the query from the view, should be in model.

							// Get a db connection.
							$db = JFactory::getDbo();

							// Create a new query object.
							$query = $db->getQuery(true);

							// Select all records from the user profile table where key begins with "custom.".
							// Order it by the ordering field.

							$query
							->select(array('evdet_id','dtstart', 'dtend', 'summary', 'modified', 've.ev_id', 've.created', 've.created_by', 've.state', 've.modified_by'))
							->from($db->quoteName('#__jevents_vevdetail'))
							->leftJoin($db->quoteName('#__jevents_vevent') . 'AS ve ON ve.detail_id = evdet_id')
							->where($db->quoteName('ve.state') . ' = 1')
							->order('ve.created ASC')
							->setLimit($limit);

							// Reset the query using our newly populated query object.
							$db->setQuery($query);

							// Load the results as a list of stdClass objects (see later for more options on retrieving data).
							$results = $db->loadObjectList();

							if (count($results) > 0)
							{
							echo '<ul class="todo-list">';
								foreach ($results as $row)
								{
									$user = JFactory::getUser($row->created_by);
									echo '<li><span class="text">' . $row->summary . '</span>
									    <span class="label label-success"> ' . JText::sprintf("JEV_BY_SPRINT", $user->name) . '</span>
									    <div class="tools">
										    <a href="index.php?option=com_jevents&task=icalevent.edit&evid=' . $row->ev_id . '"><i class="fa fa-edit"></i></a>
										    <a href="index.php?option=com_jevents&task=icalevent.delete&evid=' . $row->ev_id . '"><i class="fa fa-trash-o"></i></a>
									    </div>
								    </li>';
								}

								echo '</ul>';

							}  else {
                                echo JText::_('JEV_CPANEL_LATEST_EVENTS_ADDED_NONE');
							}?>
						</div><!-- /.box-body -->
					</div><!-- /.box -->
					<div class="box box-info collapsed-box">
						<div class="box-header with-border">
							<h3 class="box-title"><?php echo JText::_('JEV_CPANEL_RECENTLY_EDITED_EVENTS'); ?></h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                                </button>
                            </div>
							<span class="label label-danger pull-right"></span>
						</div><!-- /.box-header -->
						<div class="box-body" style="display:none;">
							<p><?php echo JText::_('JEV_CPANEL_RECENTLY_EDITED_EVENTS_DESC'); ?></p>

							<?php
							// lets get the first 10 unpublished events
							// TODO remove the query from the view, should be in model.

							// Get a db connection.
							$db = JFactory::getDbo();

							// Create a new query object.
							$query = $db->getQuery(true);

							// Select all records from the user profile table where key begins with "custom.".
							// Order it by the ordering field.

							$query
								->select(array('evdet_id','dtstart', 'dtend', 'summary', 'modified', 'ed.ev_id', 'ed.state' , 'ed.modified_by'))
								->from($db->quoteName('#__jevents_vevdetail'))
								->leftJoin($db->quoteName('#__jevents_vevent') . 'AS ed ON ed.detail_id = evdet_id')
								->where($db->quoteName('ed.state') . ' = 1')
								->order('modified ASC')
								->setLimit($limit);

							//echo ($query);die;
							// Reset the query using our newly populated query object.
							$db->setQuery($query);

							// Load the results as a list of stdClass objects (see later for more options on retrieving data).
							$results = $db->loadObjectList();
							$resultscnt = count($results);

							if ($resultscnt > 0)
							{
								echo '<ul class="todo-list">';
								foreach ($results as $row)
								{
									$user = JFactory::getUser($row->modified_by);
									echo '<li><span class="text">' . $row->summary . '</span>
									 <span class="label label-info"> ' . JText::sprintf("JEV_BY_SPRINT", $user->name) . '</span>
										<div class="tools">
										<a href="index.php?option=com_jevents&task=icalevent.edit&evid='.$row->ev_id.'"><i class="fa fa-edit"></i></a>
										<a href="index.php?option=com_jevents&task=icalevent.delete&evid='.$row->ev_id.'"><i class="fa fa-trash-o"></i></a>
										</div>
									</li>';
								}
								echo '</ul>';
							} else {
								echo JText::_('JEV_CPANEL_RECENTLY_EDITED_EVENTS_NONE');
							}
							?>
						</div><!-- /.box-body -->
					</div><!-- /.box -->
					<div class="box box-warning collapsed-box">
						<div class="box-header with-border">
                            <h3 class="box-title"><?php echo JText::_('JEV_CPANEL_RECENTLY_UNPUBLISHED'); ?></h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                                </button>
                            </div>
							<span class="label label-danger pull-right"></span>
						</div><!-- /.box-header -->
						<div class="box-body" style="display:none;">
							<p><?php echo JText::_('JEV_CPANEL_RECENTLY_UNPUBLISHED_DESC'); ?></p>

							<?php
							// lets get the first 10 unpublished events
							// TODO remove the query from the view, should be in model.

							// Get a db connection.
							$db = JFactory::getDbo();

							// Create a new query object.
							$query = $db->getQuery(true);

							// Select all records from the user profile table where key begins with "custom.".

							$query
								->select(array('evdet_id','dtstart', 'dtend', 'summary', 'modified', 'ed.ev_id', 'ed.state', 'ed.modified_by'))
								->from($db->quoteName('#__jevents_vevdetail'))
								->leftJoin($db->quoteName('#__jevents_vevent') . 'AS ed ON ed.detail_id = evdet_id')
								->where($db->quoteName('ed.state') . ' = 0')
								->order('modified ASC')
								->setLimit($limit);

							//echo ($query);die;
							// Reset the query using our newly populated query object.
							$db->setQuery($query);

							// Load the results as a list of stdClass objects (see later for more options on retrieving data).
							$results = $db->loadObjectList();
							$resultscnt = count($results);

							if ($resultscnt > 0)
							{
								echo '<ul class="todo-list">';
								foreach ($results as $row)
								{
									$user = JFactory::getUser($row->modified_by);
									echo '<li><span class="text">' . $row->summary . '</span>
									 <span class="label label-warning"> ' . JText::sprintf("JEV_BY_SPRINT", $user->name) . '</span>
										<div class="tools">
											<a href="index.php?option=com_jevents&task=icalevent.edit&evid='.$row->ev_id.'"><i class="fa fa-edit"></i></a>
											<a href="index.php?option=com_jevents&task=icalevent.delete&evid='.$row->ev_id.'"><i class="fa fa-trash-o"></i></a>
										</div>
									</li>';
								}
								echo '</ul>';
								if ($resultscnt >= $limit) {
								}
							} else {
								echo  JText::_('JEV_CPANEL_RECENTLY_UNPUBLISHED_NONE');
							}
							?>
						</div><!-- /.box-body -->
					</div><!-- /.box -->
					<div class="box box-danger collapsed-box">
						<div class="box-header with-border">
							<h3 class="box-title"><?php echo JText::_('JEV_CPANEL_RECENTLY_TRASHED_EVENTS'); ?></h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                                </button>
                            </div>
							<span class="label label-danger pull-right"></span>
						</div><!-- /.box-header -->
						<div class="box-body" style="display:none;">
							<p><?php echo JText::_('JEV_CPANEL_RECENTLY_TRASHED_EVENTS_DESC');?> </p>

							<?php
							// lets get the first 10 unpublished events
							// TODO remove the query from the view, should be in model.

							// Get a db connection.
							$db = JFactory::getDbo();

							// Create a new query object.
							$query = $db->getQuery(true);

							// Select all records from the user profile table where key begins with "custom.".
							// Order it by the ordering field.

							$query
								->select(array('evdet_id','dtstart', 'dtend', 'summary', 'modified', 'ed.ev_id', 'ed.state', 'ed.modified_by'))
								->from($db->quoteName('#__jevents_vevdetail'))
								->leftJoin($db->quoteName('#__jevents_vevent') . 'AS ed ON ed.detail_id = evdet_id')
								->where($db->quoteName('ed.state') . ' = -1')
								->order('modified ASC')
								->setLimit($limit);

							//echo ($query);die;
							// Reset the query using our newly populated query object.
							$db->setQuery($query);

							// Load the results as a list of stdClass objects (see later for more options on retrieving data).
							$results = $db->loadObjectList();
							$resultscnt = count($results);

							if ($resultscnt > 0)
							{
								echo '<ul class="todo-list">';
								foreach ($results as $row)
								{
									$user = JFactory::getUser($row->modified_by);
									echo '<li><span class="text">' . $row->summary . '</span>
									 <span class="label label-danger"> ' . JText::sprintf("JEV_BY_SPRINT", $user->name) . '</span>
										<div class="tools">
										<a href="index.php?option=com_jevents&task=icalevent.edit&evid='.$row->ev_id.'"><i class="fa fa-edit"></i></a>
										<!--<a href="index.php?option=com_jevents&task=icalevent.publish&evid='.$row->ev_id.'"><i class="fa fa-arrow-circle-up"></i></a>-->
										</div>
									</li>';
								}
								echo '</ul>';

							} else {
								echo JText::_('JEV_CPANEL_RECENTLY_TRASHED_EVENTS_NONE');
							}
							?>
						</div><!-- /.box-body -->
					</div><!-- /.box -->
				</div><!-- /.col -->
			</div>

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
