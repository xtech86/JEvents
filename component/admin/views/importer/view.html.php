<?php

/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: view.html.php 3543 2012-04-20 08:17:42Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C)  2008-2017 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML View class for the component
 *
 * @static
 */

use Joomla\String\StringHelper;

class ImporterViewImporter extends JEventsAbstractView {

	function display($cachable = false)
	{
		jimport('joomla.html.pane');

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JEVENTS') . ': ' . JText::_('JEV_IMPORTER'));

		$bar = JToolbar::getInstance('newtoolbar');

		JToolBarHelper::title(JText::_('COM_JEVENTS') . ': ' . JText::_('JEV_IMPORTER'), 'jevents');

		JEventsHelper::addSubmenu();

		$this->sidebar = JHtmlSidebar::render();

		$params = JComponentHelper::getParams(JEV_COM_COMPONENT);

		return parent::display();

	}
}

