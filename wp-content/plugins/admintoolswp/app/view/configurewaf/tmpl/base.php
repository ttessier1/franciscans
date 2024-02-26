<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

defined('ADMINTOOLSINC') or die;

$serverTZName = Wordpress::get_timezone_string();

try
{
	$timezone = new DateTimeZone($serverTZName);
}
catch (Exception $e)
{
	$timezone = new DateTimeZone('UTC');
}

$date         = new DateTime('now', $timezone);
$timezoneName = $date->format('T');
?>
<div class="akeeba-form-group">
    <label for="logfile">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGFILE'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('logfile', $this->wafconfig['logfile']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGFILE_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="ipworkarounds">
		<?php echo Language::_('COM_ADMINTOOLS_CONFIGUREWAF_OPT_IPWORKAROUNDS'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('ipworkarounds', $this->wafconfig['ipworkarounds']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_CONFIGUREWAF_IPWORKAROUNDS_TIP'); ?>
	</p>
</div>

<?php if(ADMINTOOLSWP_PRO): ?>
<div class="akeeba-form-group">
    <label for="ipwl">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_IPWL'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('ipwl', $this->wafconfig['ipwl']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::sprintf('COM_ADMINTOOLS_CONFIGUREWAF_IPWL_TIP', ADMINTOOLSWP_URL . '&view=WhitelistedAddresses') ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="ipbl">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_IPBL'); ?>
    </label>
	<?php echo SelectHelper::booleanswitch('ipbl', $this->wafconfig['ipbl']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::sprintf('COM_ADMINTOOLS_CONFIGUREWAF_IPBL_TIP', ADMINTOOLSWP_URL . '&view=BlacklistedAddresses') ?>
	</p>
</div>
<?php endif; ?>

<?php if (ADMINTOOLSWP_PRO): ?>
<div class="akeeba-form-group">
    <label for="adminlogindir">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINDIR'); ?>
    </label>

	<input type="text" size="20" name="adminlogindir" id="adminlogindir"
		   value="<?php echo $this->escape($this->wafconfig['adminlogindir']); ?>"/>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINDIR_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="customregister">
		<span class="akeeba-hidden-desktop">
			<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINDIR_CUSTOMREGISTER'); ?>
		</span>
    </label>

	<input type="text" size="20" name="customregister" id="customregister"
		   value="<?php echo $this->escape($this->wafconfig['customregister']); ?>"/>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINDIR_CUSTOMREGISTER_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="adminlogindir_action">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINDIR_ACTION'); ?>
    </label>


    <?php echo Select::custom_admin_actions('adminlogindir_action', $this->wafconfig['adminlogindir_action']) ?>
    <p class="akeeba-help-text">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINDIR_ACTION_TIP'); ?>
    </p>
</div>
<?php endif; ?>

<div class="akeeba-form-group">
    <label for="selfprotect">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SELFPROTECT'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('selfprotect', $this->wafconfig['selfprotect']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_CONFIGUREWAF_SELFPROTECT_TIP') ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="awayschedule_from"
           data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_AWAYSCHEDULE_TIP'); ?>">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_AWAYSCHEDULE'); ?>
    </label>

    <div>
		<?php echo Language::sprintf('COM_ADMINTOOLS_LBL_CONFIGUREWAF_AWAYSCHEDULE_FROM', $timezoneName); ?>
        <input type="text" name="awayschedule_from" id="awayschedule_from" class="input-mini"
               value="<?php echo $this->wafconfig['awayschedule_from'] ?>"/>
		<?php echo Language::sprintf('COM_ADMINTOOLS_LBL_CONFIGUREWAF_AWAYSCHEDULE_TO', $timezoneName); ?>
        <input type="text" name="awayschedule_to" id="awayschedule_to" class="input-mini"
               value="<?php echo $this->escape($this->wafconfig['awayschedule_to']); ?>"/>

        <div class="akeeba-block--info" style="margin-top: 10px">
			<?php echo Language::sprintf('COM_ADMINTOOLS_LBL_CONFIGUREWAF_AWAYSCHEDULE_TIMEZONE', $date->format('H:i T'), $serverTZName); ?>
        </div>
    </div>
</div>
