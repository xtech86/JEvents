<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: edit.php 3229 2012-01-30 12:06:34Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C)  2008-2017 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.modal');

global $task, $catid;
$db     = JFactory::getDbo();
$editor = JFactory::getEditor();

// Clean any existing cache files
$cache = JFactory::getCache(JEV_COM_COMPONENT);
$cache->clean(JEV_COM_COMPONENT);
$action = JFactory::getApplication()->isAdmin() ? "index.php" : "index.php?option=" . JEV_COM_COMPONENT . "&Itemid=" . JEVHelper::getItemid();

global $task;

if (isset($this->editItem->ics_id))
{
	// Load in the data since we are editing.
	$id         = (int) $this->editItem->ics_id;
	$catid      = $this->editItem->catid;
	$access     = $this->editItem->access;
	$srcURL     = $this->editItem->srcURL;
	$filename   = $this->editItem->filename;
	$overlaps   = $this->editItem->overlaps;
	$label      = $this->editItem->label;
	$icaltype   = (int) $this->editItem->icaltype;
	$icalparams = json_decode($this->editItem->params);

	if ($srcURL === "")
	{
		$filemessage = JText::_("COM_JEVENTS_MANAGE_CALENDARS_OVERVIEW_LOADED_FROM_LOCAL_FILE_CALLLED") . " ";
	}
	else
	{
		$filemessage = JText::_('FROM_FILE');
	}
}
else
{
	// This is a new iCal, lets setup the default data.
	$id          = 0;
	$catid       = 0;
	$access      = 0;
	$srcURL      = "";
	$filename    = "";
	$overlaps    = 0;
	$label       = "";
	$icaltype    = 2;
	$icalparams  = new stdClass;
	$filemessage = JText::_('FROM_FILE');
}

// build the html select list
$glist = JEventsHTML::buildAccessSelect($access, 'class="inputbox" size="1"', "", "access");

