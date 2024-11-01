<?php
/**
 * Used for popup to magic import map and edit magic tags on map to ai page
 *
 * @package Wp2Leads/Partials/MapToAPI
 * @version 1.0.2.5
 * @since 1.0.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
		$i = 0;
		if (!isset($form_type)) $form_type = $_POST['type']; 
		if (!isset($form_id)) $form_id = $_POST['form_id'];
		
		// sort fields that new will first 
		foreach($form_fields as $field) { $i++;
			if ($field['type'] == 'radio' || $field['type'] == 'checkbox' || $field['type'] == 'select') { 
			$field_label = apply_filters('wp2leads_cf_label_filter', $field['label'], $form_type, $form_id, $field['name']);
			$new_field_message = '';
			if (!$field_label) $field_label = $field['type'];
			
			$default_check = 'label';
			$exist = false;
			$filtered_name = str_replace(array(',', ';', ':'), array('', '', ''), $field['name']);
			$old_field_label = '';
			
			if (isset($mapping['replace_table']['v.vxcf_leads_detail-' . $field['name']])) {
				// this means that "label" checked
				if ($mapping['replace_table']['v.vxcf_leads_detail-' . $field['name']]) {
					// if not empty string - old value
					$exist = true;
					
					$old_field_label = substr ($mapping['replace_table']['v.vxcf_leads_detail-' . $field['name']], 2, -2);
					$old_field_label = str_replace(array(',', ';', ':'), array('', '', ''), $old_field_label);
					
					if ( $field_label !== $field['type'] && $old_field_label !== $field_label && $old_field_label !== $filtered_name ) {
						$new_field_message = '<div class="twrap-info">' . __('This value will be changed', 'wp2leads') . '. <a href="#" data-value="' . $old_field_label . '">' . __('Use old', 'wp2leads') . '? (' . $old_field_label . ')</a></div>';
					}
				}
			}
			
			if ( ( $field_label == $filtered_name ) || ( $old_field_label == $filtered_name ) )  $default_check = 'name';
			
			if ( !$new_field_message ) {
				if ( $popup == 'replacements' ) {
					// this is edit replacements table
					if ( $default_check == 'label' ) {
						$new_field_message = '<div class="twrap-info hidden">' . __('This value will be changed', 'wp2leads') . '. <a href="#" data-value="' . $field_label . '">' . __('Use old', 'wp2leads') . '? (' . $field_label . ')</a></div>';
					} else {
						$new_field_message = '<div class="twrap-info hidden">' . __('This value will be changed', 'wp2leads') . '. <a href="#" data-value="' . $filtered_name . '">' . __('Use old', 'wp2leads') . '? (' . $filtered_name . ')</a></div>';
					}
				} else {
					// there no old values as it is a new map
				}
			} ?>
			
			<div class="tag-wrap-row">	
				<div class="twr-checkbox">
					<input type="checkbox" value="<?php echo $field['name']; ?>" <?php checked($exist); ?>>
				</div>
				<div class="labels">
					<label class="label-name before-textarea"><input type="radio" name="label-name-<?php echo $i; ?>" value="label" <?php checked($default_check, 'label'); ?> data-text="<?php echo $field_label; ?>"></label>
					<label class="label-name with-textarea">
						<textarea class="radio-label-text"><?php echo $field_label; ?></textarea>
					</label>
					<label class="label-name"><input type="radio" name="label-name-<?php echo $i; ?>" value="name" data-text="<?php echo $filtered_name; ?>" <?php checked($default_check, 'name'); ?>><?php echo $filtered_name; ?></label>
				</div>
				<div class="twrap">
				<?php echo $exist ? $new_field_message : ''; ?>
				<?php foreach ($field['values'] as $val) { ?>
					<span class="tag-name" data-name="<?php echo str_replace(array(',', ';', ':'), array('', '', ''), $val['value']); ?>"><?php echo $api['tags_prefix'] . '; ' . $field_label . ': ' . $val['value']; ?></span>
				<?php } ?>
				</div>
			</div>
			<?php }
		}
		
		echo '<label><input type="checkbox" value="form_id">' . __('Form ID', 'wp2leads' ) . '</label>';
		echo '<label><input type="checkbox" value="form_url">' . __('Form URL', 'wp2leads' ) . '</label>';