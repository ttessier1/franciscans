<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

/** @var $this Akeeba\AdminTools\Admin\View\Params\Html */

defined('ADMINTOOLSINC') or die;

?>
<div class="akeeba-form-section--horizontal">
    <div class="akeeba-form-group">
        <label><?php echo Language::_('COM_ADMINTOOLS_PARAMS_MAXLOGENTRIES_LBL')?></label>
        <div>
            <input type="text" class="regular-text" name="maxlogentries" value="<?php echo $this->config['maxlogentries']?>" />

            <p class="akeeba-help-text">
				<?php echo Language::_('COM_ADMINTOOLS_PARAMS_MAXLOGENTRIES_DESC')?>
            </p>
        </div>
    </div>

    <div class="akeeba-form-group">
        <label><?php echo Language::_('COM_ADMINTOOLS_PARAMS_LONGCONFIGPAGE_LABEL')?></label>
        <div>
			<?php echo SelectHelper::booleanswitch('longConfig', $this->config['longConfig'])?>

            <p class="akeeba-help-text">
				<?php echo Language::_('COM_ADMINTOOLS_PARAMS_LONGCONFIGPAGE_DESC')?>
            </p>
        </div>
    </div>

	<?php if (ADMINTOOLSWP_PRO): ?>
    <div class="akeeba-form-group">
        <label><?php echo Language::_('COM_ADMINTOOLS_PARAMS_SHOWSTATS_LABEL')?></label>
        <div>
			<?php echo SelectHelper::booleanswitch('showstats', $this->config['showstats'])?>

            <p class="akeeba-help-text">
				<?php echo Language::_('COM_ADMINTOOLS_PARAMS_SHOWSTATS_DESC')?>
            </p>
        </div>
    </div>
	<?php endif; ?>

    <div class="akeeba-form-group">
        <label><?php echo Language::_('COM_ADMINTOOLS_PARAMS_USAGESTATS_LABEL')?></label>
        <div>
			<?php echo SelectHelper::booleanswitch('stats_enabled', $this->config['stats_enabled'])?>

            <p class="akeeba-help-text">
				<?php echo Language::_('COM_ADMINTOOLS_PARAMS_USAGESTATS_DESC')?>
            </p>
        </div>
    </div>

    <div class="akeeba-form-group">
        <label><?php echo Language::_('COM_ADMINTOOLS_PARAMS_EMAILTIMEZONE_LABEL')?></label>
        <div>
			<?php echo Select::timezones('email_timezone', null, $this->config['email_timezone'])?>

            <p class="akeeba-help-text">
				<?php echo Language::_('COM_ADMINTOOLS_PARAMS_EMAILTIMEZONE_DESC')?>
            </p>
        </div>
    </div>

	<div class="akeeba-form-group">
		<label for="darkmode">
			<?php echo Language::_('COM_ADMINTOOLS_PARAMS_DARKMODE_LABEL')?>
		</label>
		<div class="akeeba-toggle">
			<?php echo SelectHelper::radiolist([
				SelectHelper::option('0', Language::_('JNO')),
				SelectHelper::option('-1', Language::_('COM_ADMINTOOLS_PARAMS_DARKMODE_AUTO')),
				SelectHelper::option('1', Language::_('JYES')),
			], 'darkmode', ['forToggle' => 1, 'classMaps' => [0 => 'red', -1 => 'orange', 1 => 'green']], 'value', 'text', (int) $this->config['darkmode'], 'darkmode') ?>
		</div>
		<p class="akeeba-help-text">
			<?php echo Language::_('COM_ADMINTOOLS_PARAMS_DARKMODE_DESC')?>
		</p>
	</div>
</div>
