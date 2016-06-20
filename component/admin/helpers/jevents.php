<?php

// No direct access
defined('_JEXEC') or die;

JLoader::register('JevJoomlaVersion', JPATH_ADMINISTRATOR . "/components/com_jevents/libraries/version.php");

/**
 * JEvents component helper.
 *
 * @package        Jevents
 * @since        1.6
 */
class JEventsHelper
{

    public static $extention = 'com_jevents';

    /**
     * Configure the Linkbar.
     *
     * @param    string    The name of the active view.
     */
    public static function addSubmenu($vName = "")
    {
        $jinput = JFactory::getApplication()->input;

        $task = $jinput->getCmd("task", "cpanel.cpanel");
        $option = $jinput->getCmd("option", "com_categories");

        if ($option == 'com_categories') {
            $doc = JFactory::getDocument();
            if (!JevJoomlaVersion::isCompatible("3.0")) {
                $hide_options = '#toolbar-popup-options {'
                    . 'display:none;'
                    . '}';
            } else {
                $hide_options = '#toolbar-options {'
                    . 'display:none;'
                    . '}';
            }
            $doc->addStyleDeclaration($hide_options);
            // Category styling
            $style = <<<STYLE
#categoryList td.center a {
    border:none;
}
STYLE;
            JFactory::getDbo()->setQuery("SELECT * FROM #__categories WHERE extension='com_jevents'");
            $categories = JFactory::getDbo()->loadObjectList('id');
            foreach ($categories as $cat) {
                $catparams = new JRegistry($cat->params);
                if ($catparams->get("catcolour")) {
                    $style .= "tr[item-id='$cat->id'] a {  border-left:solid 3px  " . $catparams->get("catcolour") . ";padding-left:5px;}\n";
                }
            }

            $doc->addStyleDeclaration($style);
        }

        if ($vName == "") {
            $vName = $task;
        }
        // could be called from categories component
        JLoader::register('JEVHelper', JPATH_SITE . "/components/com_jevents/libraries/helper.php");

                JHtmlSidebar::addEntry(
                                JText::_('CONTROL_PANEL'), 'index.php?option=com_jevents', $vName == 'cpanel.cpanel'
                );

                JHtmlSidebar::addEntry(
                                JText::_('JEV_ADMIN_ICAL_EVENTS'), 'index.php?option=com_jevents&task=icalevent.list', $vName == 'icalevent.list'
                );

