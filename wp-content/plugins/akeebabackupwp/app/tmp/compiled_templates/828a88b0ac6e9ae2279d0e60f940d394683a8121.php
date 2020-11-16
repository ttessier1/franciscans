<?php /* D:\google\Franciscans\Trillium Website\wp-content\plugins\akeebabackupwp\app\Solo\ViewTemplates\Browser\default.blade.php */ ?>
<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

/** @var \Solo\View\Browser\Html $this */

$router = $this->getContainer()->router;
?>

<?php if(empty($this->folder)): ?>
	<form action="<?php echo $this->container->router->route('index.php?view=browser&tmpl=component&processfolder=0'); ?>" method="post"
		  name="adminForm" id="adminForm">
		<input type="hidden" name="folder" id="folder" value=""/>
		<input type="hidden" name="token"
			   value="<?php echo $this->container->session->getCsrfToken()->getValue(); ?>"/>
	</form>
<?php endif; ?>

<?php if(!(empty($this->folder))): ?>
    <div class="akeeba-panel--100 akeeba-panel--primary">
        <div>
            <form action="<?php echo $this->container->router->route('index.php?view=browser&tmpl=component'); ?>" method="post"
                  name="adminForm" id="adminForm" class="akeeba-form--inline--with-hidden--no-margins">

                <div class="akeeba-form-group">
                    <span title="<?php echo \Awf\Text\Text::_($this->writable ? 'COM_AKEEBA_CPANEL_LBL_WRITABLE' : 'COM_AKEEBA_CPANEL_LBL_UNWRITABLE'); ?>"
                          class="<?php echo $this->writable ? 'akeeba-label--green' : 'akeeba-label--red'; ?>"
                    >
                        <span class="<?php echo $this->writable ? 'akion-checkmark-circled' : 'akion-ios-close'; ?>"></span>
                    </span>
                </div>

                <div class="akeeba-form-group">
                    <input type="text" name="folder" id="folder" size="40"  value="<?php echo $this->escape($this->folder); ?>" />
                </div>

                <div class="akeeba-form-group--action">
                    <button class="akeeba-btn--primary" type="submit">
                        <span class="akion-folder"></span>
                        <?php echo \Awf\Text\Text::_('COM_AKEEBA_BROWSER_LBL_GO'); ?>
                    </button>

                    <button class="akeeba-btn--green" id="comAkeebaBrowserUseThis">
                        <span class="akion-share"></span>
                        <?php echo \Awf\Text\Text::_('COM_AKEEBA_BROWSER_LBL_USE'); ?>
                    </button>
                </div>

                <div class="akeeba-hidden-fields-container">
                    <input type="hidden" name="token" value="<?php echo $this->container->session->getCsrfToken()->getValue(); ?>"/>
                    <input type="hidden" name="folderraw" id="folderraw" value="<?php echo $this->folder_raw ?>"/>
                </div>
            </form>
        </div>
    </div>

    <?php if(count($this->breadcrumbs)): ?>
    <div class="akeeba-panel--100 akeeba-panel--information">
        <div>
            <ul class="akeeba-breadcrumb">
	            <?php $i = 0 ?>
                <?php foreach($this->breadcrumbs as $crumb): ?>
		            <?php $i++; ?>
                    <li class="<?php echo ($i < count($this->breadcrumbs)) ? '' : 'active'; ?>">
                        <?php if($i < count($this->breadcrumbs)): ?>
                            <a href="<?php echo $this->escape($router->route("index.php?view=browser&tmpl=component&folder=" . urlencode($crumb['folder']))); ?>">
                                <?php echo $this->escape($crumb['label']); ?>

                            </a>
                            <span class="divider">&bull;</span>
                        <?php else: ?>
                            <?php echo $this->escape($crumb['label']); ?>

                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="akeeba-panel--100 akeeba-panel">
        <div>
            <?php if(count($this->subfolders)): ?>
                <table class="akeeba-table akeeba-table--striped">
                    <tr>
                        <td>
                            <?php $linkbase = $router->route("index.php?&view=browser&tmpl=component&folder="); ?>
                            <a class="akeeba-btn--dark--small"
                               href="<?php echo $linkbase . urlencode($this->parent); ?>">
                                <span class="akion-arrow-up-a"></span>
                                <?php echo \Awf\Text\Text::_('COM_AKEEBA_BROWSER_LBL_GOPARENT'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php foreach($this->subfolders as $subfolder): ?>
                        <tr>
                            <td>
                                <a href="<?php echo $linkbase . urlencode($this->folder . '/' . $subfolder); ?>"><?php echo htmlentities($subfolder) ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <?php if(!$this->exists): ?>
                    <div class="akeeba-block--failure">
                        <?php echo \Awf\Text\Text::_('COM_AKEEBA_BROWSER_ERR_NOTEXISTS'); ?>
                    </div>
                <?php elseif(!$this->inRoot): ?>
                    <div class="akeeba-block--warning">
                        <?php echo \Awf\Text\Text::_('COM_AKEEBA_BROWSER_ERR_NONROOT'); ?>
                    </div>
                <?php elseif($this->openbasedirRestricted): ?>
                    <div class="akeeba-block--failure">
                        <?php echo \Awf\Text\Text::_('COM_AKEEBA_BROWSER_ERR_BASEDIR'); ?>
                    </div>
                <?php else: ?>
                    <table class="akeeba-table--striped">
                        <tr>
                            <td>
                                <?php $linkbase = $router->route("index.php?&view=browser&tmpl=component&folder="); ?>
                                <a class="akeeba-btn--dark--small"
                                   href="<?php echo $linkbase . urlencode($this->parent); ?>">
                                    <span class="akion-arrow-up-a"></span>
                                    <?php echo \Awf\Text\Text::_('COM_AKEEBA_BROWSER_LBL_GOPARENT'); ?>
                                </a>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?> <?php /* secondary block */ ?>
            <?php endif; ?> <?php /* count($this->subfolders) */ ?>
        </div>
    </div>
<?php endif; ?>