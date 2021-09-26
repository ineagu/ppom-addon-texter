<?php 
/**
 * PPM Textoxes Settings Panel Template
**/

/* 
**========== Direct access not allowed =========== 
*/
if( ! defined('ABSPATH' ) ){ exit; }

   	global $post;

   	$textbox_meta = get_post_meta($post->ID, 'ppom_design_meta', true );

   	// if texbox meta is not saved getting defualt meta
	if( empty($textbox_meta) ) {
	    $textbox_meta = array();
	    $textbox_meta[] = array('width'=>137,'height'=>60,'x_pos'=>1,'y_pos'=>1);
	}
?>
	<div class="ppom-setting-panel-wrapper">
		<?php  
		foreach ($textbox_meta as $id => $meta) {
			
			$font_family = isset( $meta['font_family'] ) ?  $meta['font_family']: '';
			$font_size   = isset( $meta['font_size'] ) ?  $meta['font_size']: '';
			$max_char    = isset( $meta['max_char'] ) ?  $meta['max_char']: '';
			$min_char    = isset( $meta['min_char'] ) ?  $meta['min_char']: '';
			$color       = isset( $meta['font_color'] ) ?  $meta['font_color']: '';
			$bg_color    = isset( $meta['font_bg_color'] ) ?  $meta['font_bg_color']: '';
			$title       = isset( $meta['textbox_title'] ) ?  $meta['textbox_title']: '';

		?>
			<div class="ppom-setting-panel-clone" data-setting-panel='<?php echo esc_attr($id); ?>' id="setting-panel-<?php echo esc_attr($id); ?>" >

				<label><?php _e('Textbox Title', 'ppom-texter'); ?></label>
				<span class="ppom-texter-desc" title="<?php _e('Enter the textbox title.', 'ppom-texter'); ?>">
					<i class="dashicons dashicons-editor-help"></i>
				</span>
				<input 
					type="text" 
					name="ppom-texbox-title" 
					class="form-control"  
					id="ppom-texbox-title" 
					value="<?php echo esc_attr($title); ?>"
				>

				<label><?php _e('Min Character', 'ppom-texter'); ?></label>
				<span class="ppom-texter-desc" title="<?php _e('Enter the number for minimum input character.', 'ppom-texter'); ?>">
					<i class="dashicons dashicons-editor-help"></i>
				</span>
				<input 
					type="number" 
					name="ppom-textbox-min-char" 
					id="ppom-textbox-min-char" 
					class="form-control" 
					value="<?php echo esc_attr($min_char); ?>" 
				>

				<label><?php _e('Max Character', 'ppom-texter'); ?></label>
				<span class="ppom-texter-desc" title="<?php _e('Enter the number for maximum input character.', 'ppom-texter'); ?>">
					<i class="dashicons dashicons-editor-help"></i>
				</span>
				<input 
					type="number" 
					name="ppom-textbox-max-char" 
					id="ppom-textbox-max-char" 
					class="form-control" 
					value="<?php echo esc_attr($max_char); ?>" 
				>

				<label><?php _e('Font Size', 'ppom-texter'); ?></label>
				<span class="ppom-texter-desc" title="<?php _e('Enter font size, e.g, 12px, 12em, 12pt, 12%. Defualt is 16px.', 'ppom-texter'); ?>">
					<i class="dashicons dashicons-editor-help"></i>
				</span>
				<input 
					type="text" 
					name="ppom-textbox-font-size" 
					id="ppom-textbox-font-size" 
					class="form-control" 
					value="<?php echo esc_attr($font_size); ?>"
				>

				<label><?php _e('Font Family', 'ppom-texter'); ?></label>
				<span class="ppom-texter-desc" title='<?php _e('Enter font family e.g, "Times New Roman", Georgia, serif. Defualt is "Times New Roman".', 'ppom-texter'); ?>'>
					<i class="dashicons dashicons-editor-help"></i>
				</span>
				<input 
					type="text" 
					name="ppom-textbox-font-family" 
					id="ppom-textbox-font-family" 
					class="form-control" 
					value="<?php echo esc_attr($font_family); ?>"
				>

				<!-- <br> -->
				<label class="ppom-text-color-label"><?php _e('Text Color', 'ppom-texter'); ?></label>
				<span class="ppom-texter-desc" title="<?php _e('Select textbox text color. Defualt is black.', 'ppom-texter'); ?>">
					<i class="dashicons dashicons-editor-help"></i>
				</span>
				<input 
					name="ppom-textbox-color" 
					id="ppom-textbox-color" 
					class="wp-color" 
					value="<?php echo esc_attr($color); ?>"
				>

				<label class="ppom-text-bgcolor-label"><?php _e('Background Color', 'ppom-texter'); ?></label>
				<span class="ppom-texter-desc" title="<?php _e('Select textbox background color. Defualt is transparent.', 'ppom-texter'); ?>">
					<i class="dashicons dashicons-editor-help"></i>
				</span>
				<input 
					name="ppom-textbox-bg-color" 
					id="ppom-textbox-bg-color" 
					class="wp-color" 
					value="<?php echo esc_attr($bg_color); ?>"
				>

				<div class="ppom-texter-save-btn">
					<span class="texter-setting-alert"></span>
					<button class="btn btn-primary ppom-setting-btn" data-setting-btn-id='<?php echo esc_attr($id); ?>'><?php _e('Apply', 'ppom-texter'); ?></button>
				</div>
			</div>
		<?php 
		} 
		?>
	</div>