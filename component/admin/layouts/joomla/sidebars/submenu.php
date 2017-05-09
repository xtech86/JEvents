<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('JPATH_BASE') or die;

JHtml::_('jquery.framework');
JHtmlBehavior::core();

JFactory::getDocument()->addScriptDeclaration('
	jQuery(document).ready(function($)
	{
		if (window.toggleSidebar)
		{
			toggleSidebar(true);
		}
		else
		{
			$("#j-toggle-sidebar-header").css("display", "none");
			$("#j-toggle-button-wrapper").css("display", "none");
		}
	});
');

//We need to loop the data first to get the groupings right
$task = JFactory::getApplication()->input;
$sections = array();
$prev_key = 0;
//var_dump($task);

//die;

foreach ($displayData->list as $key => $item) {
	// echo '<pre>';print_r($item);echo '</pre>';

	if($item[7] != 0) {
		$sections[$item[3]][$item[5]][$item[7]]['sub_links'][] = $item;

	} else
    {
	    $sections[$item[3]][$item[5]][$item[6]] = $item;
    }
}

//New sidebar
?>
<aside class="main-sidebar">
    <?php
    foreach ($sections as $section) {

       // echo '<pre>';print_R($section);echo '</pre>';
        ?>
    <section class="sidebar" style="height: auto;">
        <ul class="sidebar-menu">
        <?php
        foreach ($section as $items) {
          //  echo '<pre>';print_r($items);echo '</pre>';
            foreach ($items as $item)
            {
                //First sub_link section calculation
                $sub_links = isset($item['sub_links']) ? $item['sub_links'] : array();
                $total_slinks = count($sub_links);

                if (isset($item[5]) && $item[5] == 1)
                {
                    echo '<li class="header">' . $item[0] . '</li>';
                    continue;
                }

                //Define if active / LI Class element
                $class = '';

                if (isset ($item[2]) && $item[2] == 1)  {
                    $class = 'active ' . $item[8];


                } else {
                    $class = $item[8];
                }
                $pull_icon = (strpos($item[8], 'treeview') !== false) ? '<i class="fa fa-angle-left pull-right"></i>' : '';

                echo '<li class="' . $class . '">';
                echo '<a href="' . $item[1] . '"><i class="fa ' . $item[4] . '"></i><span>' . $item[0] . '</span>' . $pull_icon . '</a>';

                // Generate sublinks if there are any
                if ($total_slinks> 0) {

                    $ul_class = ($item[9] != '') ? $item[9] : "";
                    echo '<ul class="treeview-menu ' . $ul_class . '">';

                    foreach ($sub_links as $sub_link) {
                        //var_dump($sub_link);
                        //Check if it's a theme OR Config option since we handle these differently.
                        if ($sub_link[8] > '' && preg_match('/config_edit/', $sub_link[8])) {
                            echo '<li class="' . $sub_link[8] . '"><a ' . $sub_link[1] . '"><i class="fa ' . $sub_link[4] . '"></i><span>' . $sub_link[0] . '</span></a></li>';
                        } else {
                        echo '<li class="' . $sub_link[8] . '"><a href="' . $sub_link[1] . '"><i class="fa ' . $sub_link[4] . '"></i><span>' . $sub_link[0] . '</span></a></li>';
                        }
                    }
                    echo '</ul>';

                }
                echo "</li>";
            }
        }
        ?>
        </ul>
    </section>
        <?php
    }
        ?>
</aside>
