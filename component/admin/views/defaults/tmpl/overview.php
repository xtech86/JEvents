<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
JHtml::_('behavior.core');
JHtml::_('bootstrap.tooltip');

$pathIMG = JURI::root() . '/administrator/images/';

JEVHelper::stylesheet('jev_cp.css', 'administrator/components/' . JEV_COM_COMPONENT . '/assets/css/');
JEVHelper::script('select2.full.min.js', 'administrator/components/' . JEV_COM_COMPONENT . '/assets/js/plugins/select2/');

//Custom Toolbar Load
$bar     = JToolBar::getInstance('newtoolbar');
$toolbar = $bar->getItems() ? $bar->render() : "";

?>
<div id="jev_adminui" class="jev_adminui skin-blue sidebar-mini">
	<header class="main-header">
		<?php echo JEventsHelper::addAdminHeader($items = array(), $toolbar); ?>
	</header>
	<!-- =============================================== -->
	<!-- Left side column. contains the sidebar -->
		<!-- sidebar: style can be found in sidebar.less -->
		<?php
		echo $this->sidebar;
		?>

		<!-- /.sidebar -->
	</aside>
	<!-- =============================================== -->
	<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper" style="min-height: 1096px;">
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<h1>
				<?php echo JText::_("JEV_LAYOUT_DEFAULTS"); ?>
				<small><?php echo JText::_("JEV_LAYOUT_DEFAULTS_STRAPLINE"); ?></small>
			</h1>
		</section>

		<!-- Main content -->
		<section class="content ov_info">

			<!-- Default box -->
			<div class="box">
				<div class="box-body no-padding">
					<form action="index.php" method="post" name="adminForm" id="adminForm">
						<div class="row">
                            <fieldset id="filter-bar">
                                <div class="span12 filter-select fltrt form-group">
                                    <?php if ($this->catids)
                                    { ?>
                                        <div class="span3 fltlh"></div>
                                        <div class="span3 fltrt">
                                            <div class="form-group">
                                                <select name="filter_catid"
                                                        class="form-control select2"
                                                        style="width: 100%;"
                                                        onchange="this.form.submit()">
                                                    <option
                                                        value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY'); ?></option>
                                                    <?php echo $this->catids; ?>
                                                </select>
                                            </div><!-- /.form-group -->
                                        </div> <!-- /.span2 -->
                                    <?php }
                                    else
                                    { ?>
                                        <div class="span6 "></div>
                                    <?php } ?>
                                    <div class="span3 fltrt">
                                        <div class="form-group">
                                            <select name="filter_layout_type"
                                                    class="form-control select2"
                                                    style="width: 100%;"
                                                    onchange="this.form.submit()">
                                                <?php echo $this->addonoptions; ?>
                                            </select>
                                        </div><!-- /.form-group -->
                                    </div> <!-- ./span2 -->
                                    <div class="span3 fltrt">
                                        <div class="form-group">
                                            <select name="filter_published"
                                                    class="form-control select2"
                                                    style="width: 100%;"
                                                    onchange="this.form.submit()">
                                                <option
                                                    value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                                                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array("trash" => 0, "archived" => 0, "all" => 0)), 'value', 'text', $this->filter_published, true); ?>
                                            </select>
                                        </div><!-- /.form-group -->
                                    </div>
                                </div>
                            </fieldset>
						</div>
						<div>
							<div id="editcell" class="box-body table-responsive no-padding">
								<table class="adminlist table table-hover table-striped">
                                    <tbody>
                                    <tr>
										<th width="20" nowrap="nowrap">
											<?php echo JHtml::_('grid.checkall'); ?>
										</th>
										<th width="5" style="display:none;">
											<?php echo JText::_('NUM'); ?>
										</th>
										<th class="title">
											<?php echo JText::_('TITLE'); ?>
										</th>
										<th class="title">
											<?php echo JText::_('NAME'); ?>
										</th>
										<?php
										if (count($this->languages) > 1)
										{ ?>
											<th>
												<?php echo JText::_('JGRID_HEADING_LANGUAGE'); ?>
											</th>
											<?php
										}
										if ($this->catids)
										{ ?>
											<th>
												<?php echo JText::_('JCATEGORY'); ?>
											</th>
										<?php } ?>
										<th width="10%" nowrap="nowrap"><?php echo JText::_('JEV_PUBLISHED'); ?></th>
									</tr>
									<?php
									$k = 0;
									for ($i = 0, $n = count($this->items); $i < $n; $i++)
									{
										$row = &$this->items[$i];

										if (strpos($row->name, "com_") === 0)
										{
											$lang  = JFactory::getLanguage();
											$parts = explode(".", $row->name);
											$lang->load($parts[0]);
										}
										$link = JRoute::_('index.php?option=' . JEV_COM_COMPONENT . '&task=defaults.edit&id=' . $row->id);
										?>
										<tr class="<?php echo "row$k"; ?>">
											<td width="20">
												<?php echo JHtml::_('grid.id', $i, $row->id); ?>
											</td>
											<td style="display:none;">
												<?php echo $i + 1; ?>
											</td>
											<td>
									<span class="editlinktip hasTip"
									      title="<?php echo JText::_('JEV_Edit_Layout'); ?>::<?php echo $this->escape(JText::_($row->title)); ?>">
										<a href="<?php echo $link; ?>">
									<?php echo $this->escape(JText::_($row->title)); ?></a>
									</span>
											</td>
											<td>
												<?php echo $this->escape($row->name); ?>

											</td>
											<?php
											if (count($this->languages) > 1)
											{ ?>
												<td class="center">
													<?php echo $this->translationLinks($row);
													/*
													if ($row->language == '*'):
														 echo JText::alt('JALL', 'language');
													else:
														echo $row->language_title ? $this->escape($row->language_title) : JText::_('JUNDEFINED');
													endif;
													 */
													?>
												</td>
											<?php } ?>
											<?php if ($this->catids)
											{ ?>
												<td class="center">
													<?php if ($row->catid == '0'): ?>
														<?php echo JText::alt('JALL', 'language'); ?>
													<?php else: ?>
														<?php echo $row->category_title ? $this->escape($row->category_title) : JText::_('JUNDEFINED'); ?>
													<?php endif; ?>
												</td>
											<?php } ?>

											<td align="center">
												<?php
												$img = $row->state ? JHTML::_('image', 'admin/tick.png', '', array('title' => ''), true) : JHTML::_('image', 'admin/publish_x.png', '', array('title' => ''), true);
												$state = $row->state ? '<span class="label label-success">' .  JText::_("JEV_PUBLISHED") . '</span>' : '<span class="label label-danger">' .  JText::_("JEV_UNPUBLISHED") . '</span>';
												?>
												<a href="javascript: void(0);"
												   onclick="return listItemTask('cb<?php echo $i; ?>','<?php echo $row->state ? 'defaults.unpublish' : 'defaults.publish'; ?>')"><?php echo $state; ?></a>
											</td>
										</tr>
										<?php
										$k = 1 - $k;
									}
									?>
									</tbody>
								</table>
							</div>

							<input type="hidden" name="option" value="<?php echo JEV_COM_COMPONENT; ?>"/>
							<input type="hidden" name="task" value="defaults.list"/>
							<input type="hidden" name="boxchecked" value="0"/>
							<?php echo JHTML::_('form.token'); ?>
						</div>
					</form>
				</div><!-- /.box-body -->
				<div class="box-footer">
					<p class="text-muted well well-sm no-shadow success" style="margin-top: 10px;">
						<?php echo JText::_('JEV_CUSTOM_LAYOUTS_FOOTER_INFO'); ?>
					</p>
				</div><!-- /.box-footer-->
			</div><!-- /.box -->

		</section><!-- /.content -->
	</div>
	<!-- /.content-wrapper -->
    <?php echo JEventsHelper::addAdminFooter(); ?>
	<!-- /.control-sidebar -->
	<!-- Add the sidebar's background. This div must be placed
		   immediately after the control sidebar -->
	<div class="control-sidebar-bg" style="position: fixed; height: auto;"></div>
</div>

<script>
	jQuery(function () {
		//Initialize Select2 Elements
		jQuery(".select2").select2();
	});
</script>