$disabled = "";

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
	<?php echo $this->sidebar; ?>
	<!-- /.sidebar -->
	<!-- =============================================== -->
	<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper" style="min-height: 1096px;">
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<h1>
				<?php echo JText::_("JEV_ADMIN_CALENDAR_FEED"); ?>
				<small><?php echo JText::_(""); ?></small>
			</h1>
		</section>

		<!-- Main content -->
		<section class="content ov_info">
			<!-- Default box -->
			<div class="box">
				<div class="box-body">
					<div id="jevents">
						<form action="<?php echo $action; ?>" method="post" name="adminForm" accept-charset="UTF-8"
							id="adminForm" class="adminForm form-horizontal">
							<?php

							echo JEventsHTML::buildScriptTag('start');

							// Leave this as submit button since our submit buttons use the old functional form
							?>
							function submitbutton(pressbutton) {
								if (pressbutton.substr(0, 10) == 'icals.list') {
									submitform( pressbutton );
									return;
								}

								var form = document.adminForm;
								if (document.getElementById('catid').value == "0"){
									alert( "<?php echo html_entity_decode(JText::_('JEV_E_WARNCAT')); ?>" );
									return(false);
								} else {
									//alert('about to submit the form');
									submitform(pressbutton);
								}
							}
							<?php echo JEventsHTML::buildScriptTag('end'); ?>
								<div class="form-group span3">
									<label for="icsLabel"><?php echo JText::_("UNIQUE_IDENTIFIER"); ?></label>
									<input class="inputbox" type="text" name="icsLabel" id="icsLabel" value="<?php echo $label; ?>" size="80"/>
								</div>
								<?php if ($this->users)
								{
								?>
									<div class="form-group span12">
										<label for="jevusers">
											<?php echo JText::_("JEV_CALENDAR_OWNER"); ?>
										</label>
										<?php echo $this->users; ?>
									</div>
								<?php } ?>
								<div class="form-group span12">
									<label for="access">
										<?php echo JText::_('JEV_EVENT_ACCESSLEVEL'); ?>
									</label>
									<?php echo $glist; ?>

								</div>
                            <div class="form-group span12">
									<label for="catid">
										<?php echo JText::_("JEV_FALLBACK_CATEGORY"); ?>
									</label>
									<?php echo JEventsHTML::buildCategorySelect($catid, "", null, $this->with_unpublished_cat, true, 0, 'catid'); ?>
								</div>


								<?php
								if (!isset($this->editItem->ignoreembedcat) || $this->editItem->ignoreembedcat == 0)
								{
									$checked0 = ' checked="checked"';
									$checked1 = '';
								}
								else
								{
									$checked1 = ' checked="checked"';
									$checked0 = '';
								}
								?>
								<div class="form-group span12">
										<label class="hasTip" for="ignoreembedcat" id="ignoreembedcat-lbl">
											<?php echo JText::_('JEV_IGNORE_EMBEDDED_CATEGORIES'); ?>
										</label>
										<fieldset class="radio btn-group" id="ignoreembedcat">
											<input id="ignoreembedcat0" type="radio" value="0"  name="ignoreembedcat" <?php echo $checked0; ?>/>
											<label for="ignoreembedcat0" class="btn"><?php echo JText::_('JEV_NO'); ?></label>
											<input id="ignoreembedcat1" type="radio" value="1" name="ignoreembedcat" <?php echo $checked1; ?>/>
											<label for="ignoreembedcat1" class="btn"><?php echo JText::_('JEV_YES'); ?></label>
										</fieldset>
								</div>

							<?php if ($id === 0)
							{ ?>
								<ul class="nav nav-tabs" id="myicalTabs">
									<li class="active">
										<a data-toggle="tab" href="#from_scratch"><?php echo JText::_("FROM_SCRATCH"); ?></a>
									</li>
									<li>
										<a data-toggle="tab" href="#from_file"><?php echo JText::_("FROM_FILE"); ?></a>
									</li>
									<li>
										<a data-toggle="tab" href="#from_url"><?php echo JText::_("FROM_URL"); ?></a>
									</li>
									<li>
										<a data-toggle="tab" href="#from_facebook_page"><?php echo JText::_("FROM_FACEBOOK"); ?></a>
									</li>
								</ul>
								<?php
							}
							// Tabs
							echo JHtml::_('bootstrap.startPane', 'myicalTabs', array('active' => 'from_scratch'));

							// Load if from scratch or file.
							if ($id == 0 || $icaltype == 2)
							{
								echo JHtml::_('bootstrap.addPanel', "myicalTabs", "from_scratch");
								if (!isset($this->editItem->isdefault) || $this->editItem->isdefault == 0)
								{
									$checked0 = ' checked="checked"';
									$checked1 = '';
								}
								else
								{
									$checked1 = ' checked="checked"';
									$checked0 = '';
								}
								if (!isset($this->editItem->overlaps) || $this->editItem->overlaps == 0)
								{
									$overlaps0 = ' checked="checked"';
									$overlaps1 = '';
								}
								else
								{
									$overlaps1 = ' checked="checked"';
									$overlaps0 = '';
								}
								?>
								<div class="control-group span12">
									<div class="control-label">
										<?php echo JText::_("JEV_EVENT_ISDEFAULT"); ?>
									</div>
									<div class="controls">
										<fieldset class="radio btn-group" id="ignoreembedcat">
											<input id="isdefault0" type="radio" value="0" name="isdefault" <?php echo $checked0; ?>/>
											<label for="isdefault0"><?php echo JText::_('JEV_NO'); ?></label>
											<input id="isdefault1" type="radio" value="1" name="isdefault" <?php echo $checked1; ?>/>
											<label for="isdefault1"><?php echo JText::_('JEV_YES'); ?></label>
										</fieldset>
									</div>
								</div>

								<div class="control-group span12">
									<div class="control-label">
										<?php echo JText::_("JEV_BLOCK_OVERLAPS"); ?>
									</div>
									<div class="controls">
										<fieldset class="radio btn-group" id="ignoreembedcat">
											<input id="overlaps0" type="radio" value="0" name="overlaps" <?php echo $overlaps0; ?>/>
											<label for="overlaps0"><?php echo JText::_('JEV_NO'); ?></label>
											<input id="overlaps1" type="radio" value="1" name="overlaps" <?php echo $overlaps1; ?>/>
											<label for="overlaps1"><?php echo JText::_('JEV_YES'); ?></label>
										</fieldset>
									</div>
								</div>


								<?php if ($id === 0)
							{ ?>
								<button name="newical" title="<?php echo JText::_("CREATE_FROM_SCRATCH"); ?>" class="btn btn-success"
								        onclick="submitbutton('icals.new');return false;"><?php echo JText::_("CREATE_FROM_SCRATCH"); ?></button>
								<?php
							}
							}

							if ($id === 0 || $icaltype === 1)
							{
								echo JHtml::_('bootstrap.endPanel');
								echo JHtml::_('bootstrap.addPanel', "myicalTabs", "from_file");
								?>
								<?php if ($id == 0)
							{ ?>
								<h3><?php echo $filename; ?></h3>
								<input class="inputbox" type="file" name="upload" id="upload" size="80"/><br/><br/>
								<button name="loadical" title="<?php echo JText::_('LOAD_ICAL_FROM_FILE'); ?>" class="btn btn-success"
								        onclick="var icalfile=document.getElementById('upload').value;if (icalfile.length==0)return false; else submitbutton('icals.save');return false;">
									<?php echo JText::_('LOAD_ICAL_FROM_FILE'); ?></button>
								<?php
							}
							}

							if ($id === 0 || $icaltype === 0)
							{
								echo JHtml::_('bootstrap.endPanel');
								echo JHtml::_('bootstrap.addPanel', "myicalTabs", "from_url");
								?>
								<?php
								$urlsAllowed = ini_get("allow_url_fopen");
								if (!$urlsAllowed && !is_callable("curl_exec"))
								{
									echo "<h3>" . JText::_("JEV_ICAL_IMPORTDISABLED") . "</h3>";
									echo "<p>" . JText::_("JEV_SAVEFILELOCALLY") . "</p>";
									$disabled = "disabled";
								}
								else
								{
									$disabled = "";
								}

								if (!isset($this->editItem->autorefresh) || $this->editItem->autorefresh == 0)
								{
									$checked0 = ' checked="checked"';
									$checked1 = '';
								}
								else
								{
									$checked1 = ' checked="checked"';
									$checked0 = '';
								}
								?>

								<div class="control-group">
									<div class="control-label">
										<?php echo JText::_("JEV_EVENT_AUTOREFRESH"); ?>
									</div>
									<div class="controls">
										<fieldset class="radio btn-group" id="ignoreembedcat">
											<input id="autorefresh0" type="radio" value="0" name="autorefresh" <?php echo $checked0; ?>/>
											<label for="autorefresh0"><?php echo JText::_('JEV_NO'); ?></label>
											<input id="autorefresh1" type="radio" value="1" name="autorefresh" <?php echo $checked1; ?>/>
											<label for="autorefresh1"><?php echo JText::_('JEV_YES'); ?></label><br/><br/>
										</fieldset>
									</div>
								</div>

								<input class="inputbox" type="text" name="uploadURL"
								       id="uploadURL" <?php echo $disabled; ?>
								       size="120" value="<?php echo $srcURL; ?>"/><br/><br/>
								<?php
								if ($id == 0)
								{ ?>
									<button name="loadical" <?php echo $disabled; ?> title="<?php echo JText::_("LOAD_ICAL_FROM_URL"); ?>" class="btn btn-success"
									        onclick="var icalfile=document.getElementById('uploadURL').value;if (icalfile.length == 0) return false; else submitbutton('icals.save');return false;">
										<?php echo JText::_('LOAD_ICAL_FROM_URL'); ?>
									</button>
									<?php
								}
							}
							echo JHtml::_('bootstrap.endPanel');

							if ($id === 0 || $icaltype === 3)
							{
								echo JHtml::_('bootstrap.addPanel', "myicalTabs", "from_facebook_page");
								?>
								<fieldset class="" id="ignoreembedcat">
									<p><?php echo JText::_('JEV_FACEBOOK_INFO'); ?> </p>
									<div class="form-group span12">
										<label for="facebookapp_feed_id"> <?php echo JText::_('JEV_FACEBOOK_APP_FEED_ID'); ?> </label>
										<input id="facebookapp_feed_id" type="text" name="facebookapp_feed_id"
										       value="<?php echo isset($icalparams->facebookapp_feed_id) ? $icalparams->facebookapp_feed_id : ''; ?>"/>
									</div>
									<div class="form-group span12">
										<label for="facebookapp_id"> <?php echo JText::_('JEV_FACEBOOK_APP_ID'); ?> </label>
										<input id="facebookapp_id" type="text" name="facebookapp_id"
										       value="<?php echo isset($icalparams->facebookapp_id) ? $icalparams->facebookapp_id : ''; ?>"/>
									</div>
									<div class="form-group span12">
										<label for="facebookapp_secret"> <?php echo JText::_('JEV_FACEBOOK_APP_SECRET'); ?> </label>
										<input id="facebookapp_secret" type="text" name="facebookapp_secret"
										       value="<?php echo isset($icalparams->facebookapp_secret) ? $icalparams->facebookapp_secret : ''; ?>"/>
									</div>
									<div class="form-group span12">
										<label for="facebookapp_token"> <?php echo JText::_('JEV_FACEBOOK_APP_TOKEN'); ?> </label>
										<input id="facebookapp_token" type="text" name="facebookapp_token"
										       value="<?php echo isset($icalparams->facebookapp_token) ? $icalparams->facebookapp_token : ''; ?>" readonly/>
									</div>
									<div class="form-group span12">
										<label for=""> <?php echo JText::_('JEV_FACEBOOK_APP_GET_TOKEN'); ?> </label>
										<?php $link = JUri::getInstance() . '&layout=fb_get_token&tmpl=component'; ?>

										<a href="#" id="facebook_get_token" class="btn btn-primary" onclick="facebookInit(jQuery('#facebookapp_id').val());">Authorize APP</a>
									</div>
									<div class="form-group span12">
										<label for=""> <?php echo JText::_('JEV_ICAL_IMPORT_STATE'); ?> </label>
										<?php

										$checked1 = ' checked="checked"';
										$checked0 = '';

										if (isset($icalparams->import_state) && $icalparams->import_state == 0)
										{
											$checked0 = ' checked="checked"';
											$checked1 = '';
										}
										?>
										<fieldset class="radio btn-group" id="import_state">
											<input id="import_state0" type="radio" value="0" name="import_state" <?php echo $checked0; ?>/>
											<label for="import_state0"><?php echo JText::_('JEV_UNPUBLISHED'); ?></label>
											<input id="import_state1" type="radio" value="1" name="import_state" <?php echo $checked1; ?>/>
											<label for="import_state1"><?php echo JText::_('JEV_PUBLISHED'); ?></label><br/><br/>
										</fieldset>
									</div>
									<div class="form-group span12">
										<label for=""> <?php echo JText::_('JEV_ICAL_IMPORT_REPLACE_EVENT_TITLE_WITH_FEED_TTLE'); ?> </label>
										<?php

										$checked0 = ' checked="checked"';
										$checked1 = '';

										if (isset($icalparams->replaceEventTitle) && $icalparams->replaceEventTitle == 1)
										{
											$checked1 = ' checked="checked"';
											$checked0 = '';
										}
										?>
										<fieldset class="radio btn-group" id="replaceEventTitle">
											<input id="replaceEventTitle0" type="radio" value="0" name="replaceEventTitle" <?php echo $checked0; ?>/>
											<label for="replaceEventTitle0"><?php echo JText::_('JEV_NO'); ?></label>
											<input id="replaceEventTitle1" type="radio" value="1" name="replaceEventTitle" <?php echo $checked1; ?>/>
											<label for="replaceEventTitle1"><?php echo JText::_('JEV_YES'); ?></label><br/><br/>
										</fieldset>
									</div>

									<?php
									if (!isset($this->editItem->autorefresh) || $this->editItem->autorefresh == 0)
									{
										$checked0 = ' checked="checked"';
										$checked1 = '';
									}
									else
									{
										$checked1 = ' checked="checked"';
										$checked0 = '';
									}
									?>

									<div class="form-group row span12">
										<label>
											<?php echo JText::_("JEV_EVENT_AUTOREFRESH"); ?>
										</label>
										<fieldset class="radio btn-group" id="ignoreembedcat">
											<input id="autorefresh0" type="radio" value="0" name="autorefresh" <?php echo $checked0; ?>/>
											<label for="autorefresh0"><?php echo JText::_('JEV_NO'); ?></label>
											<input id="autorefresh1" type="radio" value="1" name="autorefresh" <?php echo $checked1; ?>/>
											<label for="autorefresh1"><?php echo JText::_('JEV_YES'); ?></label><br/><br/>
										</fieldset>
									</div>

									<div class="form-group span12">
										<?php if ($id === 0)
										{ ?>
                                            <button name="loadical" <?php echo $disabled; ?> title="<?php echo JText::_("JEV_IMPORT_ICAL_FROM_FACEBOOK"); ?>" class="btn btn-success"
                                                    onclick="var icalfile=document.getElementById('uploadURL').value;if (icalfile.length == 0) return false; else submitbutton('icals.save');return false;">
												<?php echo JText::_('JEV_IMPORT_ICAL_FROM_FACEBOOK'); ?>
                                            </button>
										<?php } ?>
									</div>
								</fieldset>
								<?php
								echo JHtml::_('bootstrap.endPanel');
							}
							echo JHtml::_('bootstrap.endPane', 'myicalTabs'); ?>

							<script>
                                function getFbParams() {
                                    app_id = jQuery('#facebookapp_id').val();
                                    app_secret = jQuery('#facebookapp_secret').val();

                                    var _href = jQuery('#facebook_get_token').attr("href");
                                    jQuery('#facebook_get_token').attr("href", _href + '&app_id=' + app_id + '&app_secret=' + app_secret);
                                }
							</script>

							<div id="fb-root"></div>
							<script src="https://connect.facebook.net/en_US/all.js"></script>
							<script type="text/javascript">
                                function facebookInit(appId) {
                                    FB.init({
                                        appId: appId,
                                        status: true,
                                        cookie: true,
                                        oauth: true
                                    });

                                    FB.getLoginStatus(function (stsResp) {
                                        if (stsResp.authResponse) {
                                            // We are logged in and ready to go!
                                            if (stsResp.authResponse.accessToken) {
                                                LongLifeToken = fetchFbToken(stsResp.authResponse.accessToken);
                                                console.log(LongLifeToken);
                                                alert('1Long Life token: ' + LongLifeToken);
                                            }
                                        } else {
                                            // We're not connected
                                            FB.login(function (response) {
                                                if (response.authResponse) {
                                                    console.log('Welcome!  Fetching your information.... ');
                                                    FB.api('/me', function (response) {
                                                        // We need to get a new long live token via Ajax Serverside.
                                                        // jQuery('#facebookapp_token').val(response.accessToken);
                                                        if (response.accessToken) {
                                                            LongLifeToken = fetchFbToken(response.accessToken);
                                                            //alert('Long Life token: ' + LongLifeToken);
                                                        }
                                                        alert('Good to see you, ' + response.name);

                                                        facebookInit(appId); // ReRun this to get the token.
                                                    });
                                                } else {
                                                    alert('Opps, you cancelled login or did not fully authorize..');
                                                }
                                            }, {scope: 'email,user_events,manage_pages,rsvp_event'});
                                            alert('Sorry, you are not logged in.');
                                        }
                                    });
                                }

                                // Function to fetch facebook long life token
                                function fetchFbToken(ShortLifetoken)
                                {
                                    var token = '{<?php echo JSession::getFormToken();?>}';
                                    jQuery.ajax({
                                        type: 'POST',
                                        dataType: 'json',
                                        url: '<?php echo JUri::root(); ?>index.php?option=com_jevents&ttoption=com_jevents&typeaheadtask=gwejson&file=fb_token&path=admin&folder=gwejsonhelpers%2F&token=' + token,
                                        data: {
                                            'json': JSON.stringify({
                                                'ShortLifeToken': ShortLifetoken,
                                                'LongLifeToken': '',
	                                            'NeverExpireToken': '',
                                                'AppID': jQuery('#facebookapp_id').val(),
                                                'AppSecret': jQuery('#facebookapp_secret').val()
                                            })
                                        },
                                        contentType: "application/x-www-form-urlencoded; charset=utf-8",
                                        scriptCharset: "utf-8"
                                    }).done(function ( data ) {
                                        try {
                                            jQuery('#facebookapp_token').val(data.NeverExpireToken);
                                        }
                                        catch (e) {
                                            //alert('The form failed? Check console log.');
                                            //console.log("The form failed and the exception was caught." + e);
                                        }
                                    }).fail(function (x)
                                    {
                                            //alert('The form failed? Check console log.');
                                            //console.log("We failed for some reason. " + x);
                                    });
                                }
							</script>

							<input type="hidden" name="icsid" id="icsid" <?php echo $disabled; ?>
							       value="<?php echo $id; ?>"/>
							<?php echo JHtml::_('form.token'); ?>
							<input type="hidden" name="boxchecked" value="0"/>
							<input type="hidden" name="task" value="icals.edit"/>
							<input type="hidden" name="params" value="1"/>
							<input type="hidden" name="option" value="<?php echo JEV_COM_COMPONENT; ?>"/>
							</form>
						</form>

					</div><!-- /.box-body -->
		</section><!-- /.content -->
	</div>
	<!-- /.content-wrapper -->
	<?php echo JEventsHelper::addAdminFooter(); ?>
	<!-- /.control-sidebar -->
	<!-- Add the sidebar's background. This div must be placed
		   immediately after the control sidebar -->
	<div class="control-sidebar-bg" style="position: fixed; height: auto;"></div>
	<!-- Modal -->
	<div class="modal fade" id="alertModal" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
					<h4 class="modal-title">Error</h4>
				</div>
				<div class="modal-body">
					<p id="error"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- End Alert Modal-->
</div>