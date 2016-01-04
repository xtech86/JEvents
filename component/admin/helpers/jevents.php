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

        if (JevJoomlaVersion::isCompatible("3.0")) {
            JHtmlSidebar::addEntry(
                JText::_('CONTROL_PANEL'), 'index.php?option=com_jevents', $vName == 'cpanel.cpanel'
            );

            JHtmlSidebar::addEntry(
                JText::_('JEV_ADMIN_ICAL_EVENTS'), 'index.php?option=com_jevents&task=icalevent.list', $vName == 'icalevent.list'
            );

            if (JEVHelper::isAdminUser()) {
                JHtmlSidebar::addEntry(
                    JText::_('JEV_ADMIN_ICAL_SUBSCRIPTIONS'), 'index.php?option=com_jevents&task=icals.list', $vName == 'icals.list'
                );
            }
            JHtmlSidebar::addEntry(
                JText::_('JEV_INSTAL_CATS'), "index.php?option=com_categories&extension=com_jevents", $vName == 'categories'
            );
            if (JEVHelper::isAdminUser()) {
                JHtmlSidebar::addEntry(
                    JText::_('JEV_MANAGE_USERS'), 'index.php?option=com_jevents&task=user.list', $vName == 'user.list'
                );
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
            }
        } else {
            JSubMenuHelper::addEntry(
                JText::_('CONTROL_PANEL'), 'index.php?option=com_jevents', $vName == 'cpanel.cpanel'
            );

            JSubMenuHelper::addEntry(
                JText::_('JEV_ADMIN_ICAL_EVENTS'), 'index.php?option=com_jevents&task=icalevent.list', $vName == 'icalevent.list'
            );

            if (JEVHelper::isAdminUser()) {
                JSubMenuHelper::addEntry(
                    JText::_('JEV_ADMIN_ICAL_SUBSCRIPTIONS'), 'index.php?option=com_jevents&task=icals.list', $vName == 'icals.list'
                );
            }
            JSubMenuHelper::addEntry(
                JText::_('JEV_INSTAL_CATS'), "index.php?option=com_categories&extension=com_jevents", $vName == 'categories'
            );
            if (JEVHelper::isAdminUser()) {
                JSubMenuHelper::addEntry(
                    JText::_('JEV_MANAGE_USERS'), 'index.php?option=com_jevents&task=user.list', $vName == 'user.list'
                );
                JSubMenuHelper::addEntry(
                    JText::_('JEV_INSTAL_CONFIG'), 'index.php?option=com_jevents&task=params.edit', $vName == 'params.edit'
                );
                JSubMenuHelper::addEntry(
                    JText::_('JEV_LAYOUT_DEFAULTS'), 'index.php?option=com_jevents&task=defaults.list', in_array($vName, array('defaults.list', 'defaults.overview'))
                );

                //Support & CSS customs should only be for Admins really.
                JSubMenuHelper::addEntry(
                    JText::_('SUPPORT_INFO'), 'index.php?option=com_jevents&task=cpanel.support', $vName == 'cpanel.support'
                );
                JSubMenuHelper::addEntry(
                    JText::_('JEV_CUSTOM_CSS'), 'index.php?option=com_jevents&task=cpanel.custom_css', $vName == 'cpanel.custom_css'
                );
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


        $sidebar_html = '<section class="sidebar" style="height: auto;">
			<!-- sidebar menu: : style can be found in sidebar.less -->
			<ul class="sidebar-menu">
				<li class="header">Events Management</li>
				<li class="active treeview">
					<a href="#">
						<i class="fa fa-dashboard"></i> <span>Events</span> <i class="fa fa-angle-left pull-right"></i>
					</a>
					<ul class="treeview-menu">
						<li><a href="index.php?option=com_jevents&task=icalevent.edit"><i class="fa fa-calendar-plus-o"></i> Add an Event</a></li>
						<li><a href="index.php?option=com_jevents&task=icalevent.list&state=3"><i class="fa fa-calendar"></i> Manage Events</a></li>
						<li><a href="index.php?option=com_jevents&task=icalevent.list&state=-1"><i class="fa fa-trash"></i> Trashed Events</a></li>

					</ul>
				</li>
				<li class="treeview">
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
				</li>
				<li class="treeview">
					<a href="#">
						<i class="fa fa-laptop"></i> <span>Club Themes</span> <i class="fa fa-angle-left pull-right"></i>
					</a>
					<ul class="treeview-menu">
						<li><a href="pages/layout/top-nav.html"><i class="fa fa-circle-o"></i> Iconic</a></li>
						<li><a href="pages/layout/boxed.html"><i class="fa fa-circle-o"></i> Ruthin</a></li>
						<li><a href="pages/layout/fixed.html"><i class="fa fa-circle-o"></i> FlatPlus</a></li>
					</ul>
				</li>
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
        $header_html = '<a href="index2.html" class="logo">
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