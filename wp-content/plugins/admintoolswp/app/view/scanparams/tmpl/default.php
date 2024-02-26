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
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=MalwareDetection">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
        <?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
    <?php echo Language::_('COM_ADMINTOOLS_TITLE_SCANPARAMS'); ?>
</h1>

<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=ScanParams" method="post" class="akeeba-form">
    <div class="akeeba-panel--primary">
        <header class="akeeba-block-header">
            <h3><?php echo Language::_('COM_ADMINTOOLS_PARAMS_SCAN_LABEL'); ?></h3>
        </header>


        <div class="akeeba-form-section--horizontal">
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_LOGLEVEL_LABEL')?></label>
                <div>
			        <?php echo Select::logLevel('logLevel', $this->config['logLevel'])?>
                    <p class="akeeba-help-text">
				        <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_LOGLEVEL_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_MINEXEC_LABEL')?></label>
                <div>
                    <input type="number" name="minExec" value="<?php echo $this->config['minExec']?>" size="30" max="30" step="0.5" min="0"/>
                    <p class="akeeba-help-text">
				        <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_MINEXEC_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_MAXEXEC_LABEL')?></label>
                <div>
                    <input type="number" name="maxExec" value="<?php echo $this->config['maxExec']?>" size="30" max="30" step="0.5" min="0"/>
                    <p class="akeeba-help-text">
				        <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_MAXEXEC_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_RUNTIMEBIAS_LABEL')?></label>
                <div>
                    <input type="number" name="runtimeBias" value="<?php echo $this->config['runtimeBias']?>" size="30" max="100" step="1" min="50"/>
                    <p class="akeeba-help-text">
				        <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_RUNTIMEBIAS_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_DIRTHRESHOLD_LABEL')?></label>
                <div>
                    <input type="number" name="dirThreshold" value="<?php echo $this->config['dirThreshold']?>" size="30" max="1000" step="25" min="25"/>
                    <p class="akeeba-help-text">
				        <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_DIRTHRESHOLD_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_FILETHRESHOLD_LABEL')?></label>
                <div>
                    <input type="number" name="fileThreshold" value="<?php echo $this->config['fileThreshold']?>" size="30" max="1000" step="25" min="25"/>
                    <p class="akeeba-help-text">
				        <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_FILETHRESHOLD_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_DIRECTORYFILTERS_LABEL')?></label>
                <div>
                    <textarea cols="80" rows="10" name="directoryFilters"><?php echo $this->config['directoryFilters'] ?></textarea>
                    <p class="akeeba-help-text">
		                <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_DIRECTORYFILTERS_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_FILEFILTERS_LABEL')?></label>
                <div>
                    <textarea cols="80" rows="10" name="fileFilters"><?php echo $this->config['fileFilters'] ?></textarea>
                    <p class="akeeba-help-text">
				        <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_FILEFILTERS_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_SCANEXTENSIONS_LABEL')?></label>
                <div>
                    <input type="text" name="scanExtensions" value="<?php echo $this->config['scanExtensions']?>" />
                    <p class="akeeba-help-text">
				        <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_SCANEXTENSIONS_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_LARGEFILETHRESHOLD_LABEL')?></label>
                <div>
                    <input type="number" name="largeFileThreshold" value="<?php echo $this->config['largeFileThreshold']?>" size="30" max="26214400" step="131072" min="131072"/>
                    <p class="akeeba-help-text">
				        <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_LARGEFILETHRESHOLD_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_PARAMS_SCANDIFF_LABEL')?></label>
                <div>
					<?php echo SelectHelper::booleanswitch('scandiffs', $this->config['scandiffs'])?>
                    <p class="akeeba-help-text">
						<?php echo Language::_('COM_ADMINTOOLS_PARAMS_SCANDIFF_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_SCANIGNORENONTHREATS_LABEL')?></label>
                <div>
			        <?php echo SelectHelper::booleanswitch('scanignorenonthreats', $this->config['scanignorenonthreats'])?>
                    <p class="akeeba-help-text">
				        <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_SCANIGNORENONTHREATS_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_OVERSIZEFILETHRESHOLD_LABEL')?></label>
                <div>
                    <input type="number" name="oversizeFileThreshold" value="<?php echo $this->config['oversizeFileThreshold']?>" size="30" max="52428800" step="524288" min="524288"/>
                    <p class="akeeba-help-text">
				        <?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_OVERSIZEFILETHRESHOLD_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_SCANEMAIL_LABEL')?></label>
                <div>
                    <input type="text" class="regular-text" name="scanemail" value="<?php echo $this->config['scanemail']?>" />
                    <p class="akeeba-help-text">
						<?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_SCANEMAIL_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_SCAN_CONDITIONAL_EMAIL_LABEL')?></label>
                <div>
					<?php echo SelectHelper::booleanswitch('scan_conditional_email', $this->config['scan_conditional_email'])?>
                    <p class="akeeba-help-text">
						<?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_SCAN_CONDITIONAL_EMAIL_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_PARAMS_FRONTEND_LABEL')?></label>
                <div>
					<?php echo SelectHelper::booleanswitch('frontend_enable', $this->config['frontend_enable'])?>
                    <p class="akeeba-help-text">
						<?php echo Language::_('COM_ADMINTOOLS_PARAMS_FEBENABLE_DESC')?>
                    </p>
                </div>
            </div>
            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_PARAMS_SECRETWORD_LABEL')?></label>
                <div>
                    <input type="text" class="regular-text" name="frontend_secret_word" value="<?php echo $this->config['frontend_secret_word']?>" />
                    <p class="akeeba-help-text">
						<?php echo Language::_('COM_ADMINTOOLS_PARAMS_SECRETWORD_DESC')?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <p class="submit">
        <input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>

        <a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=MalwareDetection">
            <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL')?>
        </a>
    </p>

    <input type="hidden" name="view" value="ScanParams"/>
    <input type="hidden" name="task" value="save"/>
	<?php wp_nonce_field('postScanParams') ?>
</form>
