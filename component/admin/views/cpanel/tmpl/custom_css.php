<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: cpanel.php 3119 2011-12-20 14:34:33Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C)  2008-2016 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.form');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

$jinput = JFactory::getApplication()->input;

$bar     = JToolBar::getInstance('newtoolbar');
$toolbar = $bar->getItems() ? $bar->render() : "";


// Check if we are saving here.
if ($jinput->get('save', null, null))
{
	customCssSave();
}
?>
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
				<?php echo JText::_("JEV_CUSTOM_CSS"); ?>
				<small><?php echo JText::_("JEV_CUSTOM_CSS_DESC"); ?></small>
			</h1>
		</section>

		<!-- Main content -->
		<section class="content ov_info">

			<!-- Default box -->
			<div class="box">
				<div class="box-body">
					<div id="jevents">
						<?php
						$file        = 'jevcustom.css';
						$srcfile     = 'jevcustom.css.new';
						$filepath    = JPATH_ROOT . '/components/com_jevents/assets/css/' . $file;
						$srcfilepath = JPATH_ROOT . '/components/com_jevents/assets/css/' . $srcfile;
						if (!JFile::exists($filepath))
						{
							$filepath = $srcfilepath;
						}
						$content = '';
						$html    = '';

						ob_start();

						$content  = JFile::read($filepath);
						$btnclass = "btn btn-success";
						$mainspan = 10;
						$fullspan = 12;

						?>
						<form action="index.php?option=com_jevents" method="post"
						      name="admin" id="adminForm">
							<?php echo JHtml::_('form.token');
							//TODO force codemirror in as per Joomla! Templates. 
							$editor = JEditor::getInstance($editor = 'none');

							echo $editor->display('content', $content, '99%', '500', '70', '20', false); ?>

							<!--<textarea style="width:60%;height:550px;" name="content"><?php echo $content; ?></textarea>-->
							<input type="hidden" name="controller" value="component"/>
							<input type="hidden" name="option" value="<?php echo JEV_COM_COMPONENT; ?>"/>
							<input type="hidden" name="task" value=""/>
							<input type="hidden" name="save" value="custom_css_save"/>
						</form>
					</div>
				</div>
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
<?php
$html = ob_get_contents();
@ob_end_clean();

echo $html;

function customCssSave()
{
	//Check for request forgeries
	JSession::checkToken() or die('Invalid Token');
	$mainframe = JFactory::getApplication();

	$file     = 'jevcustom.css';
	$filepath = JPATH_ROOT . '/components/com_jevents/assets/css/' . $file;
	$jinput   = JFactory::getApplication()->input;
	$content  = $jinput->post->get('content', '', 'RAW');

	$msg     = '';
	$msgType = '';

	$status = JFile::write($filepath, $content);
	if (!empty($status))
	{
		$msg     = JText::_('JEV_CUSTOM_CSS_SUCCESS');
		$msgType = 'notice';
	}
	else
	{
		$msg     = JText::_('JEV_CUSTOM_CSS_ERROR');
		$msgType = 'error';
	}

	$mainframe->enqueueMessage($msg, $msgType);
	$mainframe->redirect('index.php?option=com_jevents&task=cpanel.custom_css');

}

?>

</div>
