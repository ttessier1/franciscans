<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var    $this   \Akeeba\AdminTools\Admin\View\ConfigureFixPermissions\Html */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;

defined('ADMINTOOLSINC') or die;

$path = $this->at_path;

if (!empty($path))
{
	$path .= '/';
}

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_FIXPERMSCONFIG');?>
</h1>

<section class="akeeba-panel">
    <form name="defaultsForm" id="defaultsForm" action="<?php echo ADMINTOOLSWP_URL; ?>&view=ConfigureFixPermissions" method="post" class="akeeba-form--inline">
        <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DEFAULTS'); ?></h4>

        <label for="perms_show_hidden"><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SHOW_HIDDEN')?></label>
		<?php echo Select::booleanlist('perms_show_hidden', array(), $this->perms_show_hidden)?>

        <label for="dirperms"><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DEFDIRPERM'); ?></label>
		<?php echo Select::perms('dirperms', array(), $this->dirperms); ?>


        <label for="fileperms"><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DEFFILEPERMS'); ?></label>
		<?php echo Select::perms('fileperms', array(), $this->fileperms); ?>


        <input type="submit" class="akeeba-btn--primary"
               value="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEDEFAULTS'); ?>"/>

        <input type="hidden" name="view" value="ConfigureFixPermissions"/>
        <input type="hidden" name="task" value="savedefaults"/>
		<?php wp_nonce_field('postConfigureFixPermissions') ?>
    </form>
</section>

<section class="akeeba-panel">
    <?php if (!empty($this->listing['crumbs'])): ?>
        <ul class="ak-breadcrumb">
            <li>
                <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_PATH'); ?>:
                <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ConfigureFixPermissions&path=/">
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_ROOT'); ?>
                </a>
                <span class="divider">/</span>
            </li>

            <?php $relpath = '';
            $i = 1; ?>
            <?php foreach ($this->listing['crumbs'] as $crumb): ?>
                <?php $i++;
                if (empty($crumb))
                {
                    continue;
                } ?>
                <?php $relpath = ltrim($relpath . '/' . $crumb, '/'); ?>
                <li>
                    <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ConfigureFixPermissions&path=<?php echo $this->escape(urlencode($relpath)); ?>">
                        <?php echo $this->escape($this->escape($crumb)); ?>

                    </a>
                    <?php if ($i < count($this->listing['crumbs'])): ?>
                        <span class="divider">/</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif ?>

    <form name="adminForm" id="adminForm" action="<?php echo ADMINTOOLSWP_URL; ?>&view=ConfigureFixPermissions" method="post" class="akeeba-form">
        <p>
        <input type="submit" class="akeeba-btn--green" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEPERMS'); ?>"/>
        <input type="submit" class="akeeba-btn--orange"
               value="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEAPPLYPERMS'); ?>"
               onclick="document.forms.adminForm.task.value='saveapplyperms';"/>
        </p>
        <div class="akeeba-container--50-50">
            <table class="akeeba-table--striped">
                <thead>
                <tr>
                    <th><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_FOLDER'); ?></th>
                    <th><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_OWNER'); ?></th>
                    <th colspan="2"><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_PERMS'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($this->listing['folders'])) foreach ($this->listing['folders'] as $folder): ?>
                    <tr>
                        <td>
                            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ConfigureFixPermissions&path=<?php echo $this->escape(urlencode($folder['path'])); ?>">
                                <?php echo $this->escape($this->escape($folder['item'])); ?>

                            </a>
                        </td>
                        <td>
                            <?php echo $this->escape($this->renderUGID($folder['uid'], $folder['gid'])); ?>

                        </td>
                        <td>
                            <?php echo $this->escape($this->renderPermissions($folder['realperms'])); ?>

                        </td>
                        <td align="right">
                            <?php echo Select::perms('folders[' . $folder['path'] . ']', array(), $folder['perms']); ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <table class="akeeba-table--striped">
                <thead>
                <tr>
                    <th><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_FILE'); ?></th>
                    <th><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_OWNER'); ?></th>
                    <th colspan="2"><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_PERMS'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($this->listing['files'])) foreach ($this->listing['files'] as $file): ?>
                    <tr>
                        <td>
                            <?php echo $this->escape($this->escape($file['item'])); ?>

                        </td>
                        <td>
                            <?php echo $this->escape($this->renderUGID($file['uid'], $file['gid'])); ?>

                        </td>
                        <td>
                            <?php echo $this->escape($this->renderPermissions($file['realperms'])); ?>

                        </td>
                        <td align="right">
                            <?php echo Select::perms('files[' . $file['path'] . ']', array(), $file['perms']); ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p></p>

        <p>
            <input type="submit" class="akeeba-btn--green"
                   value="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEPERMS'); ?>"/>
            <input type="submit" class="akeeba-btn--orange"
                   value="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEAPPLYPERMS'); ?>"
                   onclick="document.forms.adminForm.task.value='saveapplyperms';"/>
        </p>

        <input type="hidden" name="view" value="ConfigureFixPermissions"/>
        <input type="hidden" name="task" value="saveperms"/>
        <input type="hidden" name="path" value="<?php echo $this->escape($this->at_path); ?>"/>
        <?php wp_nonce_field('postConfigureFixPermissions') ?>
    </form>
</section>