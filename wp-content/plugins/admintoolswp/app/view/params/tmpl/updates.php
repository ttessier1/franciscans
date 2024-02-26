<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;

defined('ADMINTOOLSINC') or die;

?>
<div class="akeeba-form-section--horizontal">
	<?php if (ADMINTOOLSWP_PRO): ?>
    <div class="akeeba-form-group">
        <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_DOWNLOADID')?></label>
        <div>
            <input type="text" class="regular-text" name="downloadid" value="<?php echo $this->config['downloadid']?>" />
        </div>
    </div>
	<?php else: ?>
		<input type="hidden" name="downloadid" value="" />
	<?php endif; ?>

    <div class="akeeba-form-group">
        <label><?php echo Language::_('COM_ADMINTOOLS_PARAMS_MINSTABILITY_LABEL')?></label>
        <div>
			<?php echo Select::minimumStability('minstability', $this->config['minstability'])?>

            <p class="akeeba-help-text">
				<?php echo Language::_('COM_ADMINTOOLS_PARAMS_MINSTABILITY_DESC')?>
            </p>
        </div>
    </div>
</div>
