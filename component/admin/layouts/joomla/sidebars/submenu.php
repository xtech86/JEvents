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
foreach ($displayData->list as $key => $item) {
	// echo '<pre>';print_r($item);echo '</pre>';

	if($item[7] != 0) {
		$sections[$item[3]][$item[5]][$item[7]]['sub_links'][] = $item;

	} else if ($item[5] == 0)
    {
	    $sections[$item[3]][$item[5]][] = $item;
    } else  {
	    $sections[$item[3]][$item[5]][$prev_key] = $item;
    }
	$prev_key = $key;
}

//New sidebar
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
                    //Check if it's a theme OR Config option since we handle these differently.
                    if ($sub_link[8] > '' && preg_match('/config_edit/', $sub_link[8])) {
	                    echo '<li class="' . $sub_link[8] . '"><a ' . $sub_link[1] . '"><i class="fa ' . $sub_link[4] . '"></i><span>' . $sub_link[0] . '</span>';
                    } else {
                    echo '<li class="' . $sub_link[8] . '"><a href="' . $sub_link[1] . '"><i class="fa ' . $sub_link[4] . '"></i><span>' . $sub_link[0] . '</span>';
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
<!-- OLD Joomla! SIDEBAR Below
<div id="j-toggle-sidebar-wrapper hello" style="display: none !important;">
	<div id="j-toggle-button-wrapper" class="j-toggle-button-wrapper">
		<?php echo JLayoutHelper::render('joomla.sidebars.toggle'); ?>
	</div>
	<div id="sidebar" class="sidebar">
		<div class="sidebar-nav">
			<?php if ($displayData->displayMenu) : ?>
			<ul id="submenu" class="nav nav-list">
				<?php foreach ($displayData->list as $item) :
				if (isset ($item[2]) && $item[2] == 1) : ?>
					<li class="active">
				<?php else : ?>
					<li>
				<?php endif;
				if ($displayData->hide) : ?>
					<a class="nolink"><?php echo $item[0]; ?></a>
				<?php else :
					if (strlen($item[1])) : ?>
						<a href="<?php echo JFilterOutput::ampReplace($item[1]); ?>"><?php echo $item[0]; ?></a>
					<?php else : ?>
						<?php echo $item[0]; ?>
					<?php endif;
				endif; ?>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
			<?php if ($displayData->displayMenu && $displayData->displayFilters) : ?>
			<hr />
			<?php endif; ?>
			<?php if ($displayData->displayFilters) : ?>
			<div class="filter-select hidden-phone">
				<h4 class="page-header"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></h4>
				<?php foreach ($displayData->filters as $filter) : ?>
					<label for="<?php echo $filter['name']; ?>" class="element-invisible"><?php echo $filter['label']; ?></label>
					<select name="<?php echo $filter['name']; ?>" id="<?php echo $filter['name']; ?>" class="span12 small" onchange="this.form.submit()">
						<?php if (!$filter['noDefault']) : ?>
							<option value=""><?php echo $filter['label']; ?></option>
						<?php endif; ?>
						<?php echo $filter['options']; ?>
					</select>
					<hr class="hr-condensed" />
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<div id="j-toggle-sidebar"></div>
</div>
-->