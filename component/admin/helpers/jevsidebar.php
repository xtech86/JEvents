<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

class JevAdminJHtmlSidebar extends JHtmlSidebar
{
	/**
	 * Method to add a menu item to submenu.
	 *
	 * @param   string  $name    Name of the menu item.
	 * @param   string  $link    URL of the menu item.
	 * @param   bool    $active  True if the item is active, false otherwise.
	 * @param   string  $section  section by lowercase name i.e. jevents, rsvppro
	 * @param   string  $icon    icon CSS Class for the list item
	 * @param   integer $header   1 = is header
	 * @param   integer $parent   Does this have a  menu parent item?
	 * @param   integer $sub_parent   Does this have a sub menu parent item?
	 * @param   string  $liclass   defines the li class holding the href element
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function addEntry($name, $link = '', $active = false, $section = 'jevents', $icon = 'fa-circle-o', $header = 0, $parent = 0, $sub_parent = 0,  $liclass = '', $sub_ulclass = '')
	{
		array_push(static::$entries, array($name, $link, $active, $section, $icon, $header, $parent, $sub_parent, $liclass, $sub_ulclass));
	}

}