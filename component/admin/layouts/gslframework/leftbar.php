<?php
/**
 * @version    CVS: 1.7.4
 * @package    com_yoursites
 * @author     Geraint Edwards <yoursites@gwesystems.com>
 * @copyright  2016-2019 GWE Systems Ltd
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;

$leftIconLinks = GslHelper::getLeftIconLinks();

JFactory::getDocument()->addScriptDeclaration('ys_popover(".hasYsPopover");');

?>
<aside id="left-col" class="gsl-padding-remove  gsl-background-secondary hide-label ">

    <nav class="left-nav-wrap  gsl-width-auto@m gsl-navbar-container" gsl-navbar>
        <div class="left-logo gsl-background-secondary"  gsl-toggle="target:#left-col, #left-col .left-nav, .ysts-page-title; mode: hover;cls: hide-label">
            <div>
                <?php
                GslHelper::cpanelIconLink();
                ?>
            </div>
        </div>

        <div class="gsl-navbar gsl-background-secondary"  >
            <ul class="left-nav gsl-navbar-nav gsl-list hide-label gsl-background-secondary" gsl-toggle="target:#left-col, #left-col .left-nav, .ysts-page-title; mode: hover;cls: hide-label">
                <?php
                foreach ($leftIconLinks as $leftIconLink)
                {
	                $tooltip = "";
	                if (!empty($leftIconLink->tooltip) && !empty($leftIconLink->tooltip_detail))
                    {
	                    $leftIconLink->class .= " hasYsPopover ";
	                    $tooltip = " data-yspoptitle='$leftIconLink->tooltip' data-yspopcontent='$leftIconLink->tooltip_detail'";
                    }

	                ?>
                    <li class="<?php echo $leftIconLink->class . ($leftIconLink->active ? " gsl-active" : ""); ?>" <?php echo $tooltip;?>>
                        <a href="<?php echo $leftIconLink->link; ?>" >
                            <span data-gsl-icon="icon: <?php echo $leftIconLink->icon; ?>" class="gsl-margin-small-right"></span>
                            <span class="nav-label"><?php echo $leftIconLink->label; ?></span>
                        </a>
                    </li>
	                <?php
                }

                GslHelper::returnToMainComponent();
                ?>
            </ul>
        </div>
    </nav>
</aside>