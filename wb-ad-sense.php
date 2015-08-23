<?php
/*
Plugin Name: WB Ads Rotator with Split Test
Plugin URI: http://www.wbcomdesigns.com
Description: It allows you to hooks ads to any custom post type at multiple location and also offers your split testing.
Version: 1.0.0
Author: WBCOM DESIGNS
Author URI: https://wbcomdesigns.com
License: GPL2
http://www.gnu.org/licenses/gpl-2.0.html
*/ 

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );
	
	if ( !defined( '' ) ) {
	
		define( 'WBCOM_ADS_ROTATOR' , '1.0' );
	}
	
	if ( !defined( 'WBCOM_ADS_ROTATOR_PATH' ) ) {
	
		define( 'WBCOM_ADS_ROTATOR_PATH' , plugin_dir_path( __FILE__ ) );
	}
	
	if ( !defined( 'WBCOM_ADS_ROTATOR_URL' ) ) {
	
		define( 'WBCOM_ADS_ROTATOR_URL' , plugin_dir_url( __FILE__ ));
	}
	
	if ( !defined( 'WBCOM_ADS_ROTATOR_DB_VERSION' ) ) {
	
		define( 'WBCOM_ADS_ROTATOR_DB_VERSION' , '1' );
	}
	
	if ( !defined( 'WBCOM_ADS_ROTATOR_TEXT_DOMIAN' ) ) {
	
		define( 'WBCOM_ADS_ROTATOR_TEXT_DOMIAN' , 'wb-ads-rotator' );
	}
	
class wb_ads_rotator
{
	public $settings = array();
	public $settings_dirty = false;
	public $chosen_layout = null; // channelname, and ads arr
	public $widgetA_renderers = array();
	public $widgetB_renderers = array();
	public $widgetC_renderers = array();
	public $ip = "";

	public function __construct()
	{
		$this->init();
	}
	
	public function __destruct()
	{
		//doesn't work, must destruct manually at end of settings page
	}
	
	public function activate()
	{
		
	}
	
	public function deactivate()
	{
		//nothing to do
	}	
	public function init()
	{
		//retrieve settings
		$this->settings = get_option('wb_ads_settings');
		//print_r($this->settings );
		//set defaults
		if(!isset($this->settings['segments']) || count($this->settings['segments']) == 0)
		{
			//init default segment
			//$this->settings['segments'];
			$newsegment = array();
			$newsegment['criteria'] = "Default";
			$newsegment['segmentname'] = "All Traffic";
			$newsegment['segmentabbrev'] = "All";
			$newsegment['segment_uid'] = "wb_ad_".time();
			
			$this->settings['segments'][] = $newsegment;
			$this->settings_dirty = true;
		}
		if(!isset($this->settings['siteabbrev']) || $this->settings['siteabbrev'] == '')
		{
			$sitename = str_replace( " ", "", get_bloginfo('name') ); //get rid of whitespace
			$this->settings['siteabbrev'] = substr( $sitename, 0, 3 );
			$this->settings_dirty = true;
		}
		
		$this->ip = $_SERVER['REMOTE_ADDR'];
			
		//add post type		
		add_action( 'init', array( $this, 'ads_rotator_post_type') );
		//admin menu
		add_action( 'admin_menu', array( $this, 'make_settings_menu') );	
		
		add_action( 'admin_enqueue_scripts', array( $this, 'add_ads_rotator_style_script') );
		
		add_action( 'add_meta_boxes_wb_ads_rotator',  array( $this, 'wb_ads_add_meta_box'), 10, 1 );
		
		add_action( 'save_post', array( $this, 'wb_ads_save_meta_box_data' ) );

		
		//sidebar widgets
		add_action( 'widgets_init', array( $this, 'registerSidebars') );
		
		//add hook to check page type later
		add_action( 'wp', array( $this, 'wp_ads_run' ) );
		
		add_action( 'in_admin_header', array( $this, 'in_admin_header' ) );
		
		//we save the report fromdates and google access token to session
		if( !session_id()) session_start();
		
		//Save to db. Destructor doesn't work
		if( $this->settings_dirty )
		{
			//save vars to db
			update_option( 'wb_ads_settings', $this->settings );
		}
		
	}
	
