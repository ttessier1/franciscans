<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var $this \Akeeba\AdminTools\Admin\View\WebConfigMaker\Html */

// Protect from unauthorized access
defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

$config = $this->wcconfig;

?>
<div>
    <h1>
        <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
            <span class="akion-chevron-left"></span>
            <span class="aklogo-admintools-wp-small"></span>
        <?php echo Language::_('COM_ADMINTOOLS').'</a>'.Language::_('COM_ADMINTOOLS_TITLE_WCMAKER'); ?>
    </h1>

    <form name="adminForm" id="adminForm" action="<?php echo ADMINTOOLSWP_URL; ?>&view=WebConfigMaker" method="post" class="akeeba-form">
        <div class="akeeba-block--info">
            <p>
                <strong>
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WILLTHISWORK'); ?>
                </strong>
            </p>
            <p>
                <?php if ($this->isSupported == 0): ?>
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WILLTHISWORK_NO'); ?>
                <?php elseif ($this->isSupported == 1): ?>
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WILLTHISWORK_YES'); ?>
                <?php else: ?>
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WILLTHISWORK_MAYBE'); ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="akeeba-block--warning">
            <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WARNING'); ?></h3>

            <p><?php echo Language::_('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WARNTEXT'); ?></p>

            <p><?php echo Language::_('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_TUNETEXT'); ?></p>
        </div>

        <div class="akeeba-panel--primary">
            <header class="akeeba-block-header">
                <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_BASICSEC'); ?></h3>
            </header>

            <div class="akeeba-form-section--horizontal">
                <div class="akeeba-form-group">
                    <label for="nodirlists"><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_NODIRLISTS'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('nodirlists', $config->nodirlists); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_FILEINJ'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('fileinj', $config->fileinj); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_PHPEASTER'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('phpeaster', $config->phpeaster); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_LEFTOVERS'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('leftovers', $config->leftovers); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_CLICKJACKING'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('clickjacking', $config->clickjacking); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REDUCEMIMETYPERISKS'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('reducemimetyperisks', $config->reducemimetyperisks); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFLECTEDXSS'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('reflectedxss', $config->reflectedxss); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_NOSERVERSIGNATURE'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('noserversignature', $config->noserversignature); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_NOTRANSFORM'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('notransform', $config->notransform); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_NOHOGGERS'); ?></label>
                    <div>
                        <?php echo SelectHelper::booleanswitch('nohoggers', $config->nohoggers); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_HOGGERAGENTS'); ?></label>
                    <div>
						<textarea cols="80" rows="10" name="hoggeragents" id="hoggeragents"
                                  class="input-wide"><?php echo implode("\n", $config->hoggeragents); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="akeeba-panel--primary">
            <header class="akeeba-block-header">
                <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SERVERPROT'); ?></h3>
            </header>

            <div class="akeeba-form-section--horizontal">
                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ENABLE_PROTECTION'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('siteprot', $config->siteprot); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXTYPES'); ?></label>
                    <div>
						<textarea cols="80" rows="10" name="extypes"
                                  id="extypes"><?php echo $this->escape(implode("\n", $config->extypes)); ?></textarea>
                    </div>
                </div>

                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SERVERPROT_EXCEPTIONS'); ?></h4>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXCEPTIONFILES'); ?></label>
                    <div>
						<textarea cols="80" rows="10" name="exceptionfiles"
                                  id="exceptionfiles"><?php echo $this->escape(implode("\n", $config->exceptionfiles)); ?></textarea>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXDIRS'); ?></label>
                    <div>
						<textarea cols="80" rows="10" name="exdirs"
                                  id="exdirs"><?php echo $this->escape(implode("\n", $config->exdirs)); ?></textarea>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXCEPTIONDIRS'); ?></label>
                    <div>
						<textarea cols="80" rows="10" name="exceptiondirs"
                                  id="exceptiondirs"><?php echo $this->escape(implode("\n", $config->exceptiondirs)); ?></textarea>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_FULLACCESSDIRS'); ?></label>
                    <div>
						<textarea cols="80" rows="10" name="fullaccessdirs"
                                  id="fullaccessdirs"><?php echo $this->escape(implode("\n", $config->fullaccessdirs)); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="akeeba-panel--primary">
            <header class="akeeba-block-header">
                <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_OPTUTIL'); ?></h3>
            </header>

            <div class="akeeba-form-section--horizontal">
                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_FILEORDER'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('fileorder', $config->fileorder); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXPTIME'); ?></label>
                    <div>
	                    <?php echo Select::exptime('exptime', null, $config->exptime); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOCOMPRESS'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('autocompress', $config->autocompress); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOROOT'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('autoroot', $config->autoroot); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WWWREDIR'); ?></label>
                    <div>
						<?php echo Select::wwwredirs('wwwredir', null, $config->wwwredir); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_OLDDOMAIN'); ?></label>
                    <div>
                        <input type="text" name="olddomain" id="olddomain" class="akeeba-input--block"
                               value="<?php echo $this->escape($config->olddomain); ?>">
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_HTTPSURLS'); ?></label>
                    <div>
                        <textarea cols="80" rows="10" name="httpsurls"
                                  id="httpsurls"><?php echo $this->escape(implode("\n", $config->httpsurls)); ?></textarea>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_HSTSHEADER'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('hstsheader', $config->hstsheader); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_NOTRACETRACK'); ?></label>
                    <div>
						<?php echo SelectHelper::booleanswitch('notracetrack', $config->notracetrack); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_CORS'); ?></label>
                    <div>
						<?php echo Select::cors('cors', [], $config->cors); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE'); ?></label>
                    <div>
						<?php echo Select::etagtypeIIS('etagtype', array('class' => 'input-medium'), $config->etagtype); ?>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for="referrerpolicy"><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY'); ?></label>

					<?php echo Select::referrerpolicy('referrerpolicy', array(), $config->referrerpolicy); ?>
                </div>
            </div>
        </div>

        <div class="akeeba-panel--primary">
            <header class="akeeba-block-header">
                <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SYSCONF'); ?></h3>
            </header>

            <div class="akeeba-form-section--horizontal">
                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_HTTPSHOST'); ?></label>
                    <div>
                        <input type="text" name="httpshost" id="httpshost" value="<?php echo $this->escape($config->httpshost); ?>">
                    </div>
                </div>
                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_HTTPHOST'); ?></label>
                    <div>
                        <input type="text" name="httphost" id="httphost" value="<?php echo $this->escape($config->httphost); ?>">
                    </div>
                </div>
                <div class="akeeba-form-group">
                    <label for=""><?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REWRITEBASE'); ?></label>
                    <div>
                        <input type="text" name="rewritebase" id="rewritebase" value="<?php echo $this->escape($config->rewritebase); ?>">
                    </div>
                </div>
            </div>
        </div>

        <p class="submit">
            <input type="submit" class="akeeba-btn--primary--small" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
            <input type="submit" class="akeeba-btn--green" onclick="document.getElementById('task').value = 'apply'" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WRITE')?>"/>
            <a class="akeeba-btn--red--small" href="<?php echo ADMINTOOLSWP_URL; ?>&view=ControlPanel">
			    <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
            </a>
        </p>

        <input type="hidden" name="view" value="HtaccessMaker"/>
        <input type="hidden" id="task" name="task" value="save"/>
		<?php wp_nonce_field('postHtaccessMaker')?>
    </form>
</div>
