<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: view.html.php 3401 2012-03-22 15:35:38Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C)  2008-2019 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * HTML View class for the component
 *
 * @static
 */
class AdminIcaleventViewIcalevent extends JEventsAbstractView
{

	function overview($tpl = null)
	{
		$app    = Factory::getApplication();

		$this->rows = $this->getModel()->getItems();
		$total = $this->getModel()->getTotal();

		jimport('joomla.html.pagination');
		$limit      = intval($this->getModel()->getState("list.limit", $app->getCfg('list_limit', 10)));
		$limitstart = intval($this->getModel()->getState("list.start", 0));
		$this->pagination = new \Joomla\CMS\Pagination\Pagination($total, $limitstart, $limit);

		$document = Factory::getDocument();
		$document->setTitle(JText::_('ICAL_EVENTS'));

		// Set toolbar items for the page
		JToolbarHelper::title(JText::_('ICAL_EVENTS'), 'jevents');
		JToolbarHelper::addNew('icalevent.edit');
		JToolbarHelper::editList('icalevent.edit');
		JToolbarHelper::publishList('icalevent.publish');
		JToolbarHelper::unpublishList('icalevent.unpublish');
		JToolbarHelper::custom('icalevent.editcopy', 'copy.png', 'copy.png', 'JEV_ADMIN_COPYEDIT');


		// Get fields from request if they exist
		$state      = (int) $this->getModel()->getState('filter.state', 0);
		$created_by = $this->getModel()->getState('filter.created_by', '');
		if ($created_by !== '')
		{
			$created_by = (int) $created_by;
		}
		$icsFile  = (int) $this->getModel()->getState('filter.icsFile', 0);
		$showpast = (int) $this->getModel()->getState('filter.showpast', '');

		if (!$state)
		{
			$state = 3;
			JToolbarHelper::trash('icalevent.delete');
		} else if ($state == -1){
			JToolbarHelper::deleteList("JEV_EMPTY_TRASH_DELETE_EVENT_AND_ALL_REPEATS", 'icalevent.emptytrash',"JTOOLBAR_EMPTY_TRASH");
		}
		else {
			JToolbarHelper::trash('icalevent.delete');
		}

		JToolbarHelper::spacer();
		

		$showUnpublishedICS = false;

		$db = Factory::getDbo();

		JHtmlSidebar::setAction('index.php?option=com_jevents&task=icalevent.list');

		// Get list of ics Files
		$query = "SELECT ics.ics_id as value, ics.label as text FROM #__jevents_icsfile as ics ";

		if (!$showUnpublishedICS)
		{
			$query .= " WHERE ics.state=1";
		}

		$query .= " ORDER BY ics.isdefault DESC, ics.label ASC";

		$db->setQuery($query);
		$icsfiles   = array();
		$icsfiles[] = array('value' => '', 'text' => JText::_('JEV_SELECT_ISCFILE'));
		$dbicsfiles = $db->loadAssocList();

		foreach ($dbicsfiles As $iscfile) {
			$icsfiles[] = $iscfile;
		}

		$this->filters = array('icsfile' =>
			HTMLHelper::_('select.genericlist', $icsfiles, 'filter[icsFile]', 'class="gsl-select" onChange="Joomla.submitform();"', 'value', 'text', $icsFile)
		);

		// get list of categories
		$catid    = intval($this->getModel()->getState('filter.catid', 0));
		$catidtop = $catid;
		$showUnpublishedCategories = true;

		$attribs = 'class="gsl-select" size="1" onchange="document.adminForm.submit();"';
		$clist   = JEventsHTML::buildCategorySelect($catid, $attribs, null, $showUnpublishedCategories, false, $catidtop, "filter[catid]");
		// if there is only one category then do not show the filter
		if (strpos($clist, "<select") === false)
		{
			$clist = "";
		}
		$this->filters['catid'] = $clist;

		$options[] = HTMLHelper::_('select.option', '1', JText::_('JEV_HIDE_PAST_EVENTS_NO'));
		$options[] = HTMLHelper::_('select.option', '0', JText::_('JEV_HIDE_PAST_EVENTS_YES'));
		$plist     = HTMLHelper::_('select.genericlist', $options, 'filter[showpast]', 'class="gsl-select" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $showpast);
		$this->filters['showpast'] = $plist;

		$sql = "SELECT distinct u.id, u.name, u.username FROM #__jevents_vevent as jev LEFT JOIN #__users as u on u.id=jev.created_by ORDER BY u.name ";
		$db->setQuery($sql);
		$users = $db->loadObjectList();

		$userOptions = array(
			HTMLHelper::_('select.option', '', JText::_('JEV_SELECT_CREATOR')),
		);

		foreach ($users as $user)
		{
			if (!$user->id)
			{
				$user->id = 0;
			}
			$userOptions[] = HTMLHelper::_('select.option', $user->id, $user->name . " ($user->username)");
		}

		$this->filters['created_by'] = HTMLHelper::_('select.genericlist', $userOptions, 'filter[created_by]', 'class="gsl-select" onChange="Joomla.submitform();"', 'value', 'text', $created_by);


		// Load the tags filter
		if (isset($this->tagsFiltering)) {
			// Load the tags filter
			$tagFilterHtml  = jevFilterProcessing::getInstance(array('taglookup'))->getFilterHTML(true)[0]['html'];
			// We have to use a dirty str_replace since Joomla! clear function requires value to be empty for a clear filters.
			$earchBtn = '<button type="submit" class="btn hasTooltip" title="" aria-label="' . JText::_('JEV_SEARCH')  . '" data-original-title="' . JText::_('JEV_SEARCH')  . '">
							<span class="icon-search" aria-hidden="true"></span>
						</button>';
			$this->filters['tag'] = str_replace('<option value="0">Select Tag(s)</option>', '<option value="">' . JText::_("JEV_SELECT_TAG") . ' </option>', $tagFilterHtml) . $earchBtn ;
		}



		$this->languages = $this->get('Languages');

		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

	}

	public function edit($tpl = null)
	{
		$params = ComponentHelper::getParams(JEV_COM_COMPONENT);

		$document = Factory::getDocument();
		//Define to keep editor happy that it is defined.
		$editStrings = "";
		include(JEV_ADMINLIBS . "editStrings.php");
		$document->addScriptDeclaration($editStrings);

		// WHY THE HELL DO THEY BREAK PUBLIC FUNCTIONS !!!
		JEVHelper::script('editicalJQ.js', 'components/' . JEV_COM_COMPONENT . '/assets/js/');
		JEVHelper::script('JevStdRequiredFieldsJQ.js', 'components/' . JEV_COM_COMPONENT . '/assets/js/');

		if ($this->row->title() === '')
		{
			$document->setTitle(JText::_('CREATE_ICAL_EVENT'));
			// Set toolbar items for the page
			JToolbarHelper::title(JText::_('CREATE_ICAL_EVENT'), 'jevents');

			// Set default noendtime
			$this->row->noendtime((int) $params->get('default_noendtime', '0'));
		}
		else
		{
			$document->setTitle(JText::_('EDIT_ICAL_EVENT'));

			// Set toolbar items for the page
			JToolbarHelper::title(JText::_('EDIT_ICAL_EVENT'), 'jevents');
		}

		if ($this->id > 0)
		{
			if ($this->editCopy)
			{

				if (JEVHelper::isEventEditor() || JEVHelper::canEditEvent($this->row))
				{
					$this->toolbarConfirmButton("icalevent.apply", JText::_("JEV_SAVE_COPY_WARNING"), 'apply', 'apply', 'JEV_SAVE', false);
				}
				$this->toolbarConfirmButton("icalevent.save", JText::_("JEV_SAVE_COPY_WARNING"), 'save', 'save', 'JEV_SAVE_CLOSE', false);
				$this->toolbarConfirmButton("icalevent.savenew", JText::_("JEV_SAVE_COPY_WARNING"), 'save', 'save', 'JEV_SAVE_NEW', false);
			}
			else
			{
				if (JEVHelper::isEventEditor() || JEVHelper::canEditEvent($this->row))
				{
					$this->toolbarConfirmButton("icalevent.apply", JText::_("JEV_SAVE_ICALEVENT_WARNING"), 'apply', 'apply', 'JEV_SAVE', false);
				}
				$this->toolbarConfirmButton("icalevent.save", JText::_("JEV_SAVE_ICALEVENT_WARNING"), 'save', 'save', 'JEV_SAVE_CLOSE', false);
				$this->toolbarConfirmButton("icalevent.savenew", JText::_("JEV_SAVE_COPY_WARNING"), 'save', 'save', 'JEV_SAVE_NEW', false);

			}
		}
		else
		{
			$canEditOwn = false;
			$params = ComponentHelper::getParams(JEV_COM_COMPONENT);
			if (!$params->get("authorisedonly", 0))
			{
				$juser = Factory::getUser();
				$canEditOwn = $juser->authorise('core.edit.own', 'com_jevents');
			}
			if (JEVHelper::isEventEditor() || $canEditOwn)
			{
				$this->toolbarButton("icalevent.apply", 'apply', 'apply', 'JEV_SAVE', false);
			}
			$this->toolbarConfirmButton("icalevent.save", JText::_("JEV_SAVE_ICALEVENT_WARNING"), 'save', 'save', 'JEV_SAVE_CLOSE', false);
			$this->toolbarConfirmButton("icalevent.savenew", JText::_("JEV_SAVE_COPY_WARNING"), 'save', 'save', 'JEV_SAVE_NEW', false);
		}



		JToolbarHelper::cancel('icalevent.cancel');
		//JToolbarHelper::help( 'screen.icalevent.edit', true);

		// TODO move this into JForm field type!
		$this->setCreatorLookup();

		// load Joomla javascript classes
		HTMLHelper::_('behavior.core');
		$this->setLayout("edit");

		$this->setupEditForm();

	}

	function translate($tpl = null)
	{
		$app    = Factory::getApplication();
		$input  = $app->input;
		$params = ComponentHelper::getParams(JEV_COM_COMPONENT);

		$uEditor    = Factory::getUser()->getParam('editor',  Factory::getConfig()->get('editor', 'none'));

		if ($uEditor === 'codemirror')
		{
			$this->editor = \Joomla\CMS\Editor\Editor::getInstance('none');
			$app->enqueueMessage(JText::_("JEV_CODEMIRROR_NOT_COMPATIBLE_EDITOR", "WARNING"));
		} else {
			$this->editor = \Joomla\CMS\Editor\Editor::getInstance($uEditor);
		}

		// Get the form && data
		$this->form = $this->get('TranslateForm');
		$this->original = $this->get("Original");
		$this->translation = $this->get("Translation");
		$lang = $input->getString("lang", "");

		$this->form->bind($this->original);
		$this->form->bind($this->translation);

		$this->form->setValue("trans_language",null,  $lang);
		$this->form->setValue("language",null,  $lang);
		$this->form->setValue("trans_evdet_id", null, $this->original["evdet_id"]);
		$this->form->setValue("ev_id", null, $input->getInt("ev_id", 0));

		// Event editing buttons
		if ($params->get('com_show_editor_buttons'))
		{
			$this->form->setFieldAttribute("trans_description", "hide", $params->get('com_editor_button_exceptions'));
		}
		else
		{
			$this->form->setFieldAttribute("trans_description", "buttons", "false");
		}
		$this->form->setFieldAttribute("description", "buttons", "false");

		$app->triggerEvent('onTranslateEvent', array(&$this->row, $lang));

		$this->addTranslationToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addTranslationToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::save('icalevent.savetranslation');
		JToolbarHelper::cancel('icalevent.close');

		$bar =  JToolBar::getInstance('toolbar');

		// Add a standard button
		$bar->appendButton('confirm', JText::_("JEV_DELETE_TRANSLATION_WARNING"),  'trash',  'JEV_DELETE', "icalevent.deletetranslation", false);

	}

	function csvimport($tpl = null)
	{

		$document = Factory::getDocument();
		$document->setTitle(JText::_('CSV_IMPORT'));

		// Set toolbar items for the page
		JToolbarHelper::title(JText::_('CSV_IMPORT'), 'jevents');

		JToolbarHelper::cancel('icalevent.list');

		

		HTMLHelper::_('behavior.tooltip');

	}

	protected function setCreatorLookup()
	{
		// If user is jevents can deleteall or has backend access then allow them to specify the creator
		$jevuser = JEVHelper::getAuthorisedUser();
		$user = Factory::getUser();

		//$access = JAccess::check($user->id, "core.deleteall", "com_jevents");
		$access = $user->authorise('core.admin', 'com_jevents') || $user->authorise('core.deleteall', 'com_jevents');

		$db = Factory::getDbo();
		if (($jevuser && $jevuser->candeleteall) || $access)
		{
			$params = ComponentHelper::getParams(JEV_COM_COMPONENT);
			$authorisedonly = $params->get("authorisedonly", 0);
			// if authorised only then load from database
			if ($authorisedonly)
			{
				$sql = "SELECT tl.*, ju.*  FROM #__jev_users AS tl ";
				$sql .= " LEFT JOIN #__users as ju ON tl.user_id=ju.id ";
				$sql .= " WHERE tl.cancreate=1";
				$sql .= " ORDER BY ju.name ASC";
				$db->setQuery($sql);
				$users = $db->loadObjectList();
			}
			else
			{
				$rules = JAccess::getAssetRules("com_jevents", true);
				$creatorgroups = $rules->getData();
				// need to merge the arrays because of stupid way Joomla checks super user permissions
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
				if (count($users)>200){
					return null;
				}
				$sql = "SELECT count(id) FROM #__users where id IN (" . implode(",", array_values($users)) . ") and block=0 ORDER BY name asc";
				$db->setQuery($sql);
				$userCount = $db->loadResult();

				if ($userCount<=200) {
					$sql = "SELECT * FROM #__users where id IN (" . implode(",", array_values($users)) . ") and block=0 ORDER BY name asc";
					$db->setQuery($sql);
					$users = $db->loadObjectList();
				}
				else {
					return null;
				}

			}

			// get list of creators - if fewer than 200
			if (count($users)>200) {
				return null;
			}

			$userOptions[] = HTMLHelper::_('select.option', '-1', JText::_('SELECT_USER'));
			foreach ($users as $user)
			{
				$userOptions[] = HTMLHelper::_('select.option', $user->id, $user->name . " ( " . $user->username . " )");
			}
			$creator = $this->row->created_by() > 0 ? $this->row->created_by() : (isset($jevuser) ? $jevuser->user_id : 0);
			$userlist = HTMLHelper::_('select.genericlist', $userOptions, 'jev_creatorid', 'class="gsl-select" size="1" ', 'value', 'text', $creator);

			$this->assignRef("users", $userlist);
		}

	}

	function toolbarButton($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true) {
		$bar = JToolBar::getInstance('toolbar');

		// Add a standard button
		$bar->appendButton('Jev', $icon, $alt, $task, $listSelect);
	}

	function toolbarConfirmButton($task = '', $msg = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
	{
		$bar =  JToolBar::getInstance('toolbar');

		// Add a standard button
		$bar->appendButton('Jevconfirm', $msg, $icon, $alt, $task, $listSelect, false, "document.adminForm.updaterepeats.value");

	}

	protected function translationLinks ($row) {
		if ($this->languages)
		{
			$translations = array();
			JevHtmlBootstrap::modal();
			JEVHelper::script('editpopupJQ.js','components/'.JEV_COM_COMPONENT.'/assets/js/');

			// Any existing translations ?  Do NOT use isset here since there is a magic __get that will return false if its not defined
			if ($row->evdet_id) {
				$db = Factory::getDbo();
				$db->setQuery("SELECT language FROM #__jevents_translation where evdet_id= " . $row->evdet_id);
				$translations = $db->loadColumn();
			}
			// test styling for existing translation
			//$translations[] = "cy-GB";
			?>
			<ul class="item-associations">
				<?php foreach ($this->languages as $id => $item) :

					$text = strtoupper($item->sef);
					$url = Route::_('index.php?option=com_jevents&task=icalevent.translate&evdet_id='.$row->evdet_id.'&ev_id='.$row->ev_id.'&pop=1&tmpl=component&lang=' . $item->lang_code);
					$img = HTMLHelper::_('image', 'mod_languages/' . $item->image . '.gif',
						$item->title,
						array('title' => $item->title),
						true
					);
					$url  = "javascript:jevEditTranslation('".$url ."', '". JText::sprintf("JEV_TRANSLATE_EVENT_TO" ,  addslashes($item->title),  array('jsSafe'=>true) ) . "'); ";
					$tooltipParts = array( 	$img,  $item->title);
					$item->link = HTMLHelper::_('tooltip', implode(' ', $tooltipParts), null, null, $text, $url, null, 'hasTooltip label label-association label-' . $item->sef .( in_array($item->lang_code, $translations)?" hastranslation":"" ));
					?>
					<li>
						<?php
						echo $item->link;
						?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php
		}
	}

}
