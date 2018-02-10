<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: icals.php 3548 2012-04-20 09:25:43Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C) 2008-2017 GWE Systems Ltd,2006-2008 JEvents Project Group
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controllerform');
jimport('joomla.filesystem.file');

use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;

class AdminIcalsController extends JControllerForm {

	var $_debug = false;
	var $queryModel = null;
	var $dataModel = null;

	/**
	 * Controler for the Ical Functions
	 * @param array		configuration
	 */
    function __construct($config = array())
    {
        parent::__construct($config);
        $this->registerTask('list',  'overview');
        $this->registerTask('new',  'newical');
        $this->registerTask('reload',  'save');
        $this->registerDefaultTask("overview");

		$cfg = JEVConfig::getInstance();
		$this->_debug = $cfg->get('jev_debug', 0);

		$this->dataModel = new JEventsDataModel("JEventsAdminDBModel");
		$this->queryModel =new JEventsDBModel($this->dataModel);

	}

	/**
	 * List Icals
	 *
	 */
	function overview( )
	{
		// get the view
		$this->view = $this->getView("icals","html");

		$this->_checkValidCategories();

		$option = JEV_COM_COMPONENT;
		$db	= JFactory::getDbo();


		$catid		= intval( JFactory::getApplication()->getUserStateFromRequest( "catid{$option}", 'catid', 0 ));
		$limit		= intval( JFactory::getApplication()->getUserStateFromRequest( "viewlistlimit", 'limit', JFactory::getApplication()->getCfg('list_limit',10) ));
		$limitstart = intval( JFactory::getApplication()->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 ));
		$search		= JFactory::getApplication()->getUserStateFromRequest( "search{$option}", 'search', '' );
		$search		= $db->escape( trim( strtolower( $search ) ) );
		$where		= array();

                // Trap cancelled edit and reset category ID.
                $icsid = intval(JRequest::getVar('icsid',-1));
                if ($icsid>-1){
                    $catid=0;
                }
		if( $search ){
			$where[] = "LOWER(icsf.label) LIKE '%$search%'";
		}
		if ($catid>0){
			$where[] ="catid = $catid";
		}
		// get the total number of records
		$query = "SELECT count(*)"
		. "\n FROM #__jevents_icsfile AS icsf"
		. ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' )
		;
		$db->setQuery( $query);
		$total = $db->loadResult();
		echo $db->getErrorMsg();

		if( $limitstart > $total ) {
			$limitstart = 0;
		}


		$query = "SELECT icsf.*, a.title as _groupname"
		. "\n FROM #__jevents_icsfile as icsf "
		. "\n LEFT JOIN #__viewlevels AS a ON a.id = icsf.access"
		. ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' )
		;

		$query .= "\n ORDER BY icsf.isdefault DESC, icsf.label ASC";
		if ($limit>0){
			$query .= "\n LIMIT $limitstart, $limit";
		}

		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		$catData = JEV_CommonFunctions::getCategoryData();

		for ($s=0;$s<count($rows);$s++) {
			$row =& $rows[$s];
			if (array_key_exists($row->catid,$catData)){
				$row->category = $catData[$row->catid]->name;
			}
			else {
				$row->category = "?";
			}
		}

		if( $this->_debug ){
			echo '[DEBUG]<br />';
			echo 'query:';
			echo '<pre>';
			echo $query;
			echo '-----------<br />';
			echo 'option "' . $option . '"<br />';
			echo '</pre>';
			//die( 'userbreak - mic ' );
		}

		if ($db->getErrorNum()) {
			echo $db->stderr();
			return false;
		}

