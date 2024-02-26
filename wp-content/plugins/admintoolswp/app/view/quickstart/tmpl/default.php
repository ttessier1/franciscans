<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var Akeeba\AdminTools\Admin\View\QuickStart\Html $this */
use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

defined('ADMINTOOLSINC') or die;

$formStyle    = $this->isFirstRun ? '' : 'display: none';
$warningStyle = $this->isFirstRun ? 'display: none' : '';
?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
        <span class="akion-chevron-left"></span><span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS').'</a>'.Language::_('COM_ADMINTOOLS_TITLE_QUICKSTART'); ?>
</h1>

<section class="akeeba-panel">
    <div class="akeeba-block--failure" style="<?php echo $this->escape($warningStyle); ?>" id="youhavebeenwarnednottodothat">
        <h4>
            <?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_HEAD'); ?>
        </h4>

        <p>
            <?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_BODY'); ?>
        </p>

        <p>
            <a href="<?php echo ADMINTOOLSWP_URL; ?>" class="akeeba-btn--success--big">
                <span class="icon icon-home"></span>
                <strong>
                    <?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_BTN_NO'); ?>
                </strong>
            </a>
            &nbsp;&nbsp;&nbsp;
            <a onclick="admintools.QuickStart.youWantToBreakYourSite(); return false;"
                class="akeeba-btn--ghost--small">
                <span class="icon icon-white icon-warning"></span>
                <?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_BTN_YES'); ?>
            </a>
        </p>
    </div>

    <form name="adminForm" id="adminForm" action="<?php echo ADMINTOOLSWP_URL; ?>&view=QuickStart" method="post" class="akeeba-form" style="<?php echo $this->escape($formStyle); ?>">
        <div class="akeeba-block--info" style="<?php echo $this->escape($formStyle); ?>">
            <p>
                <?php echo Language::sprintf('COM_ADMINTOOLS_QUICKSTART_INTRO', 'https://www.akeeba.com/documentation/admin-tools.html'); ?>
            </p>
        </div>

        <div class="akeeba-block--failure" style="<?php echo $this->escape($warningStyle); ?>">
            <h1>
                <?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_NOSUPPORT_HEAD'); ?>
            </h1>
            <p>
                <?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_NOSUPPORT_BODY'); ?>
            </p>
        </div>

        <div class="akeeba-panel--primary">
            <header class="akeeba-block-header">
                <h3><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_HEAD_ADMINSECURITY'); ?></h3>
            </header>

            <div class="akeeba-form-section--horizontal">
                <?php if ($this->hasHtaccess): ?>
                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_TITLE_ADMINPW'); ?></label>
                    <div>
                        <input type="text" name="admin_username" id="admin_username" value="<?php echo $this->escape($this->admin_username); ?>" autocomplete="off"
                               placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_USERNAME'); ?>"
                        />
                        <input type="text" name="admin_password" id="admin_password" value="<?php echo $this->escape($this->admin_password); ?>" autocomplete="off"
                               placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD'); ?>"
                        />
                    </div>
					<p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ADMINISTRATORPASSORD_INFO'); ?></p>
				</div>
                <?php endif; ?>

                <?php // The following features are available only in Pro versions ?>
                <?php if ($this->isPro): ?>
                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ADMINLOGINEMAIL_LBL'); ?></label>
					<input type="text" size="20" name="emailonadminlogin"
						   value="<?php echo $this->escape($this->wafconfig['emailonadminlogin']); ?>" >
					<p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ADMINLOGINEMAIL_DESC'); ?></p>
                </div>

                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_IPWL'); ?></label>
	                <?php echo SelectHelper::booleanswitch('ipwl', $this->wafconfig['ipwl']); ?>
					<p class="akeeba-help-text"><?php echo Language::sprintf('COM_ADMINTOOLS_QUICKSTART_WHITELIST_DESC', $this->myIp); ?></p>
                </div>


                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NONEWADMINS'); ?></label>
	                <?php echo SelectHelper::booleanswitch('nonewadmins', $this->wafconfig['nonewadmins']); ?>
					<p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_NONEWADMINS_DESC'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="akeeba-panel--primary">
            <header class="akeeba-block-header">
                <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_BASIC'); ?></h3>
            </header>

            <div class="akeeba-form-section--horizontal">
                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ENABLEWAF_LBL'); ?></label>
	                <?php echo SelectHelper::booleanswitch('enablewaf', 1); ?>
					<p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ENABLEWAF_DESC'); ?></p>
                </div>

                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_CONFIGUREWAF_OPT_IPWORKAROUNDS'); ?></label>
	                <?php echo SelectHelper::booleanswitch('ipworkarounds', 0); ?>
					<p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_CONFIGUREWAF_IPWORKAROUNDS_TIP'); ?></p>
                </div>

                <?php if ($this->isPro): ?>
                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_AUTOBAN_LBL'); ?></label>
	                <?php echo SelectHelper::booleanswitch('autoban', 1); ?>
					<p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_AUTOBAN_DESC'); ?></p>
                </div>

                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_AUTOBLACKLIST_LBL'); ?></label>
	                <?php echo SelectHelper::booleanswitch('autoblacklist', 0); ?>
					<p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_AUTOBLACKLIST_DESC'); ?></p>
                </div>
                <?php endif; ?>

                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILBREACHES'); ?></label>
					<input type="text" size="20" name="emailbreaches" value="<?php echo $this->escape($this->wafconfig['emailbreaches']); ?>">
					<p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILBREACHES_TIP'); ?></p>
                </div>

                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SELFPROTECT'); ?></label>

	                <?php echo SelectHelper::booleanswitch('selfprotect', $this->wafconfig['selfprotect']); ?>
					<p class="akeeba-help-text">
		                <?php echo Language::_('COM_ADMINTOOLS_CONFIGUREWAF_SELFPROTECT_TIP') ?>
					</p>
                </div>

            </div>
        </div>

        <div class="akeeba-panel--primary">
            <header class="akeeba-block-header">
                <h3><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_HEAD_ADVANCEDSECURITY'); ?></h3>
            </header>

            <div class="akeeba-form-section--horizontal">
                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BBHTTPBLKEY'); ?></label>
					<input type="text" size="45" name="bbhttpblkey" value="<?php echo $this->escape($this->wafconfig['bbhttpblkey']); ?>"/>
					<p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BBHTTPBLKEY_TIP'); ?></p>
                </div>

                <?php if ($this->hasHtaccess && $this->isPro): ?>
                <div class="akeeba-form-group">
                    <label><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_HTMAKER_LBL'); ?></label>
	                <?php echo SelectHelper::booleanswitch('htmaker', 0); ?>
					<p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_HTMAKER_DESC');?></p>
                </div>
                <?php endif; ?>
            </div>

        </div>

        <h3><?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_HEAD_ALMOSTTHERE'); ?></h3>

        <p>
            <?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ALMOSTTHERE_INTRO'); ?>
        </p>

        <ul>
            <li>
                <a href="http://akee.ba/wplockedout">http://akee.ba/wplockedout</a>
            </li>
            <li>
                    <a href="http://akee.ba/wp500htaccess">http://akee.ba/wp500htaccess</a>
            </li>
            <li>
                <a href="http://akee.ba/wpadminpassword">http://akee.ba/wpadminpassword</a>
            </li>
            <li>
                <a href="http://akee.ba/wp403edituser">http://akee.ba/wp403edituser</a>
            </li>
        </ul>
        <p>
            <?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ALMOSTTHERE_OUTRO'); ?>
        </p>

        <div class="akeeba-block--failure" style="<?php echo $this->escape($warningStyle); ?>">
            <h1>
                <?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_NOSUPPORT_HEAD'); ?>
            </h1>
            <p>
                <?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_NOSUPPORT_BODY'); ?>
            </p>
        </div>

        <div class="form-actions" style="<?php echo $this->escape($formStyle); ?>">
            <button type="submit" class="akeeba-btn--primary">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE'); ?>
            </button>
        </div>

        <div class="form-actions" style="<?php echo $this->escape($warningStyle); ?>">
            <button type="submit" class="akeeba-btn--red">
                <span class="icon icon-white icon-warning"></span>
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE'); ?>
            </button>

            <a href="<?php echo ADMINTOOLSWP_URL; ?>"
               class="akeeba-btn--big--primary">
                <span class="icon icon-home"></span>
                <strong>
                    <?php echo Language::_('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_BTN_NO'); ?>
                </strong>
            </a>
        </div>

        <input type="hidden" name="view" value="QuickStart"/>
        <input type="hidden" name="task" value="commit"/>
        <input type="hidden" name="detectedip" id="detectedip" value=""/>
		<?php wp_nonce_field('postQuickStart') ?>
    </form>
</section>

<script>
    akeeba.jQuery(document).ready(function ($){
        admintools.QuickStart.myIP = '<?php echo $this->myIp; ?>';
    });
</script>