                if (JEVHelper::isAdminUser())
                {
                        JHtmlSidebar::addEntry(
                                        JText::_('JEV_ADMIN_ICAL_SUBSCRIPTIONS'), 'index.php?option=com_jevents&task=icals.list', $vName == 'icals.list'
                        );
                }
                JHtmlSidebar::addEntry(
                                JText::_('JEV_INSTAL_CATS'), "index.php?option=com_categories&extension=com_jevents", $vName == 'categories'
                );
                if (JEVHelper::isAdminUser())
                {
                        $params = JComponentHelper::getParams(JEV_COM_COMPONENT);
                        if ($params->get("authorisedonly", 0)) {
                            JHtmlSidebar::addEntry(
                                        JText::_('JEV_MANAGE_USERS'), 'index.php?option=com_jevents&task=user.list', $vName == 'user.list'
                            );
                        }
                        JHtmlSidebar::addEntry(
                                        JText::_('JEV_INSTAL_CONFIG'), 'index.php?option=com_jevents&task=params.edit', $vName == 'params.edit'
                        );
                        JHtmlSidebar::addEntry(
                                        JText::_('JEV_LAYOUT_DEFAULTS'), 'index.php?option=com_jevents&task=defaults.list', in_array($vName, array('defaults.list', 'defaults.overview'))
                        );

                        //Support & CSS Customs should only be for Admins really.
                        JHtmlSidebar::addEntry(
                                        JText::_('SUPPORT_INFO'), 'index.php?option=com_jevents&task=cpanel.support', $vName == 'cpanel.support'
                        );
                        JHtmlSidebar::addEntry(
                                        JText::_('JEV_CUSTOM_CSS'), 'index.php?option=com_jevents&task=cpanel.custom_css', $vName == 'cpanel.custom_css'
                        );
                        
                        // Links to addons
                        // Managed Locations
                        $db = JFactory::getDbo ();
                        $db->setQuery ( "SELECT enabled FROM #__extensions WHERE element = 'com_jevlocations' AND type='component' " );
                        $is_enabled = $db->loadResult ();
                        if ($is_enabled) {
                                $link = "index.php?option=com_jevlocations";
                                JFactory::getLanguage()->load("com_jevlocations", JPATH_ADMINISTRATOR);
                                JHtmlSidebar::addEntry(
                                        JText::_('COM_JEVLOCATIONS'), $link, $vName == 'cpanel.managed_locations'
                                );
                        }
                        
                        // Managed People
                        $db = JFactory::getDbo ();
                        $db->setQuery ( "SELECT enabled FROM #__extensions WHERE element = 'com_jevpeople' AND type='component' " );
                        $is_enabled = $db->loadResult ();
                        if ($is_enabled) {
                                $link = "index.php?option=com_jevpeople";
                                JFactory::getLanguage()->load("com_jevpeople", JPATH_ADMINISTRATOR);
                                JHtmlSidebar::addEntry(
                                        JText::_('COM_JEVPEOPLE'), $link, $vName == 'cpanel.managed_people'
                                );
                                
                        }
                        // RSVP Pro
                        $db = JFactory::getDbo ();
                        $db->setQuery ( "SELECT enabled FROM #__extensions WHERE element = 'com_rsvppro' AND type='component' " );
                        $is_enabled = $db->loadResult ();
                        if ($is_enabled) {
                                $link = "index.php?option=com_rsvppro";
                                JFactory::getLanguage()->load("com_rsvppro", JPATH_ADMINISTRATOR);
                                JHtmlSidebar::addEntry(
                                        JText::_('COM_RSVPPRO'), $link, $vName == 'cpanel.rsvppro'
                                );
                                
                        }
                        // Custom Fields				
                        $db = JFactory::getDbo ();
                        $db->setQuery ( "SELECT * FROM #__extensions WHERE element = 'jevcustomfields' AND type='plugin' AND folder='jevents' " );
                        $extension = $db->loadObject();
                        // Stop if user is not authorised to manage JEvents
                        if ($extension && $extension->enabled && JEVHelper::isAdminUser()) {
                                $manifestCache = json_decode($extension->manifest_cache);
                                if (version_compare($manifestCache->version, "3.5.0RC", "ge") )
                                {
                                        $link = "index.php?option=com_jevents&task=plugin.jev_customfields.overview";
                                        JFactory::getLanguage()->load("plg_jevents_jevcustomfields", JPATH_ADMINISTRATOR);
                                        JHtmlSidebar::addEntry(
                                            JText::_('JEV_CUSTOM_FIELDS'), $link, $vName == 'plugin.jev_customfields.overview'
                                        );                                        
                                }
                        }
                        
                }
                
                

    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @param    int        The category ID.
     * @param    int        The article ID.
     *
     * @return    JObject
     */
    public static function getActions($categoryId = 0, $articleId = 0)
    {
        $user = JFactory::getUser();
        $result = new JObject;

        if (empty($articleId) && empty($categoryId)) {
            $assetName = 'com_jevents';
        } else if (empty($articleId)) {
            $assetName = 'com_jevents.category.' . (int)$categoryId;
        } else {
            $assetName = 'com_jevents.article.' . (int)$articleId;
        }

        $actions = array(
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
        );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;

    }

