<?php
/** no direct access * */
defined('_WPLEXEC') or die('Restricted access');

if($type == 'date' and !$done_this)
{
?>
<div class="fanc-body">
	<div class="fanc-row fanc-button-row-2">
		<input class="wpl-button button-1" type="button" onclick="save_dbst('<?php echo $__prefix; ?>', <?php echo $dbst_id; ?>);" value="<?php echo __('Save', WPL_TEXTDOMAIN); ?>" id="wpl_dbst_submit_button" />
		<span id="wpl_dbst_modify_ajax_loader"></span>
	</div>
	<div class="col-wp">
		<div class="col-fanc-left" id="wpl_flex_general_options">
			<div class="fanc-row fanc-inline-title">
				<?php echo __('General Options', WPL_TEXTDOMAIN); ?>
			</div>
			<?php
				/** include main file * */
				$path = _wpl_import('libraries.dbst_modify.main.main', true, true);
				include $path;
			?>
		</div>
		<div class="col-fanc-right" id="wpl_flex_specific_options">
            <?php if($kind != 2): ?>
			<div class="fanc-row fanc-inline-title">
				<?php echo __('Specific Options', WPL_TEXTDOMAIN); ?>
			</div>
			<?php
				/** include specific file * */
				$path = _wpl_import('libraries.dbst_modify.main.specific', true, true);
				include $path;
			?>
            <?php endif; ?>
			<div class="fanc-row fanc-inline-title">
				<?php echo __('Date Range', WPL_TEXTDOMAIN); ?>
			</div>
			<div class="fanc-row">
				<label for="<?php echo $__prefix; ?>opt_minimum_date"><?php echo __('Min date', WPL_TEXTDOMAIN); ?></label>
				<input type="text" name="<?php echo $__prefix; ?>opt_minimum_date" id="<?php echo $__prefix; ?>opt_minimum_date" value="<?php echo (isset($options['minimum_date']) ? $options['minimum_date'] : '1970-01-01'); ?>" />
			</div>
			<div class="fanc-row">
				<label for="<?php echo $__prefix; ?>opt_maximum_date"><?php echo __('Max date', WPL_TEXTDOMAIN); ?></label>
				<input type="text" name="<?php echo $__prefix; ?>opt_maximum_date" id="<?php echo $__prefix; ?>opt_maximum_date" value="<?php echo (isset($options['maximum_date']) ? $options['maximum_date'] : 'now'); ?>" />
			</div>
		</div>
	</div>
</div>
<?php
    $done_this = true;
}