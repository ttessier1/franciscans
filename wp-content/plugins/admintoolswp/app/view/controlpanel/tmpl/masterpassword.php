<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

/** @var  \Akeeba\AdminTools\Admin\View\ControlPanel\Html $this For type hinting in the IDE */

defined('ADMINTOOLSINC') or die;

?>
<div class="akeeba-panel--danger">
	<header class="akeeba-block-header">
		<h2>
			<span class="akion-locked"></span>
			<?php echo Language::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_MASTERPWHEAD'); ?>
		</h2>
	</header>
	<p>
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_MASTERPWINTRO'); ?>
	</p>
	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=ControlPanel" method="post" name="adminForm" id="adminForm" class="akeeba-form--inline">
		<input type="hidden" name="view" value="ControlPanel"/>
		<input type="hidden" name="task" value="login"/>

		<div class="akeeba-form-group">
			<label for="userpw">
				<?php echo Language::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_MASTERPW'); ?>
			</label>
			<input type="password" name="userpw" id="userpw"/>
		</div>

		<div class="akeeba-form-actions">
			<input type="submit" class="akeeba-btn" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_MASTERPWSUBMIT'); ?>"/>
		</div>
	</form>
</div>