    public static function addAdminSidebar($components = array(), $params = array())
    {

        // Links to addons

        // Managed Locations

        $db = JFactory::getDbo ();
        $db->setQuery ( "SELECT enabled FROM #__extensions WHERE element = 'com_jevlocations' AND type='component' " );
        $is_enabled = $db->loadResult();

        if ($is_enabled) {
            $link = "index.php?option=com_jevlocations";
            JFactory::getLanguage()->load("com_jevlocations", JPATH_ADMINISTRATOR);
                //$this->_quickiconButtonWHover($link, "cpanel/LocationsCool.png", "cpanel/LocationsHot.png", JText::_('COM_JEVLOCATIONS'), "/administrator/components/" . JEV_COM_COMPONENT . "/assets/images/");
        }

        // Managed People

        $db = JFactory::getDbo ();
        $db->setQuery ( "SELECT enabled FROM #__extensions WHERE element = 'com_jevpeople' AND type='component' " );
        $is_enabled = $db->loadResult ();
        if ($is_enabled) {
            $link = "index.php?option=com_jevpeople";
            JFactory::getLanguage()->load("com_jevpeople", JPATH_ADMINISTRATOR);
            //$this->_quickiconButtonWHover($link, "cpanel/PeopleCool.png", "cpanel/PeopleHot.png", JText::_('COM_JEVPEOPLE'), "/administrator/components/" . JEV_COM_COMPONENT . "/assets/images/");
        }

        // RSVP Pro

        $db = JFactory::getDbo ();
        $db->setQuery ( "SELECT enabled FROM #__extensions WHERE element = 'com_rsvppro' AND type='component' " );
        $is_enabled = $db->loadResult ();
        if ($is_enabled) {
            $link = "index.php?option=com_rsvppro";
            JFactory::getLanguage()->load("com_rsvppro", JPATH_ADMINISTRATOR);
            //$this->_quickiconButtonWHover($link, "cpanel/RSVPCool.png", "cpanel/RSVPHot.png", JText::_('COM_RSVPPRO'), "/administrator/components/" . JEV_COM_COMPONENT . "/assets/images/");
        }

        // Custom Fields

        $db = JFactory::getDbo ();
        $db->setQuery ( "SELECT * FROM #__extensions WHERE element = 'jevcustomfields' AND type='plugin' AND folder='jevents' " );
        $extension = $db->loadObject();
        // Stop if user is not authorised to manage JEvents
        $customfields = '';
        if ($extension && $extension->enabled && JEVHelper::isAdminUser()) {
            $manifestCache = json_decode($extension->manifest_cache);
            $link = "index.php?option=com_jevents&task=plugin.jev_customfields.overview";
            JFactory::getLanguage()->load("plg_jevents_jevcustomfields", JPATH_ADMINISTRATOR);
            $customfields = '<li><a href="' . $link . '"><i class="fa fa-file-text-o"></i> <span>' . JText::_('JEV_CUSTOM_FIELDS') . '</span></a></li>';
        }

        $jinput = JFactory::getApplication()->input;

        $task = $jinput->getCmd("task", "cpanel.cpanel");

        $config_tabs = ($task == 'params.edit' ? "data-toggle=\"tab\" href=\"" : "href=\"index.php?option=com_jevents&task=params.edit");

        //Lets checkout what club themes are installed
        $themes = '';

        //Fetch the Club themes
        $haslayouts = false;

        $first = false;
        foreach (JEV_CommonFunctions::getJEventsViewList() as $viewfile) {
            $config = JPATH_SITE . "/components/" . JEV_COM_COMPONENT . "/views/" . $viewfile . "/config.xml";
            if (file_exists($config)) {

                if (!$first) {
                    $first = $viewfile;
                    $class = ' class="active"';
                } else {
                    $class = '';
                }
                $haslayouts = true;
                $themes .= '<li ' . $class . '><a ' . $config_tabs . '#' . $viewfile . '"><i class="fa fa-circle-o"></i>'. $viewfile .'</a></li>';
            }
        }

        $sidebar_html = '<section class="sidebar" style="height: auto;">

			<!-- sidebar menu: : style can be found in sidebar.less -->
			<ul class="sidebar-menu">
				<li class="header">Events Management</li>
				<li class="' . ($task == '' || $task == 'cpanel.cpanel' || $task == 'icalevent.list' || $task == 'icalevent.edit' ? 'active' : '') . ' treeview">
					<a href="#">
						<i class="fa fa-dashboard"></i> <span>Events</span> <i class="fa fa-angle-left pull-right"></i>
					</a>
					<ul class="treeview-menu">
						<li><a href="index.php?option=com_jevents&task=icalevent.edit"><i class="fa fa-calendar-plus-o"></i> Add an Event</a></li>
						<li><a href="index.php?option=com_jevents&task=icalevent.list&state=3"><i class="fa fa-calendar"></i> Manage Events</a></li>
						<li><a href="index.php?option=com_jevents&task=icalevent.list&state=-1"><i class="fa fa-trash"></i> Trashed Events</a></li>

					</ul>
				</li>
				<li class="' . ($task == 'params.edit' ? 'active' : '') . ' treeview">
                  <a href="#">
                    <i class="fa fa-cogs"></i> <span>Configuration</span>
                    <i class="fa fa-angle-left pull-right"></i>
                  </a>
                  <ul class="treeview-menu ' . ($task == 'params.edit' ? 'menu-open' : '') . '" >

                    <li class="' . ($task == 'params.edit' ? 'active' : '') . '">
                      <a href="#"><i class="fa fa-cog"></i> JEvents Core <i class="fa fa-angle-left pull-right"></i></a>
                      <ul class="treeview-menu">
                        <li class="difficulty1"><a '.$config_tabs.'#JEV_TAB_COMPONENT"><i class="fa fa-circle-o"></i> Component</a></li>
                        <li class="difficulty1"><a '.$config_tabs.'#JEV_PERMISSIONS"><i class="fa fa-circle-o"></i> Permissions</a></li>
                        <li class="difficulty1"><a '.$config_tabs.'#JEV_EVENT_EDITING"><i class="fa fa-circle-o"></i> Event Editing</a></li>
                        <li class="difficulty1"><a '.$config_tabs.'#JEV_EVENT_DETAIL_VIEW"><i class="fa fa-circle-o"></i> Event Detail View</a></li>
                        <li class="difficulty1"><a '.$config_tabs.'#JEV_MAIN_MONTHLY_CALENDAR"><i class="fa fa-circle-o"></i> Monthly Calendar View</a></li>
                        <li class="difficulty1"><a '.$config_tabs.'#JEV_YEAR_CATEGORY_VIEW"><i class="fa fa-circle-o"></i> Year/Category View</a></li>
                        <li class="difficulty2"><a '.$config_tabs.'#JEV_ICAL_CALENDAR"><i class="fa fa-circle-o"></i> iCal Import/Export</a></li>
                        <li class="difficulty2"><a '.$config_tabs.'#JEV_TAB_RSS"><i class="fa fa-circle-o"></i> RSS</a></li>
                        <li class="difficulty3"><a '.$config_tabs.'#ROBOT_SEF_OPTIONS"><i class="fa fa-circle-o"></i> SEF/Performance</a></li>
                        <li class="difficulty3"><a '.$config_tabs.'#JEV_MODULE_CONFIG"><i class="fa fa-circle-o"></i> Module Config</a></li>
                      </ul>
                    </li>
                  </ul>
                </li>
				<li class="' . ($task == 'defaults.list' || $task == 'defaults.edit'  ? 'active' : '') . ' treeview">
					<a href="#">
						<i class="fa fa-files-o"></i>
						<span>Custom Layouts</span> <i class="fa fa-angle-left pull-right"></i>
						<span class="label label-primary pull-right"></span>
					</a>
					<ul class="treeview-menu">
						<li><a href="index.php?option=com_jevents&task=defaults.list&filter_layout_type=jevents"><i class="fa fa-circle-o"></i> JEvents Core</a></li>
						<li><a href="index.php?option=com_jevents&task=defaults.list&filter_layout_type=jevpeople"><i class="fa fa-circle-o"></i> Managed People</a></li>
						<li><a href="index.php?option=com_jevents&task=defaults.list&filter_layout_type=jevlocations"><i class="fa fa-circle-o"></i> Managed Locations</a></li>
					</ul>
				</li>';

        if ($haslayouts) {
            $sidebar_html .= '
            <li class="' . ($task == 'params.edit#club-layouts' ? 'active' : '') . ' treeview">
					<a href="#">
						<i class="fa fa-laptop"></i> <span>Club Themes</span> <i class="fa fa-angle-left pull-right"></i>
					</a>
					<ul class="treeview-menu">' . $themes . '</ul>
				</li>
				';
        }

        $sidebar_html .= '<!-- Custom Fields -->
				    ' . $customfields . '
			</ul>
		</section>';

        return $sidebar_html;
    }