	public function registerSidebars()
	{
		register_widget( 'WbAdsSidebarA' );
		register_widget( 'WbAdsSidebarB' );
		register_widget( 'WbAdsSidebarC' );
	}
	
	public function add_ads_rotator_style_script()
		{
			
			wp_register_script( 'wb_ads_jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js', 'all' );
	
			wp_enqueue_script( 'wb_ads_jquery-ui' );
			
			wp_register_script( 'wb_ads_jscolor', WBCOM_ADS_ROTATOR_URL . 'resources/jscolor.js', 'all' );
	
			wp_enqueue_script( 'wb_ads_jscolor' );
			
			wp_register_script( 'wb_ads_script', WBCOM_ADS_ROTATOR_URL . 'resources/wb-script.js', 'all' );
	
			wp_enqueue_script( 'wb_ads_script' );
	
			wp_register_style( 'wb_ads_bootstrap', WBCOM_ADS_ROTATOR_URL.'resources/aswrapped-bootstrap-3.0.3.css', 'all' );
	
			wp_enqueue_style( 'wb_ads_bootstrap' );
	
			wp_register_style( 'wb_ads_rotator_style', WBCOM_ADS_ROTATOR_URL.'resources/as_style.css', 'all' );
	
			wp_enqueue_style( 'wb_ads_rotator_style' );
	
			wp_register_style( 'wb_ads_rotator_smooth', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css', 'all' );
	
			wp_enqueue_style( 'wb_ads_rotator_smooth' );
		}
	// Register Custom Post Type
	public function ads_rotator_post_type() {
	
		$labels = array(
			'name'                => _x( 'Wb Ads', 'Post Type General Name', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'singular_name'       => _x( 'Wb Ad', 'Post Type Singular Name', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'menu_name'           => __( 'Wb Ad', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'name_admin_bar'      => __( 'Wb Ad', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'parent_item_colon'   => __( 'Parent Ads:', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'all_items'           => __( 'All Ads', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'add_new_item'        => __( 'Add New Ad', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'add_new'             => __( 'Add New', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'new_item'            => __( 'New Ad', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'edit_item'           => __( 'Edit Ad', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'update_item'         => __( 'Update Ad', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'view_item'           => __( 'View Ad', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'search_items'        => __( 'Search Ad', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'not_found'           => __( 'Not found', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'not_found_in_trash'  => __( 'Not found in Trash', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
		);
		$args = array(
			'label'               => __( 'wb_ads_rotator', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'description'         => __( 'Add New Ads Post', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor','author', 'trackbacks', 'revisions', 'custom-fields' ),
			'taxonomies'          => array( ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-chart-line',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,		
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'wb_ads_rotator', $args );
	
	}
	
	public function in_admin_header() {
		
		global $wp_meta_boxes;
		$cpt = 'wb_ads_rotator'; // Modify this to your needs!
		if( $cpt === get_current_screen()->id && isset( $wp_meta_boxes[$cpt] ) )
		{
			foreach( (array) $wp_meta_boxes[$cpt] as $context_key => $context_item )
			{
				foreach( $context_item as $priority_key => $priority_item )
				{
					foreach( $priority_item as $metabox_key => $metabox_item )
						if($metabox_key!='submitdiv' && $metabox_key!='authordiv' && $metabox_key!='wb_ads_rotator_meta')
						{
							unset($wp_meta_boxes[get_current_screen()->id][$context_key][$priority_key][$metabox_key]);
						}
				}
			}
		}
	}
	
	public function wb_ads_add_meta_box(){
		add_meta_box(
			'wb_ads_rotator_meta',
			__( 'Ads Settings', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ),
			array( $this, 'render_meta_box_content' ),
			'wb_ads_rotator',
			'normal',
			'high'
		);
	}
	
	public function render_meta_box_content( $post ) {
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'wb_ads_rotator_meta_box', 'wb_ads_rotator_meta_box_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$currentad = get_post_meta( $post->ID, '_wb_ads_rotator_settings', true );

		// Display the form, using the current value.
		if($currentad)
		{
			$editingad = true;
		}
		
		?>
        <div class='wbadssense single-ad'>
            <div class="form-group">
                <div class='col-sm-12'>
                    <h4><?php _e( 'Select Layout', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></h4><?php _e( '(Use ctrl+click to select multiple)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?>
                </div>
            </div>
			<?php foreach($this->settings['segments'] as $filter)
            {
				if(isset($filter['ads_layout']) && !empty($filter['ads_layout']))
				{?>
                <div class="form-group">
                    <label class='col-sm-2 control-label'><?php _e( $filter['segmentname'], WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                    <div class='col-sm-4'>
                        <select multiple="multiple" name="<?php echo $filter['segment_uid']?>[]" class='form-control'>
                        <?php foreach($filter['ads_layout'] as $key=>$layout)
                         {?>
                            <option value="<?php echo $key; ?>" <?php if($editingad && in_array($post->ID, $layout['ads'])) echo "selected='selected'"; ?> ><?php echo $layout['wb_layoutname']; ?></option>
                        <?php }?>
                        </select>
                    </div>
                </div>
            <?php }
			}
			?>
            <div class="form-group">
                <div class='col-sm-12'>
                    <hr style='border-top:1px solid black'/>
                </div>
            </div>
           <?php 
			do_action('before_ads_setting_start', $editingad, $currentad );?>
            <div class="form-group">
                <label class='col-sm-2 control-label'><?php _e( 'Ads type', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <label class='radio-inline'><input type='radio' name='wb_ads_type' id='wb_ads_type1' value='html' <?php if($editingad && $currentad['wb_ads_type'] == 'html') echo "checked='checked'"; ?>><?php _e( 'Custom HTML', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                    <label class='radio-inline'><input type='radio' name='wb_ads_type' id='wb_ads_type2' value='script' <?php if($editingad && $currentad['wb_ads_type'] == 'script') echo "checked='checked'"; ?>><?php _e( 'Custom Script', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                </div>
            </div>
            <div class='form-group wb_adlocation'>
                <label class='col-sm-2 control-label' for='wb_adlocation'><?php _e( 'Ad Location', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <select name='wb_adlocation' id='wb_adlocation' class='form-control wb_adlocation'>
                        <option value='AP' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == 'AP') echo "selected='selected'"; ?>><?php _e( 'Above post', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='IL' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == 'IL') echo "selected='selected'"; ?>><?php _e( 'Inside post (top, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='IR' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == 'IR') echo "selected='selected'"; ?>><?php _e( 'Inside post (top, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='PL' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == 'PL') echo "selected='selected'"; ?>><?php _e( 'Inside post (after 1st paragraph, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='PC' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == 'PC') echo "selected='selected'"; ?>><?php _e( 'Inside post (after 1st paragraph, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='PR' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == 'PR') echo "selected='selected'"; ?>><?php _e( 'Inside post (after 1st paragraph, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='1L' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == '1L') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/4 down, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='1C' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == '1C') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/4 down, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='1R' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == '1R') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/4 down, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='2L' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == '2L') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/2 down, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='2C' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == '2C') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/2 down, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='2R' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == '2R') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/2 down, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='3L' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == '3L') echo "selected='selected'"; ?>><?php _e( 'Inside post (3/4 down, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='3C' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == '3C') echo "selected='selected'"; ?>><?php _e( 'Inside post (3/4 down, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='3R' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == '3R') echo "selected='selected'"; ?>><?php _e( 'Inside post (3/4 down, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='BP' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == 'BP') echo "selected='selected'"; ?>><?php _e( 'Below post', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='SA' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == 'SA') echo "selected='selected'"; ?>><?php _e( 'Sidebar position A', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='SB' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == 'SB') echo "selected='selected'"; ?>><?php _e( 'Sidebar position B', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='SC' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation'] == 'SC') echo "selected='selected'"; ?>><?php _e( 'Sidebar position C', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                    </select>
                    <div id='sidebarwarning' style='display:none'><?php _e( "placing on a sidebar, be sure you've ", WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?><?php _e( 'Note: If ', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?><a href='#' target='_blank'><?php _e( 'set up the WbAds Sidebar Widget', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></a></div>
                </div>
            </div>
            <div class='form-group wb_adpadding'>
                <label class='col-sm-2 control-label' for='wb_adpadding'><?php _e( 'Padding', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_adpadding' id='wb_adpadding' class='form-control' <?php if($editingad && isset($currentad)) echo "value='$currentad[wb_adpadding]'"; ?>>
                    <?php _e( "Ex: '5px', or '10px 2px 5px 2px'", WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?>
                </div>
            </div>
            <div class="form-group adsense">
                <label class='col-sm-2 control-label'><?php _e( 'Ad Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <label class='radio-inline'><input type='radio' name='wb_color' class='wb_colordefault' id='wb_colordefault' value='default' <?php if( !$editingad || ( $editingad && !isset( $currentad ) ) || ( $editingad && !isset( $currentad['wb_color'] ) ) || ( $editingad && $currentad['wb_color'] == 'default' ) ) echo "checked='checked'"; ?>><?php _e( 'Default', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                    <label class='radio-inline'><input type='radio' name='wb_color' id='wb_colorcustom' class='wb_colorcustom' value='custom' <?php if( $editingad && $currentad['wb_color'] == 'custom' ) echo "checked='checked'"; ?>><?php _e( 'Custom', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                </div>
            </div>		
		
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colorborder'><?php _e( 'Border Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_colorborder' id='wb_colorborder' class='form-control color' maxlength=6 <?php if( $editingad && isset( $currentad ) && isset( $currentad['wb_colorborder'] ) ) echo "value='$currentad[wb_colorborder]'"; else echo "value='FFFFFF'"; ?>>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-3 control-label' for='wb_colorbg'><?php _e( 'Background Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_colorbg' id='wb_colorbg' class='form-control color' maxlength=6 <?php if($editingad && isset( $currentad ) && isset( $currentad['wb_colorbg'] ) ) echo "value='$currentad[wb_colorbg]'"; else echo "value='FFFFFF'"; ?>>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colorlink'><?php _e( 'Link Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_colorlink' id='wb_colorlink' class='form-control color' maxlength=6 <?php if( $editingad && isset( $currentad ) && isset( $currentad['wb_colorlink'] ) ) echo "value='$currentad[wb_colorlink]'"; else echo "value='1E0FBE'"; ?>>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colortext'><?php _e( 'Text Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_colortext' id='wb_colortext' class='form-control color' maxlength=6 <?php if( $editingad && isset( $currentad ) && isset( $currentad['wb_colortext'] ) ) echo "value='$currentad[wb_colortext]'"; else echo "value='373737'"; ?>>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colorurl'><?php _e( 'URL Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_colorurl' id='as_colorurl' class='form-control color' maxlength=6 <?php if( $editingad && isset( $currentad ) && isset( $currentad['wb_colorurl'] ) ) echo "value='$currentad[wb_colorurl]'"; else echo "value='006621'"; ?>>
                </div>
            </div>
        </div>
		<div style="clear:both;"></div>
        <?php
	}
	public function wb_ads_save_meta_box_data( $post_id ) {

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */
	
		// Check if our nonce is set.
		if ( ! isset( $_POST['wb_ads_rotator_meta_box_nonce'] ) ) {
			return;
		}
	
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['wb_ads_rotator_meta_box_nonce'], 'wb_ads_rotator_meta_box' ) ) {
			return;
		}
	
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
	
		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
	
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
	
		} else {
	
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}
	
		/* OK, it's safe for us to save the data now. */
		//print_r($_POST);
		foreach($this->settings['segments'] as $fil_key=>$filter)
            {
				$selected_ly=sanitize_text_field($_POST[$filter['segment_uid']]);
				if(isset($filter['ads_layout']) && !empty($filter['ads_layout']) && isset($selected_ly) && !empty($selected_ly))
				{					
					foreach($filter['ads_layout'] as $key=>$layout)
					 {
						 if(in_array($key, $selected_ly))
						 {
							 if(!in_array($post_id,$layout['ads']))
							 $this->settings['segments'][$fil_key]['ads_layout'][$key]['ads'][]=$post_id;
						 }
					 }                         
				}
			}
		update_option( 'wb_ads_settings', $this->settings );
	
		// Sanitize user input.
		$wb_ads['wb_ads_on_page'] = sanitize_text_field( $_POST['wb_ads_on_page'] );
		$wb_ads['wb_ads_segments'] = serialize($_POST['wb_ads_segments'] );
		$wb_ads['wb_ads_type'] = sanitize_text_field( $_POST['wb_ads_type']);
		$wb_ads['wb_adlocation'] = sanitize_text_field( $_POST['wb_adlocation']);
		$wb_ads['wb_adpadding'] = sanitize_text_field( $_POST['wb_adpadding']);
		$wb_ads['wb_color'] = sanitize_text_field( $_POST['wb_color']);
		if($wb_ads['wb_color'] == "custom")
		{
			$wb_ads['wb_colorborder'] = sanitize_text_field( $_POST['wb_colorborder']);
			$wb_ads['wb_colorbg'] = sanitize_text_field( $_POST['wb_colorbg']);
			$wb_ads['wb_colorlink'] = sanitize_text_field( $_POST['wb_colorlink']);
			$wb_ads['wb_colortext'] = sanitize_text_field( $_POST['wb_colortext']);
			$wb_ads['wb_colorurl'] = sanitize_text_field( $_POST['wb_colorurl']);
		}
		$wb_ads = apply_filters('filter_ads_layout_meta_post', $wb_ads);
		// Update the meta field in the database.
		update_post_meta( $post_id, '_wb_ads_rotator_settings', $wb_ads );
	}
	public function make_settings_menu()
		{
			//this must be its own function
			add_menu_page( 'Wb Ads Rotators', 'Wb Ads Rotators', 'administrator', 'wb-ads-rotator-main', array( $this, 'make_main_page' ), 'dashicons-chart-line', 61 );
			add_submenu_page( 'wb-ads-rotator-main', 'Filters', 'Filters', 'administrator', 'wb-ads-segments', array( $this, 'make_segments_page' ) );
			
			//replace first submenu 'Wb Ads Rotators' with 'Split Tests'
		global $submenu;
		if ( isset( $submenu['wb-ads-rotator-main'] ) )
			$submenu['wb-ads-rotator-main'][0][0] = __( 'Split Tests', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );
	}
	
	public function make_segments_page()
	{
		include( WBCOM_ADS_ROTATOR_PATH . 'segments.php' );
	}
	
	public function make_main_page()
	{
		include( apply_filters('filter_layout_template', WBCOM_ADS_ROTATOR_PATH . 'split-test.php') );
	}
	
	public function cleanInput( $string )
	{
		if( !isset( $string ) ) return "";

		$string = trim( $string );
		
		if(true) { //wordpress automatically escapes quotes, regardless of if get_magic_quotes_gpc() is on
			return stripslashes( $string );
		} else {
			return $string;
		}
	}
	
	public function handle_action( $action )
	{
		$toreturn = "";
		
		if($action == 'addsegment')
		{
			$newsegment = array();
			$criteria=sanitize_text_field($_POST['as_criteria']);
			$newsegment['criteria'] = $criteria;
			if( $criteria == 'page' || $criteria == 'post') 
			{
				$newsegment['criteriaparam_in'] = sanitize_text_field($_POST['as_include']);
				$newsegment['criteriaparam_ex'] = sanitize_text_field($_POST['as_exclude']);
			}
			$newsegment['segmentname'] = $this->cleanInput(sanitize_text_field($_POST['as_segmentname']));
			$newsegment['segmentabbrev'] = $this->cleanInput(sanitize_text_field($_POST['as_segmentabbrev']));
			$newsegment['segment_uid'] = 'wb_ad_'.time();

			//add to beginning of list
			array_unshift( $this->settings['segments'], $newsegment );
			
			$this->settings_dirty = true;
			$toreturn = "Fliter Created.";
		}
		elseif( $action == 'reordersegments' )
		{
			$orderedsegmentsarr = array();
			$wouldoverwritearr = array();
			foreach( $_POST['priority'] as $key=>$val )
			{
				$i = $val-1;
				//make sure we don't overwrite
				if( array_key_exists( $i, $orderedsegmentsarr ) ) $wouldoverwritearr[] = $this->settings['segments'][$key];
				else $orderedsegmentsarr[$i] = $this->settings['segments'][$key];
			}
			foreach( $wouldoverwritearr as $savedseg ) $orderedsegmentsarr[] = $savedseg;
			ksort( $orderedsegmentsarr ); //even though indexes are in order, may still need to be sorted
			$this->settings['segments'] = $orderedsegmentsarr;
			$this->settings_dirty = true;
			$toreturn = "Filter Order Saved.";
		}
		elseif( $action == 'deletesegment' )
		{
			$key = $_GET['as_segmentindex'];
			unset( $this->settings['segments'][$key] );
			if(count($this->settings['segments'])==0):
			$newsegment = array();
			$newsegment['criteria'] = "Default";
			$newsegment['segmentname'] = "All Traffic";
			$newsegment['segmentabbrev'] = "All";
			$newsegment['segment_uid'] = "wb_ad_".time();
			$this->settings['segments'][0] = $newsegment;
			endif;
			ksort( $this->settings['segments'] ); //even though indexes are in order, may still need to be sorted
			$this->settings_dirty = true;
			$toreturn = "Filter Deleted.";
		}

		elseif( $action == 'addlayout' )
		{
			//just add it to the settings var
			$editing = ( isset( $_POST['as_editinglayouti'] ) && sanitize_text_field($_POST['as_editinglayouti']) != '') ? true : false;
			$newlayout['wb_layoutname'] = $this->cleanInput( sanitize_text_field($_POST['wb_layoutname']) );
			$newlayout['whenstarted'] = time();
			$newlayout['ads'] = array();
			for( $i=1; $i<=$_POST['as_numads']; $i++ )
			{
				if( $_POST['new_ad'][$i] == "yes"){
					$newad = array();
					if( $_POST['wb_ads_type'][$i] == "html" || $_POST['wb_ads_type'][$i] == "script" )
					{
						$ads_post = array(
						  'post_title'    => $this->cleanInput( sanitize_text_field($_POST['wb_ad_title'][$i]) ),
						  'post_content'  => $this->cleanInput( sanitize_text_field($_POST['wb_customcode'][$i]) ),
						  'post_type'	  => 'wb_ads_rotator',
						  'post_status'   => 'publish',
						  'post_author'   => get_current_user_id()
						);
						
						// Insert the post into the database
						$post_id=wp_insert_post( $ads_post );
						if( $post_id>0 )
						{
							$wb_ads['wb_ads_type'] = sanitize_text_field( $_POST['wb_ads_type'][$i] );
							$wb_ads['wb_adlocation'] = sanitize_text_field( $_POST['wb_adlocation'][$i] );
							$wb_ads['wb_adpadding'] = sanitize_text_field( $_POST['wb_adpadding'][$i] );
							$wb_ads['wb_color'] = sanitize_text_field( $_POST['wb_color'][$i] );
							if( $wb_ads['wb_color'] == "custom" )
							{
								$wb_ads['wb_colorborder'] = sanitize_text_field( $_POST['wb_colorborder'][$i] );
								$wb_ads['wb_colorbg'] = sanitize_text_field( $_POST['wb_colorbg'][$i] );
								$wb_ads['wb_colorlink'] = sanitize_text_field( $_POST['wb_colorlink'][$i] );
								$wb_ads['wb_colortext'] = sanitize_text_field( $_POST['wb_colortext'][$i] );
								$wb_ads['wb_colorurl'] = sanitize_text_field( $_POST['wb_colorurl'][$i] );
							}
						
							$wb_ads = apply_filters( 'filter_ads_layout_meta_option', $wb_ads );
							// Update the meta field in the database.
							update_post_meta( $post_id, '_wb_ads_rotator_settings', $wb_ads );
						}
					}
					$newlayout['ads'][] = $post_id;
				}
				else
				{
					$newlayout['ads'][] = sanitize_text_field($_POST['as_layout_ads'][$i]);
				}
			}

			$segmenti = sanitize_text_field($_POST['as_segmenti']);
			//add new or edit?
			if( $editing )
			{
				$editingrecipei = sanitize_text_field($_POST['as_editinglayouti']);
				$newlayout['active'] = $this->settings['segments'][$segmenti]['ads_layout'][$editingrecipei]['active'];
				$this->settings['segments'][$segmenti]['ads_layout'][$editingrecipei] = $newlayout;
			}
			else
			{
				$newlayout['active'] = true;
				$this->settings['segments'][$segmenti]['ads_layout'][] = $newlayout;
			}
			
			$this->settings_dirty = true;
			$toreturn = "Ad Layout Created.";
		}
		
		elseif( $action == 'deletelayout' )
		{
			$segmenti = $_GET['as_segmenti'];
			$recipei = $_GET['wb_layouti'];
		
			//remove recipe from array		
			unset($this->settings['segments'][$segmenti]['ads_layout'][$recipei]);

			$this->settings_dirty = true;
			$toreturn = "Ad Layout Deleted.";
		}
	
		elseif( $action == 'pauselayout' )
		{			
			$segmenti = $_GET['as_segmenti'];
			$recipei = $_GET['wb_layouti'];
		
			//just update active flag
			$this->settings['segments'][$segmenti]['ads_layout'][$recipei]['active'] = 0;

			$this->settings_dirty = true;
			$toreturn = "Ad Layout Paused.";
		}
		elseif( $action == 'resumelayout' )
		{
			$segmenti = $_GET['as_segmenti'];
			$recipei = $_GET['wb_layouti'];
		
			//just update active flag
			$this->settings['segments'][$segmenti]['ads_layout'][$recipei]['active'] = 1;

			$this->settings_dirty = true;
			$toreturn = "Ad Layout Resumed.";
		}
		elseif( $action == 'setreportdate' )
		{
			//just set session var
			$i = $_GET['as_segmenti'];
			$_SESSION['as_fromdate'][$i] = $_GET['as_fromdate'];
		}
		
		//Save to db. Destructor doesn't work
		if( $this->settings_dirty )
		{
			//save vars to db
			update_option( 'wb_ads_settings', $this->settings );
		}

		return $toreturn;
	}
	
	public function wp_ads_run()
	{
		//figure out what segment we're in
		global $post;
		$segmenti = 0;
		foreach( $this->settings['segments'] as $i=>$segment )
		{
			$segmenti = $i;
			if( $segment['criteria'] == "page" )
			{
				if( isset( $segment['criteriaparam_in'] ) && $segment['criteriaparam_in'] != "" && is_page( explode( ',', $segment['criteriaparam_in'] ) ) )
				{
					break;
				}
				elseif( ( isset( $segment['criteriaparam_ex'] ) || $segment['criteriaparam_ex'] != "" ) && is_page( explode( ',', $segment['criteriaparam_ex'] ) ) )
				{
					//Do nothing
				}
				elseif( is_page() && ( !isset( $segment['criteriaparam_in'] ) || $segment['criteriaparam_in'] == "" ) && ( !isset( $segment['criteriaparam_ex'] ) || $segment['criteriaparam_ex'] == "" ) )
				{
					break;
				}
			}
			elseif( $segment['criteria'] == $post->post_type )
			{
				if( isset( $segment['criteriaparam_in'] ) && $segment['criteriaparam_in'] != "" && is_single( explode( ',',$segment['criteriaparam_in'] ) ) )
				{
					break;
				}
				elseif( ( isset( $segment['criteriaparam_ex'] ) || $segment['criteriaparam_ex']!="" ) && is_single( explode( ',', $segment['criteriaparam_ex'] ) ) )
				{
					//Do nothing
				}
				elseif( is_single() && ( !isset($segment['criteriaparam_in'] ) || $segment['criteriaparam_in'] == "" ) && (!isset( $segment['criteriaparam_ex'] ) || $segment['criteriaparam_ex'] == "" ) )
				{
					break;
				}
			}
			elseif( $segment['criteria'] == "homepage" )
			{
				if( is_front_page() )
				{
					break;
				}
			}
			elseif( $segment['criteria'] == "mobile" )
			{
				//used to have own mobile detection, but was quickly outdated. Use WP's built in function
				//this catches tablets, but that may be okay
				if( wp_is_mobile() )
				{
					break;
				}
			}
			elseif( $segment['criteria'] == "Default" )
			{
				//all traffic matches this
				break;
			}
			$segmenti = "";
		}
		
		//pick ad at random
		if( isset( $this->settings['segments'][$segmenti]['ads_layout']) && count( $this->settings['segments'][$segmenti]['ads_layout'] ) )
		{
			//only from those that are active
			$activekeys = array();
			foreach( $this->settings['segments'][$segmenti]['ads_layout'] as $key=>$layout )
			{
				if( $layout['active'] )
				{
					$activekeys[] = $key;
				}
			}
			$chosenindex = array_rand( $activekeys );
			$chosenkey = $activekeys[$chosenindex];
			$chosen_layout['ads'] = $this->settings['segments'][$segmenti]['ads_layout'][$chosenkey]['ads'];
			if( isset( $this->settings['segments'][$segmenti]['ads_layout'][$chosenkey]['view_count'] ) )
				$this->settings['segments'][$segmenti]['ads_layout'][$chosenkey]['view_count']+=1;
			else
				$this->settings['segments'][$segmenti]['ads_layout'][$chosenkey]['view_count']=1;
				
			update_option( 'wb_ads_settings', $this->settings );
		}
	
		if( isset( $chosen_layout ) ) //may  not be set if no ads on this segment
		{
			//hook chosen recipe's ads for later
			foreach( $chosen_layout['ads'] as $ad_id )
			{
				$ad['customcode'] = get_post_field( 'post_content', $ad_id );
				$ad_meta = get_post_meta( $ad_id, '_wb_ads_rotator_settings', true);
				$ad['custom'] = $ad_meta['wb_ads_type'];
				$ad['adlocation'] = $ad_meta['wb_adlocation'];
				$ad['adpadding'] = $ad_meta['wb_adpadding'];
				$ad['color'] = $ad_meta['wb_color'];
				if( isset( $ad['color'] ) && $ad['color'] == 'custom' )
				{
					$ad['color_border'] = $ad_meta['wb_colorborder'];
					$ad['color_bg'] = $ad_meta['wb_colorbg'];
					$ad['color_link'] = $ad_meta['wb_colorlink'];
					$ad['color_text'] = $ad_meta['wb_colortext'];
					$ad['color_url'] = $ad_meta['wb_colorurl'];
				}
				$this->hook_ad( $ad );
			}
		}
		
	}	
	
	public function hook_ad( $ad )
	{
		//based on ad location, add hook to show ad
		
		//use class that passes along $ad var
		$renderer = new WbRenderer();
		$renderer->ad = $ad;

		if( $ad['adlocation'] == 'AP' ||
			$ad['adlocation'] == 'IL' ||
			$ad['adlocation'] == 'IR' ||
			$ad['adlocation'] == 'PL' ||
			$ad['adlocation'] == 'PC' ||
			$ad['adlocation'] == 'PR' ||
			$ad['adlocation'] == '1L' ||
			$ad['adlocation'] == '1C' ||
			$ad['adlocation'] == '1R' ||
			$ad['adlocation'] == '2L' ||
			$ad['adlocation'] == '2C' ||
			$ad['adlocation'] == '2R' ||
			$ad['adlocation'] == '3L' ||
			$ad['adlocation'] == '3C' ||
			$ad['adlocation'] == '3R' ||
			$ad['adlocation'] == 'BP' )
		{
			//content related
			add_filter('the_content',  array( $renderer, 'inject_ad_in_content' ));
		}		
		//sidebar widgets
		elseif( $ad['adlocation'] == 'SA' )
		{
			$this->widgetA_renderers[] = $renderer;
		}
		elseif( $ad['adlocation'] == 'SB' ) 
		{
			$this->widgetB_renderers[] = $renderer;
		}
		elseif( $ad['adlocation'] == 'SC' )
		{
			$this->widgetC_renderers[] = $renderer;
		}
	}
	
	public function get_ir_fromdate( $segmenti )
	{
		if( isset( $_SESSION['as_fromdate'][$segmenti] ) && $_SESSION['as_fromdate'][$segmenti]!="" )
		{
			return $_SESSION['as_fromdate'][$segmenti];
		}
		else
		{
			//go through each recipe, pick first date
			$earliest = time()+1; //future
			if( isset( $this->settings['segments'][$segmenti]['ads_layout' ]) && count( $this->settings['segments'][$segmenti]['ads_layout'] ) )
			{
				foreach( $this->settings['segments'][$segmenti]['ads_layout'] as $recipe )
				{
					if( $recipe['whenstarted']<$earliest )
					{
						$earliest = $recipe['whenstarted'];
					}
				}
			}
			return date( "m/d/Y",$earliest );
		}
		return "XX/XX/XX";
	}
}
	global $wb_ad_sense;
	$wb_ad_sense = new wb_ads_rotator();
	include_once( 'render-class.php' );
	include_once( 'widget.php' );
	
//register activation hooks
register_activation_hook( __FILE__ , array( $wb_ad_sense, 'activate' ) );
register_deactivation_hook( __FILE__ , array( $wb_ad_sense, 'deactivate' ) );