<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

defined('ADMINTOOLSINC') or die;

?>

<div class="akeeba-form-group">
    <label for="saveusersignupip">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SAVEUSERSIGNUPIP'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('saveusersignupip', $this->wafconfig['saveusersignupip']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SAVEUSERSIGNUPIP_TIP'); ?>
	</p>
</div>

<?php if (ADMINTOOLSWP_PRO): ?>
<div class="akeeba-form-group">
    <label for="logbreaches">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGBREACHES'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('logbreaches', $this->wafconfig['logbreaches']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGBREACHES_TIP'); ?>
	</p>
</div>
<?php else: ?>
    <input type="hidden" name="logbreaches" value="1" />
<?php endif; ?>

<?php if (ADMINTOOLSWP_PRO): ?>
<div class="akeeba-form-group">
    <label for="iplookup">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUP_LABEL'); ?>
    </label>
    
    <div>
        <?php echo Select::httpschemes('iplookupscheme', array('class' => 'input-small'), $this->wafconfig['iplookupscheme']); ?>

        <input type="text" class="regular-text" name="iplookup" value="<?php echo $this->escape($this->wafconfig['iplookup']); ?>"
               title="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUP_DESC'); ?>"/>
    </div>

	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUP_DESC'); ?>
	</p>
</div>
<?php else: ?>
    <input type="hidden" name="iplookupscheme" value="https" />
    <input type="hidden" name="iplookup" value="ip-lookup.net/index.php?ip={ip}" />
<?php endif; ?>

<div class="akeeba-form-group">
    <label for="emailbreaches">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILBREACHES'); ?>
    </label>

	<input type="text" class="regular-text" name="emailbreaches" value="<?php echo $this->escape($this->wafconfig['emailbreaches']); ?>">
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILBREACHES_TIP'); ?>
	</p>
</div>

<?php if (ADMINTOOLSWP_PRO): ?>
<div class="akeeba-form-group">
    <label for="emailonadminlogin">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILADMINLOGIN'); ?>
    </label>

	<input type="text" class="regular-text" name="emailonadminlogin"
		   value="<?php echo $this->escape($this->wafconfig['emailonadminlogin']); ?>" >
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILADMINLOGIN_TIP'); ?>
	</p>
</div>
<?php endif; ?>

<?php if (ADMINTOOLSWP_PRO): ?>
<div class="akeeba-form-group">
    <label for="reasons_nolog">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REASONS_NOLOG'); ?>
    </label>

	<?php
	echo Select::reasons($this->wafconfig['reasons_nolog'], 'reasons_nolog[]', array(
			'class'     => 'advancedSelect input-large',
			'multiple'  => 'multiple',
			'size'      => 5,
			'hideEmpty' => true
		)
	)
	?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REASONS_NOLOG_TIP'); ?>
	</p>
</div>
<?php else: ?>
    <input type="hidden" name="reasons_nolog[]" value="geoblocking" />
<?php endif; ?>

<?php if (ADMINTOOLSWP_PRO): ?>
<div class="akeeba-form-group">
    <label for="reasons_noemail">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REASONS_NOEMAIL'); ?>
    </label>

	<?php
	echo Select::reasons($this->wafconfig['reasons_noemail'], 'reasons_noemail[]', array(
			'class'     => 'advancedSelect input-large',
			'multiple'  => 'multiple',
			'size'      => 5,
			'hideEmpty' => true
		)
	)
	?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REASONS_NOEMAIL_TIP'); ?>
	</p>
</div>
<?php else: ?>
    <input type="hidden" name="reasons_noemail[]" value="geoblocking" />
<?php endif; ?>

<?php if (ADMINTOOLSWP_PRO): ?>
<div class="akeeba-form-group">
    <label for="email_throttle">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILTHROTTLE'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('email_throttle', $this->wafconfig['email_throttle']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILTHROTTLE_TIP'); ?>
	</p>
</div>
<?php endif; ?>