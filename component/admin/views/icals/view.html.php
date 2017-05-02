<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: view.html.php 3548 2012-04-20 09:25:43Z geraintedwards $
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
class AdminIcalsViewIcals extends JEventsAbstractView
{

	public function overview($tpl = null)
	{

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('ICALS'));

		// Set toolbar items for the page
		$this->toolbarpublishList('icals.publish');
		$this->toolbarunpublishList('icals.unpublish');
		$this->toolbaraddNew('icals.edit');
		$this->toolbareditList('icals.edit');
		$this->toolbardeleteList('Delete Ical and all associated events and repeats?', 'icals.delete');

		JEventsHelper::addSubmenu();
		$this->sidebar = JHtmlSidebar::render();

		JHTML::_('behavior.tooltip');
	}

	public function edit($tpl = null)
	{

		JEVHelper::script('editicalJQ.js', 'components/' . JEV_COM_COMPONENT . '/assets/js/');

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('EDIT_ICS'));

		if ($this->editItem && isset($this->editItem->ics_id) && $this->editItem->ics_id > 0)
		{
			$this->toolbarSave('icals.savedetails');
		}
		$this->toolbarCancel('icals.list');

		$params = JComponentHelper::getParams(JEV_COM_COMPONENT);

		$db = JFactory::getDbo();
		if ($params->get("authorisedonly", 0))
		{
			// get authorised users
			$sql = "SELECT u.* FROM #__jev_users as jev LEFT JOIN #__users as u on u.id=jev.user_id where jev.published=1 and jev.cancreate=1";
			$db  = JFactory::getDbo();
			$db->setQuery($sql);
			$users = $db->loadObjectList();
		}
		else
		{
			$rules         = JAccess::getAssetRules("com_jevents", true);
			$creatorgroups = $rules->getData();
			// need to merge the arrays because of stupid way Joomla! checks super user permissions
			//$creatorgroups = array_merge($creatorgroups["core.admin"]->getData(), $creatorgroups["core.create"]->getData());
			// use union orf arrays sincee getData no longer has string keys in the resultant array
			//$creatorgroups = $creatorgroups["core.admin"]->getData()+ $creatorgroups["core.create"]->getData();
			// use union orf arrays sincee getData no longer has string keys in the resultant array
			$creatorgroupsdata = $creatorgroups["core.admin"]->getData();
			// take the higher permission setting
			foreach ($creatorgroups["core.create"]->getData() as $creatorgroup => $permission)
			{
				if ($permission)
				{
					$creatorgroupsdata[$creatorgroup] = $permission;
				}
			}

			$users = array(0);
			foreach ($creatorgroupsdata as $creatorgroup => $permission)
			{
				if ($permission == 1)
				{
					$users = array_merge(JAccess::getUsersByGroup($creatorgroup, true), $users);
				}
			}
			$sql = "SELECT * FROM #__users where id IN (" . implode(",", array_values($users)) . ") ORDER BY name asc";
			$db->setQuery($sql);
			$users = $db->loadObjectList();
		}
		$userOptions = array();
		foreach ($users as $user)
		{
			$userOptions[] = JHTML::_('select.option', $user->id, $user->name . " ($user->username)");
		}
		$jevuser = JFactory::getUser();
		if ($this->editItem && isset($this->editItem->ics_id) && $this->editItem->ics_id > 0 && $this->editItem->created_by > 0)
		{
			$created_by = $this->editItem->created_by;
		}
		else
		{
			$created_by = $jevuser->id;
		}
		if (count($userOptions) > 0)
		{
			$userlist = JHTML::_('select.genericlist', $userOptions, 'created_by', 'class="inputbox select2" size="1" ', 'value', 'text', $created_by);
		}
		else
		{
			$userList = "";
		}
		$this->users = $userList;

		JHTML::_('behavior.tooltip');
		JEventsHelper::addSubmenu();
		$this->sidebar = JHtmlSidebar::render();

		$this->setLayout("edit");

	}

}
