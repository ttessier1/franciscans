<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/* SFTP browser */
?>
<div class="modal fade" id="sftpdialog" tabindex="-1" role="dialog" aria-labelledby="sftpdialogLabel" aria-hidden="true"
     style="display: none;">
    <div class="akeeba-renderer-fef <?php echo ($this->getContainer()->appConfig->get('darkmode', -1) == 1) ? 'akeeba-renderer-fef--dark' : '' ?>">
        <h4 id="sftpdialogLabel">
			@lang('COM_AKEEBA_CONFIG_UI_SFTPBROWSER_TITLE')
        </h4>

        <p class="instructions akeeba-block--info">
			@lang('COM_AKEEBA_SFTPBROWSER_LBL_INSTRUCTIONS')
        </p>

        <div class="error akeeba-block--failure" id="sftpBrowserErrorContainer">
            <h2>@lang('COM_AKEEBA_SFTPBROWSER_LBL_ERROR')</h2>
            <p id="sftpBrowserError"></p>
        </div>

        <ul id="ak_scrumbs" class="breadcrumb"></ul>

        <div class="folderBrowserWrapper" id="sftpBrowserWrapper">
            <table id="sftpBrowserFolderList" class="akeeba-table akeeba-table--striped">
            </table>
        </div>

        <div class="modal-footer">
            <button type="button" id="sftpdialogOkButton" class="akeeba-btn--primary">
                <span class="akion-checkmark"></span>
				@lang('COM_AKEEBA_BROWSER_LBL_USE')
            </button>

            <button type="button" id="sftpdialogCancelButton" class="akeeba-btn--red">
                <span class="akion-ios-close"></span>
				@lang('SOLO_BTN_CANCEL')
            </button>
        </div>

    </div>
</div>
