<?php
global $wb_ad_sense;
$as_page = "";

// if form submitted, handle action
if( !empty( $_POST ) || count($_GET)>1 )
{
	if(!empty($_REQUEST['as_action']))
	{
		$wb_ad_sense->handle_action($_REQUEST['as_action'],$as_page);
	}
}

?>

<div class='wbadssense'>

<?php
if(isset($_GET['as_newlayout']) || isset($_GET['wb_editlayout']))
{
	$segmenti = $_GET['as_segmenti'];
	$maxads = 6;
	
	$editinglayout = false;
	if(isset($_GET['wb_editlayout']))
	{
		$editinglayout = true;
		$layouti = $_GET['wb_layouti'];
		$currentlayout = $wb_ad_sense->settings['segments'][$segmenti]['ads_layout'][$layouti];
	}
	?>
	<h2><?php if($editinglayout) echo "Editing"; else echo "Create New"; ?> Ad Layout</h2>
	
	<form method='post' class='form-horizontal' id="theform" action="<?php echo admin_url('admin.php?page=wb-ads-rotator-main') ?>">
	<input type='hidden' name='as_action' value='addlayout'>
	<input type='hidden' name='as_segmenti' value='<?php echo $segmenti; ?>'>
	<?php if($editinglayout) echo "<input type='hidden' name='as_editinglayouti' value='$layouti'>"; ?>
	<div class='form-group'>
		<label class='col-sm-2 control-label' for='as_numads'># Ads on page</label>
		<div class='col-sm-6'>
			<select name='as_numads' id='as_numads'>
				<?php
				for($i=1; $i<=$maxads; $i++)
				{
					echo "<option value='$i'"; if($editinglayout && count($currentlayout['ads'])==$i) echo " selected='selected'"; echo ">$i</option>";
				}
				?>
			</select>
		</div>
	</div>
	<?php
	for($i=1; $i<=$maxads; $i++)
	{
		$offset = $i-1;
		$existingad = isset($currentlayout['ads'][$offset]) ? $currentlayout['ads'][$offset] : null;
		printAdOptions($i,$editinglayout,$existingad);
	}
	?>
	
	<div class="form-group">
		<div class='col-sm-8'>
			<hr style='border-top:1px solid black'/>
		</div>
	</div>
	
	<div class='form-group'>
		<label class='col-sm-2 control-label' for='wb_layoutname'>Layout Name</label>
		<div class='col-sm-6'>
			<input type='text' class='form-control' name='wb_layoutname' id='wb_layoutname' maxlength=18 <?php if($editinglayout) echo "value=\"$currentlayout[wb_layoutname]\""; ?>>
		</div>
	</div>
	
	<div class='form-group'>
		<div class="col-sm-offset-2 col-sm-6">
			<button type='submit' id="createbutton" class='btn btn-primary'>Save Ad Layout</button>
		</div>
	</div>
	</form>
    
	<?php
}
else
{
	?>
	<h2>Welcome to Wb Ads Rotator!</h2>
	<p>Congratulations on installing Wb Ads Rotator! You're about to run some kick-butt split tests to help you <b>increase your AdSense revenue</b>. Sweet!</p>
	
	<?php
				
		//grab all data from google for each segment date
		$google_success = false;
		$apiresults = array(); //indexed by segmenti
		
		//foreach segment, list out recipes and how they're doing
		foreach($wb_ad_sense->settings['segments'] as $i=>$segment)
		{
			$fromdate = $wb_ad_sense->get_ir_fromdate($i);
			$statsheaderrow = "<tr><td colspan=5></td><td>Views</td><td>Clicks</td></tr>";
			$irdate = "<input type='hidden' id='datetestinput$i' /> since <span id='datetestdisplay$i' style='cursor:pointer; text-decoration:underline'>$fromdate</span>";
			$script = "<script>
					jQuery('#datetestinput$i').datepicker({ onSelect: function(d) { window.location='".admin_url('admin.php?page=wb-ads-rotator-main')."&as_action=setreportdate&as_segmenti=$i&as_fromdate='+d; } });
					jQuery('#datetestdisplay$i').click(function() {  jQuery('#datetestinput$i').datepicker( 'show' ); });
				</script>";
			
			echo "<h3>$segment[segmentname]</h3>";
			echo "<table class='table table-hover'>";
			echo "<tr><th></th><th>Recipe</th><th>Date Started</th><th>Status</th><th># Ads</th><th colspan=5 style='text-align:center'>Stats $irdate</th></tr>";
			echo $statsheaderrow;
			if(isset($segment['ads_layout']) && count($segment['ads_layout']))
			{
				foreach($segment['ads_layout'] as $j=>$layout)
				{
					//$previewurl = $wb_ad_sense->get_segment_preview_url($i)."?".$wb_ad_sense->get_recipe_preview_qs($layout);
					echo "<tr>";
					//actions
					echo "<td>";
					echo "<a href='".admin_url('admin.php?page=wb-ads-rotator-main')."&as_action=deletelayout&as_segmenti=$i&wb_layouti=$j' title='Delete' onClick=\"return confirm('Are you sure you want to delete?')\"><img src='".WBCOM_ADS_ROTATOR_URL."resources/delete.png' /></a> ";
					echo "<a href='".admin_url('admin.php?page=wb-ads-rotator-main')."&wb_editlayout=1&as_segmenti=$i&wb_layouti=$j' title='Edit' onClick=\"return confirm('Editing a test after it has started could affect the accuracy of your results. Are you sure you want to edit?')\"><img src='".WBCOM_ADS_ROTATOR_URL."resources/edit.png' /></a> ";
					if($layout['active']) echo "<a href='".admin_url('admin.php?page=wb-ads-rotator-main')."&as_action=pauselayout&as_segmenti=$i&wb_layouti=$j' title='Pause'><img src='".WBCOM_ADS_ROTATOR_URL."resources/pause.png' /></a>";
					else echo "<a href='".admin_url('admin.php?page=wb-ads-rotator-main')."&as_action=resumelayout&as_segmenti=$i&wb_layouti=$j' title='Resume'><img src='".WBCOM_ADS_ROTATOR_URL."resources/resume.png' /></a>";
					echo "</td>";
					//namef
					echo "<td><a href='".$previewurl."' target='_blank'>$layout[wb_layoutname]</a></td>";
					//date
					echo "<td>".date("m/d/Y",$layout['whenstarted'])."</td>";
					//status
					echo "<td>";
					if($layout['active']) echo "<span style='color:green'>Active</span>";
					else echo "<span style='color:orange'>Paused</span>";
					echo "</td>";
					//ad count
					echo "<td>".count($layout['ads'])."</td>";
					//show stats, or link to stats
					echo "<td>".$layout['view_count']."</td>"; //views
					echo "<td></td>"; //clicks
				}
			}
			else
			{
				echo "<tr><td colspan=10>No ad recipes yet.</td></tr>";
			}
			echo "<tr><td colspan=10>Create <a href='".admin_url('admin.php?page=wb-ads-rotator-main')."&as_newlayout=1&as_segmenti=$i'>New Layout</a></td></tr>";
			echo "</table><br/>";
			echo $script;
		}
		echo '<p>Need help? <a href="#" target="_blank">Creating your first split test</a></p>';
	
}

