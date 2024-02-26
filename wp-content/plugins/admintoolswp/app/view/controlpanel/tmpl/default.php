<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\ControlPanel\Html */
use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;
?>
<script>
jQuery(document).ready(function(){
    admintools.ControlPanel.myIP = '<?php echo $this->myIP; ?>';
    admintools.ControlPanel.plugin_url = '<?php echo ADMINTOOLSWP_URL; ?>';
});
</script>
<div>
    <h1 class="akeeba-cpanel-title"><span class="aklogo-admintools-wp-small"></span> Admin Tools</h1>

    <?php include __DIR__.'/warnings.php'; ?>

    <div id="selfBlocked" class="akeeba-panel--danger text-center" style="display: none;">
        <a class="akeeba-btn--red--big" href="<?php echo ADMINTOOLSWP_URL; ?>&view=ControlPanel&task=unblockme">
            <span class="akion-unlocked"></span>
            <?php echo Language::_('COM_ADMINTOOLS_CONTROLPANEL_UNBLOCK_ME'); ?>
        </a>
    </div>

    <div class="akeeba-container--50-50">
        <div>

            <?php if (!$this->hasValidPassword): ?>
                <?php include __DIR__.'/masterpassword.php'; ?>
            <?php endif; ?>

			<?php include __DIR__.'/security.php'; ?>
			<?php include __DIR__.'/tools.php'; ?>

			<?php if (!$this->needsQuickSetup): ?>
				<?php include __DIR__.'/quicksetup.php' ?>
			<?php endif; ?>
        </div>

        <div>
            <div class="akeeba-panel--default">
                <header class="akeeba-block-header">
                    <h3><?php echo Language::_('COM_ADMINTOOLS_PARAMS_UPDATES') ?></h3>
                </header>

                <div>
                    <!-- CHANGELOG :: BEGIN -->
					<?php add_thickbox(); ?>
                    <p>
                        Admin Tools version <?php echo ADMINTOOLSWP_VERSION; ?> &bull;
                        <a href="#TB_inline?width=600&height=550&inlineId=akeeba-changelog" id="btnchangelog" class="thickbox akeeba-btn--dark akeeba-btn--small">CHANGELOG</a>
                        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ControlPanel&task=reloadUpdateInformation" class="akeeba-btn--grey--small">
                            <span class="akion-android-refresh"></span>
                            <?php echo Language::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_RELOADUPDATE'); ?>
                        </a>
                    </p>

                    <div class="modal fade" id="akeeba-changelog" tabindex="-1" role="dialog" aria-labelledby="changelogDialogLabel" aria-hidden="true" style="display:none;">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title" id="changelogDialogLabel">
										<?php echo Language::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_CHANGELOG'); ?>
                                    </h2>
                                </div>
                                <div class="modal-body akeeba-renderer-fef" id="DialogBody">
									<?php echo $this->changeLog; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- CHANGELOG :: END -->

                    <p>
                        Copyright &copy; 2017&ndash;<?php echo date('Y'); ?> Nicholas K. Dionysopoulos / <a href="https://www.akeeba.com">Akeeba Ltd</a>
                    </p>
                </div>

				<?php if (!$this->isPro): ?>
                    <div style="text-align: center;">
                        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                            <input type="hidden" name="cmd" value="_s-xclick">
                            <input type="hidden" name="hosted_button_id" value="6ZLKK32UVEPWA">

                            <p>
                                <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0"
                                       name="submit" alt="PayPal - The safer, easier way to pay online." style="width: 73px;">
                                <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                            </p>
                        </form>
                    </div>
				<?php endif; ?>
            </div>

			<?php if ($this->isPro && $this->showstats): ?>
				<?php include __DIR__ . '/graphs.php'; ?>
				<?php include __DIR__ . '/stats.php'; ?>
			<?php endif; ?>

            <div id="disclaimer" class="akeeba-block--info small">
                <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_DISCLAIMER') ?></h3>

                <p>
					<?php echo Language::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_DISTEXT'); ?>
                </p>
            </div>
        </div>
    </div>
</div>
<?php
if ($this->statsIframe)
{
	echo $this->statsIframe;
}