<?php
/*
Plugin Name: WB ADS ROTATOR
Plugin URI: http://www.wbcomdesigns.com
Description: Place custom ads on your site
Version: 1.0
Author: WBCOM DESIGNS
Author URI: http://www.wbcomdesigns.com
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
	public $chosen_recipe = null; // channelname, and ads arr
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
		//print_r($this->settings);
		
		//set defaults
		if(!isset($this->settings['segments']) || count($this->settings['segments'])==0)
		{
			//init default segment
			$this->settings['segments'];
			$newsegment = array();
			$newsegment['criteria'] = "default";
			$newsegment['segmentname'] = "All Traffic";
			$newsegment['segmentabbrev'] = "All";
			
			$this->settings['segments'][] = $newsegment;
			$this->settings_dirty = true;
		}
		if(!isset($this->settings['siteabbrev']) || $this->settings['siteabbrev']=='')
		{
			$sitename = str_replace(" ","",get_bloginfo('name')); //get rid of whitespace
			$this->settings['siteabbrev'] = substr($sitename,0,3);
			$this->settings_dirty = true;
		}
		
		$this->ip = $_SERVER['REMOTE_ADDR'];
		
		//add post type
		add_action( 'init', array( $this, 'ads_rotator_post_type') );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'add_ads_rotator_style_script') );
		
		add_action( 'add_meta_boxes_wb_ads_rotator',  array( $this, 'wb_ads_add_meta_box'),10,1 );
		
		add_action( 'save_post', array( $this, 'wb_ads_save_meta_box_data' ) );

		//admin menu
		add_action('admin_menu', array( $this, 'make_settings_menu') );
		
		//sidebar widgets
		add_action('widgets_init', array( $this, 'registerSidebars') );
		
		//add hook to check page type later
		//add_action('wp', array( $this, 'run'));
		
		//we save the report fromdates and google access token to session
		if( !session_id()) session_start();
		
		//Save to db. Destructor doesn't work
		if($this->settings_dirty)
		{
			//save vars to db
			update_option('wb_ads_settings', $this->settings);
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
			
			wp_register_script('wb_ads_jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js', 'all');
	
			wp_enqueue_script('wb_ads_jquery-ui');
			
			wp_register_script('wb_ads_jscolor', WBCOM_ADS_ROTATOR_URL . 'resources/jscolor.js', 'all');
	
			wp_enqueue_script('wb_ads_jscolor');
			
			wp_register_script('wb_ads_script', WBCOM_ADS_ROTATOR_URL . 'resources/wb-script.js', 'all');
	
			wp_enqueue_script('wb_ads_script');
	
			wp_register_style('wb_ads_bootstrap', WBCOM_ADS_ROTATOR_URL.'resources/aswrapped-bootstrap-3.0.3.css', 'all');
	
			wp_enqueue_style('wb_ads_bootstrap');
	
			wp_register_style('wb_ads_rotator_style', WBCOM_ADS_ROTATOR_URL.'resources/as_style.css', 'all');
	
			wp_enqueue_style('wb_ads_rotator_style');
	
			wp_register_style('wb_ads_rotator_smooth', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css', 'all');
	
			wp_enqueue_style('wb_ads_rotator_smooth');
		}
	// Register Custom Post Type
	public function ads_rotator_post_type() {
	
		$labels = array(
			'name'                => _x( 'Wb Ads Rotators', 'Post Type General Name', 'wb-ads-rotator' ),
			'singular_name'       => _x( 'Wb Ads Rotator', 'Post Type Singular Name', 'wb-ads-rotator' ),
			'menu_name'           => __( 'Wb Ads Rotator', 'wb-ads-rotator' ),
			'name_admin_bar'      => __( 'Wb Ads Rotator', 'wb-ads-rotator' ),
			'parent_item_colon'   => __( 'Parent Ads:', 'wb-ads-rotator' ),
			'all_items'           => __( 'All Ads', 'wb-ads-rotator' ),
			'add_new_item'        => __( 'Add New Ad', 'wb-ads-rotator' ),
			'add_new'             => __( 'Add New', 'wb-ads-rotator' ),
			'new_item'            => __( 'New Ad', 'wb-ads-rotator' ),
			'edit_item'           => __( 'Edit Ad', 'wb-ads-rotator' ),
			'update_item'         => __( 'Update Ad', 'wb-ads-rotator' ),
			'view_item'           => __( 'View Ad', 'wb-ads-rotator' ),
			'search_items'        => __( 'Search Ad', 'wb-ads-rotator' ),
			'not_found'           => __( 'Not found', 'wb-ads-rotator' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'wb-ads-rotator' ),
		);
		$args = array(
			'label'               => __( 'wb_ads_rotator', 'wb-ads-rotator' ),
			'description'         => __( 'Add New Ads Post', 'wb-ads-rotator' ),
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
        <div class='wbadssense'>
            <div class='form-group'>
                <label class='col-sm-2 control-label' for='wb_ads_on_page'><?php _e( '# Ads on page', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <select name='wb_ads_on_page' id='wb_ads_on_page'>
                        <?php
                        for($i=1; $i<=6; $i++)
                        {
                            echo "<option value='$i'"; if($editingad && $currentad['wb_ads_on_page']==$i) echo " selected='selected'"; echo ">$i</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class='col-sm-12'>
                    <hr style='border-top:1px solid black'/>
                </div>
            </div>
            <div class="form-group">
            	<label class='col-sm-2 control-label'><?php _e( 'Ads Segment', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                	<?php
					$segments=unserialize($currentad['wb_ads_segments']);
                    if(count($this->settings['segments']))
					{
						foreach($this->settings['segments'] as $i=>$seg)
						{?>
							  <label class='radio-inline'><input type='checkbox' name='wb_ads_segments[]' id='' value='<?php echo $seg[segment_uid];?>' <?php if($editingad && in_array($seg[segment_uid], $segments)) echo "checked='checked'"; ?>> <?php _e( $seg[segmentname], WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
						<?php
                        }
					}
					?>
                </div>
            </div>
            <div class="form-group">
                <div class='col-sm-12'>
                    <hr style='border-top:1px solid black'/>
                </div>
            </div>
            <div class="form-group">
                <label class='col-sm-2 control-label'><?php _e( 'Ads type', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <label class='radio-inline'><input type='radio' name='wb_ads_type' id='wb_ads_type1' value='html' <?php if($editingad && $currentad['wb_ads_type']=='html') echo "checked='checked'"; ?>><?php _e( 'Custom HTML', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                    <label class='radio-inline'><input type='radio' name='wb_ads_type' id='wb_ads_type2' value='script' <?php if($editingad && $currentad['wb_ads_type']=='script') echo "checked='checked'"; ?>><?php _e( 'Custom Script', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label' for='wb_adlocation'><?php _e( 'Ad Location', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <select name='wb_adlocation' id='wb_adlocation' class='form-control'>
                        <option value='AP' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='AP') echo "selected='selected'"; ?>><?php _e( 'Above post', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='IL' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='IL') echo "selected='selected'"; ?>><?php _e( 'Inside post (top, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='IR' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='IR') echo "selected='selected'"; ?>><?php _e( 'Inside post (top, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='PL' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='PL') echo "selected='selected'"; ?>><?php _e( 'Inside post (after 1st paragraph, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='PC' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='PC') echo "selected='selected'"; ?>><?php _e( 'Inside post (after 1st paragraph, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='PR' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='PR') echo "selected='selected'"; ?>><?php _e( 'Inside post (after 1st paragraph, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='1L' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='1L') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/4 down, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='1C' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='1C') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/4 down, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='1R' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='1R') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/4 down, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='2L' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='2L') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/2 down, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='2C' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='2C') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/2 down, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='2R' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='2R') echo "selected='selected'"; ?>><?php _e( 'Inside post (1/2 down, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='3L' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='3L') echo "selected='selected'"; ?>><?php _e( 'Inside post (3/4 down, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='3C' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='3C') echo "selected='selected'"; ?>><?php _e( 'Inside post (3/4 down, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='3R' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='3R') echo "selected='selected'"; ?>><?php _e( 'Inside post (3/4 down, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='BP' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='BP') echo "selected='selected'"; ?>><?php _e( 'Below post', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='SA' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='SA') echo "selected='selected'"; ?>><?php _e( 'Sidebar position A', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='SB' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='SB') echo "selected='selected'"; ?>><?php _e( 'Sidebar position B', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='SC' <?php if($editingad && isset($currentad) && $currentad['wb_adlocation']=='SC') echo "selected='selected'"; ?>><?php _e( 'Sidebar position C', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                    </select>
                    <div id='sidebarwarning' style='display:none'>Note: If placing on a sidebar, be sure you've <a href='http://www.ampedsense.com/placing-adsense-ads-wordpress-sidebar/' target='_blank'>set up the AmpedSense Sidebar Widget</a></div>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label' for='wb_adpadding'><?php _e( 'Padding', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_adpadding' id='wb_adpadding' class='form-control' <?php if($editingad && isset($currentad)) echo "value='$currentad[wb_adpadding]'"; ?>>
                    <?php _e( "Ex: '5px', or '10px 2px 5px 2px'", WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?>
                </div>
            </div>
            <div class="form-group adsense">
                <label class='col-sm-2 control-label'><?php _e( 'Ad Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <label class='radio-inline'><input type='radio' name='wb_color' id='wb_colordefault' value='default' <?php if(!$editingad || ($editingad && !isset($currentad)) || ($editingad && !isset($currentad['wb_color'])) || ($editingad && $currentad['wb_color']=='default')) echo "checked='checked'"; ?>><?php _e( 'Default', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                    <label class='radio-inline'><input type='radio' name='wb_color' id='wb_colorcustom' value='custom' <?php if($editingad && $currentad['wb_color']=='custom') echo "checked='checked'"; ?>><?php _e( 'Custom', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                </div>
            </div>		
		
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colorborder'><?php _e( 'Border Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_colorborder' id='wb_colorborder' class='form-control color' maxlength=6 <?php if($editingad && isset($currentad) && isset($currentad['wb_colorborder'])) echo "value='$currentad[wb_colorborder]'"; else echo "value='FFFFFF'"; ?>>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colorbg'><?php _e( 'Background Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_colorbg' id='wb_colorbg' class='form-control color' maxlength=6 <?php if($editingad && isset($currentad) && isset($currentad['wb_colorbg'])) echo "value='$currentad[wb_colorbg]'"; else echo "value='FFFFFF'"; ?>>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colorlink'><?php _e( 'Link Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_colorlink' id='wb_colorlink' class='form-control color' maxlength=6 <?php if($editingad && isset($currentad) && isset($currentad['wb_colorlink'])) echo "value='$currentad[wb_colorlink]'"; else echo "value='1E0FBE'"; ?>>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colortext'><?php _e( 'Text Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_colortext' id='wb_colortext' class='form-control color' maxlength=6 <?php if($editingad && isset($currentad) && isset($currentad['wb_colortext'])) echo "value='$currentad[wb_colortext]'"; else echo "value='373737'"; ?>>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colorurl'><?php _e( 'URL Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-12'>
                    <input type='text' name='wb_colorurl' id='as_colorurl' class='form-control color' maxlength=6 <?php if($editingad && isset($currentad) && isset($currentad['wb_colorurl'])) echo "value='$currentad[wb_colorurl]'"; else echo "value='006621'"; ?>>
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
		
	
		// Sanitize user input.
		$wb_ads['wb_ads_on_page'] = sanitize_text_field( $_POST['wb_ads_on_page'] );
		$wb_ads['wb_ads_segments'] = serialize($_POST['wb_ads_segments'] );
		$wb_ads['wb_ads_type'] = sanitize_text_field( $_POST['wb_ads_type']);
		$wb_ads['wb_adlocation'] = sanitize_text_field( $_POST['wb_adlocation']);
		$wb_ads['wb_adpadding'] = sanitize_text_field( $_POST['wb_adpadding']);
		$wb_ads['wb_color'] = sanitize_text_field( $_POST['wb_color']);
		if($wb_ads['wb_color']=="custom")
		{
			$wb_ads['wb_colorborder'] =sanitize_text_field( $_POST['wb_colorborder']);
			$wb_ads['wb_colorbg'] =sanitize_text_field( $_POST['wb_colorbg']);
			$wb_ads['wb_colorlink'] =sanitize_text_field( $_POST['wb_colorlink']);
			$wb_ads['wb_colortext'] =sanitize_text_field( $_POST['wb_colortext']);
			$wb_ads['wb_colorurl'] =sanitize_text_field( $_POST['wb_colorurl']);
		}
	
		// Update the meta field in the database.
		update_post_meta( $post_id, '_wb_ads_rotator_settings', $wb_ads );
	}
	public function make_settings_menu()
		{
			//this must be its own function
			//add_menu_page( 'AmpedSense', 'AmpedSense', 'administrator', 'ampedsense-main', array( $this, 'make_main_page' ) );
			add_submenu_page( 'edit.php?post_type=wb_ads_rotator', 'Segments', 'Segments', 'administrator', 'wb-ads-segments', array( $this, 'make_segments_page' ) );
			//add_submenu_page( 'edit.php?post_type=wb_ads_rotator', 'Split Tests', 'Segments', 'administrator', 'wb-ads-segments', array( $this, 'make_segments_page' ) );
			
			//replace first submenu 'AmpedSense' with 'Split Tests'
		
	}
	
	public function cleanInput( $string )
	{
		if(!isset($string)) return "";

		$string = trim($string);
		
		if(true) { //wordpress automatically escapes quotes, regardless of if get_magic_quotes_gpc() is on
			return stripslashes($string);
		} else {
			return $string;
		}
	}
	
	public function handle_action($action)
	{
		$toreturn = "";
		
		if($action=='setauth')
		{
			//convert param into usable vars
			$param = base64_decode($_GET['as_p']);
			list($at,$rt,$exp) = explode("|||",$param);
			//set refresh token
			$this->settings['googlerefreshtoken'] = $rt;
			$this->settings_dirty = true;
			
			//access token should also have been passed
			$_SESSION['as_googleaccesstoken'] = $at;
			$_SESSION['as_googleaccesstokenexpires'] = time() + $exp;
			
			$toreturn = "Successfully authenticated! Verify Publisher ID below, or manage <a href='".admin_url('admin.php?page=ampedsense-main')."'>split tests</a>";
		}
		elseif($action=='updatesettings')
		{
			//set vars
			$this->settings['adsensepublisherid'] = $this->cleanInput($_POST['as_adsensepublisherid']);
			$this->settings['siteabbrev'] = $this->cleanInput($_POST['as_siteabbrev']);
			
			$this->settings_dirty = true;
			$toreturn = "Settings Saved.";
		}
		elseif($action=='addsegment')
		{
			$newsegment = array();
			$newsegment['criteria'] = $_POST['as_criteria'];
			if($_POST['as_criteria']=='page') $newsegment['criteriaparam'] = $_POST['as_criteriaparam_page'];
			if($_POST['as_criteria']=='post') $newsegment['criteriaparam'] = $_POST['as_criteriaparam_post'];
			$newsegment['segmentname'] = $this->cleanInput($_POST['as_segmentname']);
			$newsegment['segmentabbrev'] = $this->cleanInput($_POST['as_segmentabbrev']);
			$newsegment['segment_uid'] = 'wb_ad_'.time();

			//add to beginning of list
			array_unshift($this->settings['segments'], $newsegment);
			
			$this->settings_dirty = true;
			$toreturn = "Segment Created.";
		}
		elseif($action=='reordersegments')
		{
			$orderedsegmentsarr = array();
			$wouldoverwritearr = array();
			foreach($_POST['priority'] as $key=>$val)
			{
				$i = $val-1;
				//make sure we don't overwrite
				if(array_key_exists($i,$orderedsegmentsarr)) $wouldoverwritearr[] = $this->settings['segments'][$key];
				else $orderedsegmentsarr[$i] = $this->settings['segments'][$key];
			}
			foreach($wouldoverwritearr as $savedseg) $orderedsegmentsarr[] = $savedseg;
			ksort($orderedsegmentsarr); //even though indexes are in order, may still need to be sorted
			$this->settings['segments'] = $orderedsegmentsarr;
			$this->settings_dirty = true;
			$toreturn = "Segment Order Saved.";
		}
		elseif($action=='deletesegment')
		{
			$key = $_GET['as_segmentindex'];
			unset($this->settings['segments'][$key]);
			ksort($this->settings['segments']); //even though indexes are in order, may still need to be sorted
			$this->settings_dirty = true;
			$toreturn = "Segment Deleted.";
		}

		elseif($action=='addrecipe')
		{
			
			//just add it to the settings var
			$editing = (isset($_POST['as_editingrecipei']) && $_POST['as_editingrecipei']!='') ? true : false;
			$newrecipe['recipename'] = $this->cleanInput($_POST['as_recipename']);
			$newrecipe['channelname'] = $this->cleanInput($_POST['as_channelname']);
			$newrecipe['whenstarted'] = time();
			$newrecipe['ads'] = array();
			for($i=1; $i<=$_POST['as_numads']; $i++)
			{
				$newad = array();
				if($_POST['as_custom'][$i]=="html" || $_POST['as_custom'][$i]=="script")
				{
					$newad['custom'] = $_POST['as_custom'][$i];
					$newad['customcode'] = $this->cleanInput($_POST['as_customcode'][$i]);
				}
				else
				{
					$newad['custom'] = "no";
					$newad['adsize'] = $_POST['as_adsize'][$i];
					$newad['adtype'] = $_POST['as_adtype'][$i];
				}
				$newad['adlocation'] = $_POST['as_adlocation'][$i];
				$newad['adpadding'] = $this->cleanInput($_POST['as_adpadding'][$i]);
				if($_POST['as_color'][$i]=="custom")
				{
					$newad['color'] = "custom";
					$newad['color_border'] = $this->cleanInput($_POST['as_colorborder'][$i]);
					$newad['color_bg'] = $this->cleanInput($_POST['as_colorbg'][$i]);
					$newad['color_link'] = $this->cleanInput($_POST['as_colorlink'][$i]);
					$newad['color_text'] = $this->cleanInput($_POST['as_colortext'][$i]);
					$newad['color_url'] = $this->cleanInput($_POST['as_colorurl'][$i]);
				}
				else
				{
					$newad['color'] = "default";
				}
				
				$newrecipe['ads'][] = $newad;
			}

			$segmenti = $_POST['as_segmenti'];
			//add new or edit?
			if($editing)
			{
				$editingrecipei = $_POST['as_editingrecipei'];
				$newrecipe['active'] = $this->settings['segments'][$segmenti]['recipes'][$editingrecipei]['active'];
				$this->settings['segments'][$segmenti]['recipes'][$editingrecipei] = $newrecipe;
			}
			else
			{
				$newrecipe['active'] = true;
				$this->settings['segments'][$segmenti]['recipes'][] = $newrecipe;
			}
			
			//automatically try and find id for new channel
			$this->lookup_channels();
			
			$this->settings_dirty = true;
			$toreturn = "Ad Recipe Created.";
		}
		
		elseif($action=='deleterecipe')
		{
			$segmenti = $_GET['as_segmenti'];
			$recipei = $_GET['as_recipei'];
		
			//remove recipe from array		
			unset($this->settings['segments'][$segmenti]['recipes'][$recipei]);

			$this->settings_dirty = true;
			$toreturn = "Ad Recipe Deleted.";
		}
	
		elseif($action=='pauserecipe')
		{			
			$segmenti = $_GET['as_segmenti'];
			$recipei = $_GET['as_recipei'];
		
			//just update active flag
			$this->settings['segments'][$segmenti]['recipes'][$recipei]['active'] = 0;

			$this->settings_dirty = true;
			$toreturn = "Ad Recipe Paused.";
		}
		elseif($action=='resumerecipe')
		{
			$segmenti = $_GET['as_segmenti'];
			$recipei = $_GET['as_recipei'];
		
			//just update active flag
			$this->settings['segments'][$segmenti]['recipes'][$recipei]['active'] = 1;

			$this->settings_dirty = true;
			$toreturn = "Ad Recipe Resumed.";
		}
		elseif($action=='setreportdate')
		{
			//just set session var
			$i = $_GET['as_segmenti'];
			$_SESSION['as_fromdate'][$i] = $_GET['as_fromdate'];
		}
		elseif($action=='lookupchannels')
		{
			//manually get channelids
			$this->lookup_channels();
		}

		
		//Save to db. Destructor doesn't work
		if($this->settings_dirty)
		{
			//save vars to db
			update_option('wb_ads_settings', $this->settings);
		}

		return $toreturn;
	}
	public function make_segments_page()
	{
		include( WBCOM_ADS_ROTATOR_PATH . 'segments.php' );
	}
	
	
}
include_once('widget.php');

$wb_ad_sense = new wb_ads_rotator();

//register activation hooks
register_activation_hook( __FILE__ , array( $wb_ad_sense, 'activate' ) );
register_deactivation_hook( __FILE__ , array( $wb_ad_sense, 'deactivate' ) );