function printAdOptions($i,$editingad,$currentad)
{
	?>
     <div id='adoptions<?php echo $i; ?>' class="single-ad">
        <div class="form-group current-ad">
            <div class='col-sm-8'>
                <hr style='border-top:1px solid black'/>
            </div>
        </div>
       <div class="form-group current-ad">
            <label class='col-sm-2 control-label'>Select Ad <?php echo $i; ?></label>
            <div class='col-sm-6'>
                <select name='as_layout_ads[<?php echo $i; ?>]' id='as_layout_ads<?php echo $i; ?>' class='form-control'>
                <?php
                    $args = array(
                        'post_type' => 'wb_ads_rotator',
                        'post_status' => 'publish',
                        'posts_per_page' => -1 //defaults to only 5, -1 is undocumented, but saw some code samples that use it. Seems to work
                    ); 
                    $posts = get_posts($args);
                    foreach($posts as $post)
                    {
                        echo "<option value='".$post->ID."'";
						if($editingad && $post->ID==$currentad) echo " selected='selected'";
						echo ">".$post->post_title."</option>";
                    }
                ?>
                </select>
            </div>
            <div class='col-sm-2'>
            	<a href="javascript:void(0)" onClick="show_new_ad('<?php echo $i; ?>');" class="add-remove">+</a>
            </div>
      	</div>
   		<div class="new_ad_option">
            <div class="form-group">
                <div class='col-sm-8'>
                    <hr style='border-top:1px solid black'/>
                    <input type="hidden" value="" name="new_ad[<?php echo $i; ?>]" />
                </div>
            </div>
        
            <div class='form-group' >
                <label class='col-sm-2 control-label' for='wb_ad_title'><?php _e( 'Ads Title', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-6'>
                    <input type='text' name='wb_ad_title[<?php echo $i; ?>]' id='wb_ad_title' class='form-control' value="">
                </div>
                <div class='col-sm-2'>
                    <a href="javascript:void(0)" onClick="hide_new_ad('<?php echo $i; ?>');" class="add-remove">-</a>
                </div>
            </div>
         	<div class="form-group">
                <label class='col-sm-2 control-label'><?php _e( 'Ads type', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-6'>
                    <label class='radio-inline'><input type='radio' name='wb_ads_type[<?php echo $i; ?>]' id='wb_ads_type1' value='html' checked='checked' ><?php _e( 'Custom HTML', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                    <label class='radio-inline'><input type='radio' name='wb_ads_type[<?php echo $i; ?>]' id='wb_ads_type2' value='script' ><?php _e( 'Custom Script', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                </div>
            </div>
            <div class='form-group' >
                <label class='col-sm-2 control-label' for='wb_customcode<?php echo $i; ?>'>Code Snippet</label>
                <div class='col-sm-6'>
                    <textarea class="form-control" rows="3" name='wb_customcode[<?php echo $i; ?>]' id='wb_customcode<?php echo $i; ?>'></textarea>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label' for='wb_adlocation'><?php _e( 'Ad Location', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-6'>
                    <select name='wb_adlocation[<?php echo $i; ?>]' id='wb_adlocation' class='form-control wb_adlocation'>
                        <option value='AP' ><?php _e( 'Above post', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='IL' ><?php _e( 'Inside post (top, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='IR' ><?php _e( 'Inside post (top, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='PL' ><?php _e( 'Inside post (after 1st paragraph, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='PC' ><?php _e( 'Inside post (after 1st paragraph, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='PR' ><?php _e( 'Inside post (after 1st paragraph, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='1L' ><?php _e( 'Inside post (1/4 down, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='1C' ><?php _e( 'Inside post (1/4 down, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='1R' ><?php _e( 'Inside post (1/4 down, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='2L' ><?php _e( 'Inside post (1/2 down, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='2C' ><?php _e( 'Inside post (1/2 down, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='2R' ><?php _e( 'Inside post (1/2 down, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='3L' ><?php _e( 'Inside post (3/4 down, left)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='3C' ><?php _e( 'Inside post (3/4 down, center)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='3R' ><?php _e( 'Inside post (3/4 down, right)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='BP' ><?php _e( 'Below post', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='SA' ><?php _e( 'Sidebar position A', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='SB' ><?php _e( 'Sidebar position B', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                        <option value='SC' ><?php _e( 'Sidebar position C', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></option>
                    </select>
                    <div class='sidebarwarning' style='display:none'>Note: If placing on a sidebar, be sure you've <a href='http://www.ampedsense.com/placing-adsense-ads-wordpress-sidebar/' target='_blank'>set up the AmpedSense Sidebar Widget</a></div>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label' for='wb_adpadding'><?php _e( 'Padding', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-6'>
                    <input type='text' name='wb_adpadding[<?php echo $i; ?>]' id='wb_adpadding' class='form-control' >
                    <?php _e( "Ex: '5px', or '10px 2px 5px 2px'", WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?>
                </div>
            </div>
            <div class="form-group adsense">
                <label class='col-sm-2 control-label'><?php _e( 'Ad Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-6'>
                    <label class='radio-inline'><input type='radio' name='wb_color[<?php echo $i; ?>]' class="wb_colordefault" id='wb_colordefault' value='default' checked='checked'><?php _e( 'Default', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                    <label class='radio-inline'><input type='radio' name='wb_color[<?php echo $i; ?>]' class="wb_colorcustom" id='wb_colorcustom' value='custom' ><?php _e( 'Custom', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                </div>
            </div>		
		
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colorborder'><?php _e( 'Border Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-6'>
                    <input type='text' name='wb_colorborder[<?php echo $i; ?>]' id='wb_colorborder' class='form-control color' maxlength=6 value='FFFFFF'>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colorbg'><?php _e( 'Background Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-6'>
                    <input type='text' name='wb_colorbg[<?php echo $i; ?>]' id='wb_colorbg' class='form-control color' maxlength=6 value='FFFFFF'>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colorlink'><?php _e( 'Link Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-6'>
                    <input type='text' name='wb_colorlink[<?php echo $i; ?>]' id='wb_colorlink' class='form-control color' maxlength=6 value='1E0FBE'>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colortext'><?php _e( 'Text Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-6'>
                    <input type='text' name='wb_colortext[<?php echo $i; ?>]' id='wb_colortext' class='form-control color' maxlength=6 value='373737'>
                </div>
            </div>
            
            <div class='form-group adsense as_adcustomcolorrow' style='display:none'>
                <label class='col-sm-2 control-label' for='wb_colorurl'><?php _e( 'URL Color', WBCOM_ADS_ROTATOR_TEXT_DOMIAN );?></label>
                <div class='col-sm-6'>
                    <input type='text' name='wb_colorurl[<?php echo $i; ?>]' id='as_colorurl' class='form-control color' maxlength=6 value='006621'>
                </div>
            </div>
    
        </div>
    </div>
	<?php
}


?>


</div>