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

$bar     = JToolBar::getInstance('newtoolbar');
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
                            <?php
                            $clubnews = $this->renderVersionsForClipboard();
                            $label    = JText::_("JEV_VERSION_INFORMATION_FOR_SUPPORT");
                            //echo JHtml::_('sliders.panel', $label, 'cpanelstatustextarea');
                            ?>
                            <p>
                                <strong><?php echo JText::_("JEV_VERSION_INFORMATION_FOR_SUPPORT_DESCRIPTION"); ?></strong>
                            </p>

                                <?php echo $clubnews; ?>

                                <input type="hidden" name="task" value="cpanel"/>
                                <input type="hidden" name="act" value=""/>
                                <input type="hidden" name="option" value="<?php echo JEV_COM_COMPONENT; ?>"/>
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