    public static function addAdminHeader($items = array(), $buttons = array(), $notifications = array(), $params = array())
    {
        // Load in the Libraries
        JEVHelper::stylesheet('jevadmin_lte.css', 'administrator/components/' . JEV_COM_COMPONENT . '/assets/css/');
        JEVHelper::stylesheet('jev_cp.css', 'administrator/components/' . JEV_COM_COMPONENT . '/assets/css/');
        JEVHelper::script('app.js', 'administrator/components/' . JEV_COM_COMPONENT . '/assets/js/');
        JEVHelper::script('jquery.slimscroll.min.js', 'administrator/components/' . JEV_COM_COMPONENT . '/assets/js/plugins/slimScroll/');
        JEVHelper::script('fastclick.min.js', 'administrator/components/' . JEV_COM_COMPONENT . '/assets/js/plugins/fastclick/');

//<!-- Ionicons -->
//<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
//<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
        JEVHelper::stylesheet('font-awesome.min.css', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/');
        JEVHelper::stylesheet('ionicons.min.css', 'https://code.ionicframework.com/ionicons/2.0.1/css//');

//Get the current user for user display.
        $user = JFactory::getUser();
        $header_html = '<a href="index.php?option=com_jevents&task=cpanel.cpanel" class="logo">
			<!-- mini logo for sidebar mini 50x50 pixels -->
			<span class="logo-mini"><img src="components/'.JEV_COM_COMPONENT.'/assets/images/JeventsTransparent_icon.png" alt="JEvents Icon" /></span>
			<!-- logo for regular state and mobile devices -->
			<span class="logo-lg"><img style="width:50px;max-width:100%;"src="components/'.JEV_COM_COMPONENT.'/assets/images/JeventsTransparent_icon.png" alt="JEvents Icon" /><b>JE</b>vents</span>
		</a>
		<!-- Header Navbar: style can be found in header.less -->
		<nav class="navbar navbar-static-top" role="navigation">
			<!-- Sidebar toggle button-->
			<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
				<span class="sr-only">Toggle navigation</span>
			</a>
			<!-- Navbar Right Menu -->
			<div class="navbar-custom-menu">
				<ul class="nav navbar-nav">';

        foreach ($items as $key => $value) {
            $header_html .= $value;
        }
		$header_html	.=	'<!-- User Account: style can be found in dropdown.less -->
					<li class="dropdown user user-menu">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<img src="http://www.gravatar.com/avatar/'. md5($user->email) . '?s35" class="user-image" alt="User Image">
							<span class="hidden-xs">' . $user->name . '</span>
						</a>
						<ul class="dropdown-menu">
							<!-- User image -->
							<li class="user-header">
								<img src="http://www.gravatar.com/avatar/'. md5($user->email).'?s80" class="img-circle" alt="User Image">
								<p>'.$user->name.'
									<small></small>
								</p>
							</li>
							<!-- Menu Body -->
							<li class="user-body">
								<div class="col-xs-4 text-center">
									<a href="#">Followers</a>
								</div>
								<div class="col-xs-4 text-center">
									<a href="#">Sales</a>
								</div>
								<div class="col-xs-4 text-center">
									<a href="#">Friends</a>
								</div>
							</li>
							<!-- Menu Footer-->
							<li class="user-footer">
								<div class="pull-left">
									<a href="#" class="btn btn-default btn-flat">Profile</a>
								</div>
								<div class="pull-right">
									<a href="#" class="btn btn-default btn-flat">Sign out</a>
								</div>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</nav>';

        return $header_html;

    }

    public static function addAdminFooter() {
        $version = JEventsVersion::getInstance();

        $footer_html = '		<div class="pull-right hidden-xs">
			<b>Version: </b>
			' . JString::substr($version->getShortVersion(), 1) . '
		</div>
		<strong>Copyright © 2015 <a href="http://www.jevents.net">JEvents - GWE Systems Ltd</a>.</strong> All rights reserved.';

        return $footer_html;
    }

}