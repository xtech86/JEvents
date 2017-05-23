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

$jinput = JFactory::getApplication()->input;

JEVHelper::stylesheet('jev_cp.css', 'administrator/components/' . JEV_COM_COMPONENT . '/assets/css/');

$bar = JToolBar::getInstance('newtoolbar');
$toolbar = $bar->getItems() ? $bar->render() : "";
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
				<?php echo JText::_('JEV_CUSTOM_CSS'); ?>
                <small><?php echo JText::_('Editor'); ?></small>
            </h1>
        </section>

        <!-- Main content -->
        <section class="content ov_info">

            <!-- Default box -->
            <div class="box">
                <div class="box-body">
                    <div id="jevents">
                        <form action="index.php?option=com_jevents&view=customcss" method="post" name="adminForm" id="adminForm" class="form-vertical">
                            <?php //Render the Editor ?>
                            <?php echo $this->form->renderField('source'); ?>
                            <?php echo JHtml::_( 'form.token' ); ?>
                            <input type="hidden" name="controller" value="component" />
                            <input type="hidden" name="option" value="<?php echo JEV_COM_COMPONENT; ?>" />
                            <input type="hidden" name="task" value="" />
                        </form>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
        </section><!-- /.content -->

    </div>
    <!-- /.content-wrapper -->
    <?php echo JEventsHelper::addAdminFooter(); ?>
    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
    <div class="control-sidebar-bg" style="position: fixed; height: auto;"></div>
</div>