		// get list of categories
		$attribs = 'class="inputbox" size="1" onchange="document.adminForm.submit();"';
		$clist = JEventsHTML::buildCategorySelect( $catid, $attribs, null, true,false, 0, 'catid');

		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit  );


		// Set the layout
		$this->view->setLayout('overview');

		$this->view->assign('option',JEV_COM_COMPONENT);
		$this->view->assign('rows',$rows);
		$this->view->assign('clist',$clist);
		$this->view->assign('search',$search);
		$this->view->assign('pageNav',$pageNav);

		$this->view->display();
	}

	function edit ($key = null, $urlVar = null) {
		$user = JFactory::getUser();
		if (!JEVHelper::isAdminUser()){
			$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=cpanel.cpanel", "Not Authorised - must be super admin" );
			$this->redirect();
			return;
		}

		// get the view
		$this->view = $this->getView("icals","html");

		$cid	= JRequest::getVar(	'cid',	array(0) );
		$cid = ArrayHelper::toInteger($cid);
		if (is_array($cid) && count($cid)>0) $editItem=$cid[0];
		else $editItem=0;

		$item =new stdClass;
		if ($editItem!=null){
			$db	= JFactory::getDbo();
			$query = "SELECT * FROM #__jevents_icsfile as ics where ics.ics_id=$editItem";

			$db->setQuery( $query );
			$item = null;
			$item = $db->loadObject();
		}


		// Set the layout
		$this->view->setLayout('edit');

		// for Admin interface only

		$this->view->assign('with_unpublished_cat',JFactory::getApplication()->isAdmin());

		$this->view->assign('editItem',$item);
		$this->view->assign('option',JEV_COM_COMPONENT);

		$this->view->display();

	}

    function reloadall(){

		@set_time_limit(1800);

		if (JFactory::getApplication()->isAdmin()){
			$redirect_task = "icals.list";
		}
		else
		{

			$redirect_task = "day.listevents";
		}

        $query = "SELECT icsf.* FROM #__jevents_icsfile as icsf";
		$db	= JFactory::getDbo();
		$db->setQuery($query);
		$allICS = $db->loadObjectList();

        foreach ($allICS as $currentICS){

	        //only update iCals from url
	        if ($currentICS->icaltype == '0' && $currentICS->autorefresh == 1){

		        JRequest::setVar('icsid', $currentICS->ics_id);
		        $this->save();
	        }
        }

	    $user = JFactory::getUser();
	    $guest = (int) $user->get('guest');

	    $link = "index.php?option=".JEV_COM_COMPONENT."&task=$redirect_task";
	    $message = JText::_( 'ICS_ALL_FILES_IMPORTED' );

	    if ($guest === 1) {
		    $this->setRedirect( $link );
	    } else {
		    $this->setRedirect( $link, $message);
	    }

		$this->redirect();
    }

	function save($key = null, $urlVar = null){

		$app    = JFactory::getApplication();
		$jinput = $app->input;
		$task   = $jinput->getCmd("task");
		// Check for request forgeries
		if ($task !== "icals.reload" && $task !== "icals.reloadall"){
			JSession::checkToken() or jexit( 'Invalid Token' );
		}

		$user = JFactory::getUser();
		$guest = (int) $user->get('guest');

		$authorised = false;

		if (JFactory::getApplication()->isClient('administrator')){
			$redirect_task = "icals.list";
		}
		else {
			$redirect_task="day.listevents";
		}

		// clean this up later - this is a quick fix for frontend reloading
		$autorefresh = 0;
		$icsid = $jinput->getInt('icsid', 0);

		if ($icsid>0){
			$query = "SELECT icsf.* FROM #__jevents_icsfile as icsf WHERE ics_id = $icsid";
			$db	= JFactory::getDbo();
			$db->setQuery($query);
			$currentICS = $db->loadObjectList();

			if (count($currentICS)>0){
				$currentICS= $currentICS[0];
				if ($currentICS->autorefresh){
					$authorised = true;
					$autorefresh=1;
				}
			}
		}

		$user = JFactory::getUser();

		if (!($authorised || JEVHelper::isAdminUser($user))) {
			$this->setRedirect( "index.php?option=" . JEV_COM_COMPONENT . "&task=$redirect_task", "Not Authorised - must be super admin" );
			$this->redirect();
			return;
		}
		$cid	= JRequest::getVar(	'cid',	array(0) );
		$cid = ArrayHelper::toInteger($cid);
		if (is_array($cid) && count($cid)>0) {
			$cid=$cid[0];
		} else {
			$cid=0;
		}

		$db	= JFactory::getDbo();

		// include iCal files
		if ($icsid > 0 || $cid != 0){

			$icsid = ($icsid > 0) ? $icsid : $cid;
			$query = "SELECT icsf.* FROM #__jevents_icsfile as icsf WHERE ics_id = $icsid";
			$db->setQuery($query);
			$currentICS = $db->loadObjectList();
			if (count($currentICS)>0){
				$currentICS= $currentICS[0];
				if ($currentICS->autorefresh){
					$authorised = true;
					$autorefresh=1;
				}

			}
			else {
				$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=$redirect_task", "Invalid iCal Details");
				$this->redirect();
			}

			$catid = $jinput->getInt('catid', $currentICS->catid);

			if ($catid<=0 && $currentICS->catid>0){
				$catid = (int) $currentICS->catid;
			}

			$access = (int) $jinput->get('access', $currentICS->access);

			if ($access<0 && $currentICS->access>=0){
				$access = (int) $currentICS->access;
			}

            $icsLabel = $jinput->getString('icsLabel', $currentICS->label);
            $isdefault = $jinput->getInt('isdefault', $currentICS->isdefault);
            $overlaps = $jinput->getInt('overlaps', $currentICS->overlaps);
            $autorefresh = $jinput->getInt('autorefresh', $autorefresh);
            $ignoreembedcat = $jinput->getInt('ignoreembedcat', $currentICS->ignoreembedcat);

			if ((int) $currentICS->icaltype === 3) {

				$csvData = $this->generateFacebookData($currentICS, $catid);
				$ics = new iCalICSFile($db);
				$ics->load($icsid);
				$icsFile = $ics->newICSFileFromString($csvData, $icsid, $catid);
				$icsFile->icaltype = 3;
				$icsFile->refreshed = date('Y-m-d H-i-s');
				$icsFile->store();

				$this->setRedirect( "index.php?option=" . JEV_COM_COMPONENT . "&task=$redirect_task", JText::_( 'FACEBOOK_FEED_REFRESHED' ));
				$this->redirect();
			}

			if (($icsLabel === '' || $task === "icals.reload") && JString::strlen($currentICS->label)>=0){
				$icsLabel = $currentICS->label;
			}

			// This is a native iCal - so we are only updating identifiers etc
			if ($currentICS->icaltype == 2 || $currentICS->icaltype == 3){
				$ics = new iCalICSFile($db);
				$ics->load($icsid);
				$ics->catid     = $catid;
				$ics->isdefault = $isdefault;
				$ics->overlaps  = $overlaps;
				$ics->access    = $access;
				$ics->label     = $icsLabel;

				// TODO update access and state
				$ics->updateDetails();
				$this->setRedirect( "index.php?option=" . JEV_COM_COMPONENT . "&task=$redirect_task", JText::_( 'ICS_SAVED' ));
				$this->redirect();
			}

			$state = 1;
			if (JString::strlen($currentICS->srcURL)==0) {
				echo "Can only reload URL based subscriptions";
				return;
			}

			$uploadURL = $currentICS->srcURL;

		}
		else {
			$catid = JRequest::getInt('catid',0);
			$ignoreembedcat = JRequest::getInt('ignoreembedcat',0);

			// Should come from the form or existing item
			$access = JRequest::getInt('access',0);
			$state = 1;
			$uploadURL = JRequest::getVar('uploadURL','' );
			$icsLabel = JRequest::getString('icsLabel','' );
		}

		if ($catid==0){

			// Paranoia, should not be here, validation is done by java script
			JFactory::getApplication()->enqueueMessage('Fatal Error - ' . JText::_('JEV_E_WARNCAT') , 'error');

			$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=$redirect_task",  JText::_('JEV_E_WARNCAT'));
			$this->redirect();
			return;
		}


		// I need a better check and expiry information etc.
		if (JString::strlen($uploadURL)>0){
			$icsFile = iCalICSFile::newICSFileFromURL($uploadURL, $icsid, $catid, $access, $state, $icsLabel, $autorefresh, $ignoreembedcat);
		}
		else if (isset($_FILES['upload']) && is_array($_FILES['upload']) ) {
			$file 			= $_FILES['upload'];
			if ($file['size'] == 0 ){//|| !($file['type']=="text/calendar" || $file['type']=="application/octet-stream")){
				JFactory::getApplication()->enqueueMessage(JText::_('JEV_EMPTY_FILE_UPLOAD'), 'warning');
				$icsFile = false;
			}
			else {
				$icsFile = iCalICSFile::newICSFileFromFile($file, $icsid, $catid, $access, $state, $icsLabel);
			}
		}

		$message = '';
		if ($icsFile !== false) {
			// preserve ownership
			if (isset($currentICS) && $currentICS->created_by>0 ){
                            $icsFile->created_by = $currentICS->created_by;
                        }
                        else $icsFile->created_by = JRequest::getInt("created_by",0);

			$icsFileid = $icsFile->store();
			$message = JText::_( 'ICS_FILE_IMPORTED' );
		}
		if (JRequest::getCmd("task") !== "icals.reloadall")
		{
			$link = "index.php?option=" . JEV_COM_COMPONENT . "&task=$redirect_task";

			if ($guest === 1) {
				$this->setRedirect($link);
			} else
			{
				$this->setRedirect($link, $message);
			}
			$this->redirect();
		}
	}

	/**
	 * This just updates the details not the content of the calendar
	 *
	 */
	function savedetails(){
		$user = JFactory::getUser();
		$authorised = false;
		// Check for request forgeries
		JSession::checkToken() or jexit( 'Invalid Token' );

		$app    = JFactory::getApplication();
		$jinput = $app->input;

		$redirect_task = "month.calendar";

		$user = JFactory::getUser();

		if ($app->isClient('administrator')){
			$redirect_task = "icals.list";
		}

		if (!($authorised || JEVHelper::isAdminUser($user))) {
			$this->setRedirect( "index.php?option=" . JEV_COM_COMPONENT . "&task=$redirect_task", "Not Authorised - must be super admin" ); //TODO add language string.
			$this->redirect();
			return;
		}

		$icsid = intval(JRequest::getVar('icsid',0));
		$cid	= JRequest::getVar(	'cid',	array(0) );
		$cid = ArrayHelper::toInteger($cid);
		if (is_array($cid) && count($cid)>0) {
			$cid=$cid[0];
		} else {
			$cid=0;
		}

		$db	= JFactory::getDbo();

		// include iCal files
		if ($icsid > 0 || $cid != 0){
			$icsid = ($icsid > 0) ? $icsid : $cid;

			$query = "SELECT icsf.* FROM #__jevents_icsfile as icsf WHERE ics_id=$icsid";
			$db->setQuery($query);
			$currentICS = $db->loadObjectList();

			if (count($currentICS)>0){
				$currentICS= $currentICS[0];
			}
			else {
				$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=$redirect_task", "Invalid Ical Details");
				$this->redirect();
			}

			$catid = JRequest::getInt('catid',$currentICS->catid);
			if ($catid<=0 && $currentICS->catid>0){
				$catid = intval($currentICS->catid);
			}
			$access = intval(JRequest::getVar('access',$currentICS->access));
			if ($access<0 && $currentICS->access>=0){
				$access = intval($currentICS->access);
			}
			$state = intval(JRequest::getVar('state',$currentICS->state));
			if ($state<0 && $currentICS->state>=0){
				$state = intval($currentICS->state);
			}
			$icsLabel = JRequest::getVar('icsLabel',$currentICS->label );
			if ($icsLabel=="" && JString::strlen($currentICS->icsLabel)>=0){
				$icsLabel = $currentICS->icsLabel;
			}
			$uploadURL = JRequest::getVar('uploadURL',$currentICS->srcURL );
			if ($uploadURL=="" && JString::strlen($currentICS->srcURL)>=0){
				$uploadURL = $currentICS->srcURL;
			}
			$isdefault = JRequest::getInt('isdefault',$currentICS->isdefault);
			$overlaps = JRequest::getInt('overlaps',$currentICS->overlaps);
			$autorefesh = JRequest::getInt('autorefresh',$currentICS->autorefresh);
			$ignoreembed = JRequest::getInt('ignoreembedcat',$currentICS->ignoreembedcat);

			// We are only updating identifiers etc
			$ics = new iCalICSFile($db);
			$ics->load($icsid);
			$ics->catid = $catid;
			$ics->isdefault = $isdefault;
			$ics->overlaps = $overlaps;
			$ics->created_by = JRequest::getInt("created_by",$currentICS->created_by);
			$ics->state = $state;
			$ics->access = $access;
			$ics->label = $icsLabel;
			$ics->srcURL =  $uploadURL;
			$ics->ignoreembedcat = $ignoreembed;
			$ics->params = json_encode(array(
				'facebookapp_id' => $jinput->get('facebookapp_id', ''),
				'facebookapp_token' => $jinput->get('facebookapp_token', ''),
				'facebookapp_secret' => $jinput->get('facebookapp_secret', ''),
				'facebookapp_feed_id' => $jinput->getString('facebookapp_feed_id', ''),
				'import_state' => $jinput->getString('import_state', 1),
				'replaceEventTitle' => $jinput->getString('replaceEventTitle', 1),
				'replaceTimeZone' => $jinput->getString('replaceTimeZone', 1)
			));

			// TODO update access and state
			$ics->updateDetails();
			$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=$redirect_task", JText::_( 'ICS_SAVED' ));
			$this->redirect();
		}
	}

	function publish(){
		$cid = JRequest::getVar(	'cid',	array(0) );
		$cid = ArrayHelper::toInteger($cid);
		$this->toggleICalPublish($cid,1);
	}

	function unpublish(){
		$cid = JRequest::getVar(	'cid',	array(0) );
		$cid = ArrayHelper::toInteger($cid);
		$this->toggleICalPublish($cid,0);
	}

	function toggleICalPublish($cid,$newstate){
		$user = JFactory::getUser();
		if (!JEVHelper::isAdminUser($user)) {
			$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=cpanel.cpanel", "Not Authorised - must be super admin" );
			$this->redirect();
			return;
		}

		$db	= JFactory::getDbo();
		foreach ($cid as $id) {
			$sql = "UPDATE #__jevents_icsfile SET state=$newstate where ics_id='".$id."'";
			$db->setQuery($sql);
			$db->execute();
		}
		$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=icals.list", JText::_('JEV_ADMIN_ICALSUPDATED'));
		$this->redirect();
	}

	function autorefresh(){
		$cid = JRequest::getVar(	'cid',	array(0) );
		$cid = ArrayHelper::toInteger($cid);
		$this->toggleAutorefresh($cid,1);
	}

	function noautorefresh(){
		$cid = JRequest::getVar(	'cid',	array(0) );
		$cid = ArrayHelper::toInteger($cid);
		$this->toggleAutorefresh($cid,0);
	}

	function toggleAutorefresh($cid,$newstate){
		$user = JFactory::getUser();
		if (!JEVHelper::isAdminUser($user)) {
			$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=cpanel.cpanel", "Not Authorised - must be super admin" );
			$this->redirect();
			return;
		}

		$db	= JFactory::getDbo();
		foreach ($cid as $id) {
			$sql = "UPDATE #__jevents_icsfile SET autorefresh=$newstate where ics_id='".$id."'";
			$db->setQuery($sql);
			$db->execute();
		}
		$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=icals.list", JText::_('JEV_ADMIN_ICALSUPDATED'));
		$this->redirect();
	}

	function isdefault(){
		$cid = JRequest::getVar(	'cid',	array(0) );
		$cid = ArrayHelper::toInteger($cid);
		$this->toggleDefault($cid,1);
	}

	function notdefault(){
		$cid = JRequest::getVar(	'cid',	array(0) );
		$cid = ArrayHelper::toInteger($cid);
		$this->toggleDefault($cid,0);
	}

	function toggleDefault($cid,$newstate){
		$user = JFactory::getUser();
		if (!JEVHelper::isAdminUser($user)) {
			$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=cpanel.cpanel", "Not Authorised - must be super admin" );
			$this->redirect();
			return;
		}

		$db	= JFactory::getDbo();
		// set all to not default first
		$sql = "UPDATE #__jevents_icsfile SET isdefault=0";
		$db->setQuery($sql);
		$db->execute();

		$id = $cid[0];
		$sql = "UPDATE #__jevents_icsfile SET isdefault=$newstate where ics_id='".$id."'";
		$db->setQuery($sql);
		$db->execute();
		$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=icals.list", JText::_('JEV_ADMIN_ICALSUPDATED'));
		$this->redirect();
	}

	/**
 	* create new ICAL from scratch
 	*/
	function newical() {

		// Check for request forgeries
		JSession::checkToken() or jexit( 'Invalid Token' );
		$app    = JFactory::getApplication();
		$jinput = $app->input;

		// Include iCal files
		$catid = (int) $jinput->get('catid', 0);

		// Should come from the form or existing item
		$access = $jinput->getInt('access',0);
		$state = 1;
		$icsLabel = $jinput->getString('icsLabel','');

		//Map the params on in.
		$jinput->set('params', array(
				'facebookapp_id' => $jinput->get('facebookapp_id', ''),
				'facebookapp_token' => $jinput->get('facebookapp_token', ''),
				'facebookapp_secret' => $jinput->get('facebookapp_secret', ''),
				'facebookapp_feed_id' => $jinput->getString('facebookapp_feed_id', ''),
				'import_state' => $jinput->getString('import_state', 1),
				'replaceEventTitle' => $jinput->getString('replaceEventTitle', 1),
				'replaceTimeZone' => $jinput->getString('replaceTimeZone', 1)
			));


		if ($catid === 0){

			// Paranoia, should not be here, validation is done by java script
			$app->enqueueMessage('Fatal Error - ' . JText::_("JEV_E_WARNCAT"), 'error');

			// Set option variable.
			$option = JEV_COM_COMPONENT;
			$app->redirect( 'index.php?option=' . $option);
			return;
		}

        // Check for duplicates
        $db = JFactory::getDbo();
        $query = "SELECT icsf.* FROM #__jevents_icsfile as icsf WHERE label=".$db->quote($icsLabel);
        $db->setQuery($query);
        $existing = $db->loadObject();

        if ($existing){

            $app->enqueueMessage(JText::_('JEV_DUPLICATE_CALENDAR') , 'error');
            $this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=icals.edit");
            $this->redirect();
            return;

        }

		$icsid = 0;
		$icsFile = iCalICSFile::editICalendar($icsid, $catid, $access, $state, $icsLabel);
		$icsFile->created_by = $jinput->getInt("created_by",0);
		// Map Facebook SDK
		$icsFile->params = json_encode(array(
			'facebookapp_id' => $jinput->get('facebookapp_id', ''),
			'facebookapp_token' => $jinput->get('facebookapp_token', ''),
			'facebookapp_secret' => $jinput->get('facebookapp_secret', ''),
			'facebookapp_feed_id' => $jinput->getString('facebookapp_feed_id', ''),
			'import_state' => $jinput->getString('import_state', 1),
			'replaceEventTitle' => $jinput->getString('replaceEventTitle', 1),
			'replaceTimeZone' => $jinput->getString('replaceTimeZone', 1)
		));

		if ($jinput->get('facebookapp_id', '') !== '') {
			$icsFile->icaltype = 3;
		}

		$icsFileid = $icsFile->store();

		$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=icals.list", JText::_( 'ICAL_FILE_CREATED' ));
		$this->redirect();
	}


	function delete(){

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid	= JRequest::getVar(	'cid',	array(0) );
		$cid = ArrayHelper::toInteger($cid);

		$db	= JFactory::getDbo();

		// check this won't create orphan events
		$query = "SELECT ev_id FROM #__jevents_vevent WHERE icsid in (".implode(",",$cid).")";
		$db->setQuery( $query );
		$kids = $db->loadObjectList();
		if (count($kids)>0){
			$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=icals.list", JText::_("DELETE_CREATES_ORPHAN_EVENTS") );
			$this->redirect();
			return;
		}

		$icsids = $this->_deleteICal($cid);
		$query = "DELETE FROM #__jevents_icsfile WHERE ics_id IN ($icsids)";
		$db->setQuery( $query);
		$db->execute();

		$this->setRedirect( "index.php?option=".JEV_COM_COMPONENT."&task=icals.list", "ICal deleted" );
		$this->redirect();
	}

	function _deleteICal($cid){
		$db	= JFactory::getDbo();
		$icsids = implode(",",$cid);

		$query = "SELECT ev_id FROM #__jevents_vevent WHERE icsid IN ($icsids)";
		$db->setQuery( $query);
		$veventids = $db->loadColumn();
		$veventidstring = implode(",",$veventids);

		if ($veventidstring) {
			// TODO the ruccurences should take care of all of these??
			// This would fail if all recurrances have been 'adjusted'
			$query = "SELECT DISTINCT (eventdetail_id) FROM #__jevents_repetition WHERE eventid IN ($veventidstring)";
			$db->setQuery( $query);
			$detailids = $db->loadColumn();
			$detailidstring = implode(",",$detailids);

			$query = "DELETE FROM #__jevents_rrule WHERE eventid IN ($veventidstring)";
			$db->setQuery( $query);
			$db->execute();

			$query = "DELETE FROM #__jevents_repetition WHERE eventid IN ($veventidstring)";
			$db->setQuery( $query);
			$db->execute();

			if ($detailidstring) {
				$query = "DELETE FROM #__jevents_vevdetail WHERE evdet_id IN ($detailidstring)";
				$db->setQuery( $query);
				$db->execute();
			}
		}

		if ($icsids) {
			$query = "DELETE FROM #__jevents_vevent WHERE icsid IN ($icsids)";
			$db->setQuery( $query);
			$db->execute();
		}

		return $icsids;
	}


	function _checkValidCategories(){
		// TODO switch this after migration
		$component_name = "com_jevents";

		$db	= JFactory::getDbo();
		$query = "SELECT COUNT(*) AS count FROM #__categories WHERE extension = '$component_name' AND `published` = 1;";  // RSH 9/28/10 added check for valid published, J!1.6 sets deleted categoris to published = -2
		$db->setQuery($query);
		$count = (int) $db->loadResult();
		if ($count <= 0){
			// RSH 9/28/10 - Added check for J!1.6 to use different URL for reroute
			$redirectURL = "index.php?option=com_categories&extension=" . JEV_COM_COMPONENT;
			$this->setRedirect($redirectURL, "You must first create at least one category");
			$this->redirect();
		}
	}

	function generateFacebookData($ical, $catid)
	{
		$ical_params = json_decode($ical->params);
		$app_id      = $ical_params->facebookapp_id;
		$app_token   = $ical_params->facebookapp_token;
		$app_secret  = $ical_params->facebookapp_secret;
		$cats        = JEV_CommonFunctions::getCategoryData();
		$comParams = JComponentHelper::getParams(JEV_COM_COMPONENT);


		$feed_ids   = explode(',', str_replace(' ', '', $ical_params->facebookapp_feed_id));

		if (array_key_exists($catid, $cats))
		{
			$cat = $cats[$catid];
		}
		else
		{
			die('Error, no category set?');
		}

		// Include the required dependencies.
		require_once JPATH_ADMINISTRATOR . '/components/com_jevents/vendor/autoload.php';

		//Build the Data

		$csvData = '';

		$f = 0;
		foreach ($feed_ids as $feed_id)
		{

			// Initialize the Facebook PHP SDK v5.
			$fb = new Facebook\Facebook([
				'app_id'                => $app_id,
				'app_secret'            => $app_secret,
				'default_graph_version' => 'v2.10',
			]);

			$fields = 'id,name,category,description,cover,place,start_time,end_time,timezone';
			// Lets set the start date we want to get events from.
            $sinceDate = date('d-m-Y', strtotime("-1 week"));
			$res  = $fb->get('/' . $feed_id . '/events?since=' . $sinceDate . '&limit=1000&fields=' . $fields, $app_token);
			$data = $res->getDecodedBody();


			// Set an import file, handy for debugging etc.
			$filename = 'jevents_fb_import.csv';
			$fh = fopen(JPATH_SITE . '/tmp/' . $filename, 'wb+');

			if($f === 0)
			{
				// Only Insert for the first feed.
				$csvData = '"CATEGORIES","SUMMARY","LOCATION","GEO","DESCRIPTION","CONTACT","X-EXTRAINFO","DTSTART","DTEND","TIMEZONE","RRULE","UID","PUBLISHED","upload_image1","upload_image1_title"';
			}

			$filesImages = false;

			if(JFile::exists(JPATH_SITE . '/plugins/jevents/jevfiles/jevfiles.xml')) {
				$filesImages        = true;
				$filesImagesPlugin  = JPluginHelper::getPlugin('jevents', 'jevfiles');
				$filesImagesParams    = new JRegistry($filesImagesPlugin->params);
				$imagePath          = JPATH_SITE . '/images/' . $filesImagesParams->get('folder', 'jevents') . '/';
				$thumbWidth         = $filesImagesParams->get('thumbw', 120);
				$thumbHeight         = $filesImagesParams->get('thumbh', 90);
			}

			foreach ($data['data'] as $event)
			{

				// New Line
				$csvData .= "\r\n";

				$csvRow = array();
				if (isset($event['category']) && in_array($event['category'], $cats))
				{
					$csvRow['cat'] = '"' . $event['category'] . '"';
				}
				else
				{
					$csvRow['cat'] = '"' . $cats[$catid]->title . '"';
				}

				$eventTitle = $event['name'];
				if($ical_params->replaceEventTitle) {
					$eventTitle = $ical->label;
				}
				$csvRow['summary'] = '"' . $eventTitle . '"';

				$location           = '';
				$csvRow['location'] = '""';
				$loc_segments_count = isset($event['place']) ? count($event['place']) : 0;
				if (isset($event['place']['location']) && is_array($event['place']['location']))
				{
					//Get rid of the location id for now.
					unset($event['place']['id']);

					$loc_details = $event['place']['location'];
					unset($event['place']['location'], $loc_details['id']);
					//Manipulate the data...
					$event['place'] = array_merge($loc_details, $event['place']);
				}

				$i   = 0;
				$geolat = false;
				$geolon = false;

				$lcd = array();

				if (isset($event['place']) && is_array($event['place']))
				{
					foreach ($event['place'] as $key => $loc_row)
					{
						if ($key === 'latitude')
						{
							$geolat .= $loc_row . ';';
							unset($event['place'][$key]);
							continue;
						}
						if ($key === 'longitude')
						{
							$geolon .= $loc_row;
							unset($event['place'][$key]);
							continue;
						}

						if ($key === 'name') {
							$lcd[1] = $loc_row;
						}

						if ($key === 'street') {
							$lcd[2] = $loc_row;
						}
						if ($key === 'town') {
							$lcd[3] = $loc_row;
						}
						if ($key === 'state') {
							$lcd[4] = $loc_row;
						}
						if ($key === 'zip') {
							$lcd[5] = $loc_row;
						}
					}
				}
				else
				{
					$location = '';
				}
				$lcd_cnt = count($lcd);
				if ($lcd_cnt > 0)
				{
					$location = '';
					$x = 1;

					ksort($lcd);
					//var_dump($lcd);
					foreach ($lcd as $lkey => $locd) {
						$location .= $locd;
							$location .= ',';
						$x++;
					}
					$csvRow['location'] = '"' . substr($location, 0, -1) . '"';
				}

				$csvRow['geo'] = '""';

				if ($geolat)
				{
					$csvRow['geo'] = '"' . $geolat . $geolon . '"';
				}

				// Description

				$csvRow['description'] = '"' . htmlentities($event['description']) . '"';
				$csvRow['contact'] = '""';
				$csvRow['X-EXTRAINFO'] = '""';

				if (!empty($ical_params->replaceTimeZone)) {
					$timezone = $comParams->get('icaltimezone');
					$startTime = str_replace('+', '', strstr($event['start_time'], '+', true));
					$endTime =  str_replace('+', '', strstr($event['end_time'], '+', true));
				} else {
					$startTime = $event['start_time'];
					$endTime = $event['end_time'];
					$timezone = $event['timezone'];
				}

				// Times:

				$csvRow['DTSTART'] = '"' . JevDate::strftime("%Y%m%dT%H%M%S", strtotime($startTime), $timezone) . '"';
				if (isset($event['end_time']))
				{
					$csvRow['DTEND'] =  '"' . JevDate::strftime("%Y%m%dT%H%M%S", strtotime($endTime), $timezone) . '"';
				}
				else
				{
					$csvRow['DTEND'] = '"' . JevDate::strftime('%Y-%m-%d 23:59:59', strtotime($startTime), $timezone) . '"';
				}

				$csvRow['timezone'] = '"' . $timezone . '"';

				$csvRow['rrule']    = '""';
				$csvRow['uid']      = '"FB' . $event['id'] . '"';
				$csvRow['PUBLISHED']    = !isset($ical_params->import_state) ? 1 : $ical_params->import_state;

				if($filesImages && isset($event['cover']))
				{
					// Download the image!git fe
					if (!JFile::exists(JPATH_SITE . $imagePath . 'FB' . $event['id'] . '.jpg'))
					{
						copy($event['cover']['source'], $imagePath . '/originals/FB' . $event['id'] . '.jpg');
						$image = new JImage($imagePath . '/originals/FB' . $event['id'] . '.jpg');
						$image = $image->resize($thumbWidth, $thumbHeight, true, JImage::SCALE_INSIDE);
						$image->toFile($imagePath . '/thumbnails/thumb_FB' . $event['id'] . '.jpg', '.jpg', array("quality" => 90));
					}

					$csvRow['upload_image1']       = '"' . 'FB' . $event['id'] . '.jpg' . '"';
					$csvRow['upload_image1_title'] = '"' . $event['name'] . '"';
				} else {

					$csvRow['upload_image1']       = '""';
					$csvRow['upload_image1_title'] = '""';
				}

				// End csvRow array and set it to the Data var
				$csvData .= implode(',', $csvRow);
			}

			$f++;
		}

		JFile::write(PATH_SITE . '/tmp/' . $filename, $csvData);

		echo $csvData;die;
		return $csvData;
		}
}
