<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: icalevent.php 3576 2012-05-01 14:11:04Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C) 2008-2017 GWE Systems Ltd, 2006-2008 JEvents Project Group
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');

use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;

class ImporterController extends JControllerAdmin
{

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('overview', 'overview');
		$this->registerTask('apply', 'save');
		$this->registerDefaultTask("display");
	}

	public function display($cachable = false, $urlparams = Array()) {
		// get the view
		$this->view = $this->getView('Importer', 'html', 'ImporterView');

		//Get Application
		$app = JFactory::getApplication();

		//Hold on... Are you a super user?
		$user = JFactory::getUser();

		// Get/Create the model
		if ($model = $this->getModel()) {
			// Push the model into the view (as default)
			$this->view->setModel($model, true);
		}

		if (!$user->authorise('core.admin')) {
			$msg = JTExt::_('JEV_ERROR_NOT_AUTH_IMPORTER');
			$msgType = 'error';
			$app->enqueueMessage($msg, $msgType);
			$app->redirect('index.php?option=com_jevents&msg=' . $msg . '&msgtype=' . $msgType . '');
			return;
		}

		// Set the layout
		$this->view->setLayout('default');
		$this->view->assign('title', JText::_('JEV_IMPORTER'));
		$this->view->display();
	}

	public function save() {
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		var_dump($this);
		return parent::save();
		die();
	}
}