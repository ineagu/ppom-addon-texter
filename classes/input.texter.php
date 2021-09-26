<?php
/*
 * Followig class handling images text
* dependencies. Do not make changes in code
* Create on: 10 February, 2017
*/

/* 
**========== Direct access not allowed =========== 
*/
if( ! defined('ABSPATH' ) ){ exit; }

class NM_Texter_wooproduct extends PPOM_Inputs{
	
	/*
	 * input control settings
	 */
	var $title, $desc, $settings, $currency, $format;
	
	/*
	 * this var is pouplated with current plugin meta
	*/
	var $plugin_meta;
	
	function __construct(){
		
		$this -> plugin_meta = ppom_get_plugin_meta();
		
		$this -> title 		= __ ( 'Texter', 'ppom' );
		$this -> settings	= self::get_settings();
		$this -> icon		= __ ( '<i class="fa fa-keyboard-o" aria-hidden="true"></i>', 'ppom' );
		
	}

	/* 
	**========== Get post id =========== 
	*/
	function get_post_id(){

		$args = array(
            'post_type'      => 'nm_ppom_texter',
            'posts_per_page' => 999,
            'order'          => 'ASC',
        );

        $post_meta = get_posts($args);

        $options = array( '' => 'select' );

        foreach ($post_meta as $key => $meta_data) {
        	
	        $options[$meta_data->ID] = $meta_data->post_title;
        }
						
		return apply_filters('ppom_texter_options', $options);
	}
	
	
	private function get_settings(){
		
		return array (
			'title' => array (
					'type' => 'text',
					'title' => __ ( 'Titles', 'ppom' ),
					'desc' => __ ( 'It will as section heading wrapped in h2.', 'ppom' )
			),
			'data_name' => array (
					'type' => 'text',
					'title' => __ ( 'Data name', 'ppom' ),
					'desc' => __ ( 'REQUIRED: The identification name of this field, that you can insert into body email configuration. Note:Use only lowercase characters and underscores.', 'ppom' ) 
			),
			'description' => array (
					'type' => 'textarea',
					'title' => __ ( 'Description', 'ppom' ),
					'desc' => __ ( 'Type description, it will be display under section heading.', 'ppom' )
			),
			'post_id' => array (
				'type'    => 'select',
				'title'   => __ ( 'Texter meta', 'ppom' ),
				'desc'    => __ ( 'Select the name of texter that you want to show in frontent.', 'ppom'),
				'options' => $this->get_post_id(),
				'default' => null,
			),
			'button_title' => array (
				'type' => 'text',
				'title'=> __ ( 'Model button label', 'ppom' ),
				'desc' => __ ( 'Enter popup button title.', 'ppom' ),
				'default' => 'Open',
			),
			'btn_color' => array (
				'type'  => 'text',
				'title' => __ ( 'Button title color', 'ppom' ),
				'desc'  => __ ( 'Define color e.g: #effeff.', 'ppom' )
			),
			'btn_bg_color' => array (
				'type'  => 'text',
				'title' => __ ( 'Button background color', 'ppom' ),
				'desc'  => __ ( 'Define color e.g: #effeff.', 'ppom' )
			),
			'width' => array (
				'type'    => 'select',
				'title'   => __ ( 'Width', 'ppom' ),
				'desc'    => __ ( 'Select width column.', 'ppom'),
				'options' => ppom_get_input_cols(),
				'default' => 12,
			),
			'visibility' => array (
					'type' => 'select',
					'title' => __ ( 'Visibility', 'ppom' ),
					'desc' => __ ( 'Set field visibility based on user.', 'ppom'),
					'options'	=> ppom_field_visibility_options(),
					'default'	=> 'everyone',
			),
			'visibility_role' => array (
					'type' => 'text',
					'title' => __ ( 'User Roles', 'ppom' ),
					'desc' => __ ( 'Role separated by comma.', 'ppom'),
					'hidden' => true,
			),
			'desc_tooltip' => array (
					'type' => 'checkbox',
					'title' => __ ( 'Show tooltip (PRO)', 'ppom' ),
					'desc' => __ ( 'Show Description in Tooltip with Help Icon.', 'ppom' )
			),
			'alignment' => array (
				'type'  => 'checkbox',
				'title' => __ ( 'Text alignment', 'ppom' ),
				'desc'  => __ ( 'Allow user to change text alignment.', 'ppom' )
			),
			'font_size' => array (
				'type'  => 'checkbox',
				'title' => __ ( 'Font size', 'ppom' ),
				'desc'  => __ ( 'Allow user to change font size.', 'ppom' )
			),
			'font_family' => array (
				'type'  => 'checkbox',
				'title' => __ ( 'Font family', 'ppom' ),
				'desc'  => __ ( 'Allow user to change font family.', 'ppom' )
			),
			'font_color' => array (
				'type'  => 'checkbox',
				'title' => __ ( 'Font color', 'ppom' ),
				'desc'  => __ ( 'Allow user to change font color.', 'ppom' )
			),
			'logic' => array (
					'type' => 'checkbox',
					'title' => __ ( 'Enable Conditions', "ppom" ),
					'desc' => __ ( 'Tick it to turn conditional logic to work below.', "ppom" )
			),
			'conditions' => array (
					'type' => 'html-conditions',
					'title' => __ ( 'Conditions', "ppom" ),
					'desc' => __ ( 'Tick it to turn conditional logic to work below', "ppom" )
			),
									
		);
	}
}