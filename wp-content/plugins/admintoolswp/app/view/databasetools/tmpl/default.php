<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */
use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\Databasetools\Html */
defined('ADMINTOOLSINC') or die;

?>

    <h1>
        <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
            <span class="akion-chevron-left"></span>
            <span class="aklogo-admintools-wp-small"></span>
			<?php echo Language::_('COM_ADMINTOOLS') ?>
        </a>
		<?php echo Language::_('COM_ADMINTOOLS_TITLE_DBTOOLS'); ?>
    </h1>

<section class="akeeba-panel">
	<?php if (!empty($this->table)): ?>
        <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB_INPROGRESS'); ?></h3>
	<?php else: ?>
        <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB_COMPLETE'); ?></h3>
	<?php endif; ?>

    <p>
        <?php echo Language::sprintf('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB_PROGRESS', $this->percent); ?>
    </p>

<?php if (!empty($this->table)): ?>
	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=Databasetools" class="akeeba-form--horizontal" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="view" value="Databasetools"/>
		<input type="hidden" name="task" value="display"/>
        <?php wp_nonce_field('postDatabaseTools')?>
		<input type="hidden" name="from" value="<?php echo $this->escape($this->table); ?>"/>
	</form>
<?php endif; ?>

	<?php if ($this->percent == 100): ?>
        <p id="admintools-databasetools-autoclose">
            <a href="<?php echo ADMINTOOLSWP_URL; ?>" class="akeeba-btn--ghost">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_BACK_CPANEL')?>
            </a>
        </p>
	<?php endif; ?>

</section>
