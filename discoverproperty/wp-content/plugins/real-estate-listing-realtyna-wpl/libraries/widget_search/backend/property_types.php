<?php
/** no direct access * */
defined('_WPLEXEC') or die('Restricted access');

if($type == 'property_types' and !$done_this)
{
    $listings = wpl_global::get_property_types();
?>
<div class="search-field-wp search-field-property-type <?php echo (isset($value['enable']) ? $value['enable'] : ''); ?>" data-field-id="<?php echo $field->id; ?>" data-status="<?php echo (isset($value['enable']) ? $value['enable'] : ''); ?>" data-field-name="<?php echo __($field->name, WPL_TEXTDOMAIN); ?>" data-field-order="<?php echo (isset($value['sort']) ? $value['sort'] : ''); ?>">

	<input type="hidden" id="field_sort_<?php echo $field->id; ?>" name="<?php echo $this->get_field_name('data'); ?>[<?php echo $field->id; ?>][sort]" value="<?php echo (isset($value['sort']) ? $value['sort'] : ''); ?>" />
	<input type="hidden" id="field_enable_<?php echo $field->id; ?>" onchange="elementChanged(true);" name="<?php echo $this->get_field_name('data'); ?>[<?php echo $field->id; ?>][enable]" value="<?php echo (isset($value['enable']) ? $value['enable'] : ''); ?>" />
	<input type="hidden" id="field_id_<?php echo $field->id; ?>" name="<?php echo $this->get_field_name('data'); ?>[<?php echo $field->id; ?>][id]" value="<?php echo $value['id']; ?>" />

	<h4>
		<span>
			<?php echo __($field->name, WPL_TEXTDOMAIN); ?>
		</span>
	</h4>
	<div class="field-body">
		<div class="erow">
			<select name="<?php echo $this->get_field_name('data'); ?>[<?php echo $field->id; ?>][type]" onchange="selectChange(this,'property_types');" >
				<option value="select" <?php if (isset($value['type']) and $value['type'] == "select") echo 'selected="selected"'; ?> ><?php echo __('Select box', WPL_TEXTDOMAIN); ?></option>
				<option value="multiple" <?php if (isset($value['type']) and $value['type'] == "multiple") echo 'selected="selected"'; ?>><?php echo __('Multiple SelectBox', WPL_TEXTDOMAIN); ?></option>
				<option value="checkboxes" <?php if (isset($value['type']) and $value['type'] == "checkboxes") echo 'selected="selected"'; ?>><?php echo __('Check boxes', WPL_TEXTDOMAIN); ?></option>
				<option value="radios" <?php if (isset($value['type']) and $value['type'] == "radios") echo 'selected="selected"'; ?>><?php echo __('Radio Buttons', WPL_TEXTDOMAIN); ?></option>
				<option value="radios_any" <?php if (isset($value['type']) and $value['type'] == "radios_any") echo 'selected="selected"'; ?>><?php echo __('Radio buttons with any', WPL_TEXTDOMAIN); ?></option>
				<option value="predefined" <?php if (isset($value['type']) and $value['type'] == "predefined") echo 'selected="selected"'; ?>><?php echo __('Predefined', WPL_TEXTDOMAIN); ?></option>
				<option value="select-predefined" <?php if (isset($value['type']) and $value['type'] == "select-predefined") echo 'selected="selected"'; ?>><?php echo __('Select Box from predefined items', WPL_TEXTDOMAIN); ?></option>
			</select>
		</div>
		<div class="erow wpl_extoptions_span <?php echo (isset($value['type']) ? $value['type'] : ''); ?>" id="wpl_extoptions_span_<?php echo $field->id; ?>_1">
			<select name="<?php echo $this->get_field_name('data'); ?>[<?php echo $field->id; ?>][extoption][]" id="wpl_extoptions_select_<?php echo $field->id; ?>" <?php if (isset($value['type']) and $value['type'] == "select-predefined") echo 'multiple="multiple"'; ?>>
				<?php foreach ($listings as $list): ?>
					<option <?php if (isset($value['extoption']) and in_array($list['id'], $value['extoption'])) echo 'selected="selected"'; ?> value="<?php echo $list['id']; ?>"><?php echo $list['name']; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
</div>
<?php
    $done_this = true;
}