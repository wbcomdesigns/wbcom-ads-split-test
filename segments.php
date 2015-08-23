<?php
global $wb_ad_sense;

//If form submitted, handle action
$message = "";
if( !empty( $_POST ) || count($_GET)>1 ) {
	if(isset($_REQUEST['as_action'])) $message = $wb_ad_sense->handle_action($_REQUEST['as_action']);
}

?>
<div class='wbadssense'>
<?php
if(!empty($_GET['as_newsegment']))
{
	?>
<h2><?php _e( 'Create New Filter', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></h2>
<p><?php _e( 'Set up filters if you want to have specific ads show (or not show) on particular pages.', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?> <a href="#" target="_blank"><?php _e( 'Learn how', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></a></p>

<form method='post' class='form-horizontal' id="theform" action="<?php echo admin_url('admin.php?page=wb-ads-segments') ?>">
<input type='hidden' name='as_action' value='addsegment'>

<div class='form-group'>
	<label class='col-sm-2 control-label' for='as_criteria'><?php _e( 'Criteria', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></label>
	<div class='col-sm-6'>
		<select name='as_criteria' id='as_criteria'>
			<?php 
				$args = array(
				   'public' => true
				);
				$output = 'objects'; // names or objects
				$post_types = get_post_types($args, $output);
					foreach ( $post_types  as $post_type ) {
					if($post_type->name!='wb_ads_rotator')
			   		echo '<option value="'.$post_type->name.'">'.$post_type->label.'</option>';
				}
				do_action('wb_ads_add_new_option');
				?>
			<option value='homepage'><?php _e( 'Home Page', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></option>
			<option value='mobile'><?php _e( 'Mobile Users', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></option>
		</select>
	</div>
</div>
<div class='form-group as_ex_in_id'>
	<label class='col-sm-2 control-label' for='as_exclude'><?php _e( 'Exclude Ids</br>(eg- 1,2,3,...)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></label>
	<div class='col-sm-6'>
		<input type='text' class='form-control' name='as_exclude' id='as_exclude'>
	</div>
</div>
<div class='form-group as_ex_in_id'>
	<label class='col-sm-2 control-label' for='as_include'><?php _e( 'Include Ids</br>(eg- 1,2,3,...)', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></label>
	<div class='col-sm-6'>
		<input type='text' class='form-control' name='as_include' id='as_include'>
	</div>
</div>
<div class='form-group'>
	<label class='col-sm-2 control-label' for='as_segmentname'><?php _e( 'Filter Name', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></label>
	<div class='col-sm-6'>
		<input type='text' class='form-control' name='as_segmentname' id='as_segmentname'>
	</div>
</div>
<div class='form-group'>
	<label class='col-sm-2 control-label' for='as_segmentabbrev'><?php _e( 'Filter Abbreviation', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></label>
	<div class='col-sm-6'>
		<input type='text' class='form-control' name='as_segmentabbrev' id='as_segmentabbrev' maxlength='3'>
		<br/>
		<?php _e( '3-letter abbreviation to be used as a prefix in reports', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?>
	</div>
</div>
<div class='form-group'>
	<div class="col-sm-offset-2 col-sm-6">
		<button type='submit' class='btn btn-primary'><?php _e( 'Save Filter', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></button>
	</div>
</div>
</form>

	<?php
}
else
{
	?>
<h2><?php _e( 'Filters', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></h2>
<h4><?php _e( 'Filters are a way to separate your traffic into specific categories', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></h4>
<p><?php _e( 'Need help? ', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?><a href="#" target="_blank"><?php _e( 'How to use filters', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></a></p>
<?php if($message) echo "<div class='as_statusmessage'>$message</div>"; ?>
<table class='table table-hover'>
	<tr><th></th><th><?php _e( 'Name', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></th><th><?php _e( 'Abbrev.', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></th><th><?php _e( 'Criteria', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></th><th><?php _e( 'Priority', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )?></th></tr>
	<?php
	if(count($wb_ad_sense->settings['segments']))
	{
		echo "<form method='post'>";
		echo "<input type='hidden' name='as_action' value='reordersegments'>";
		foreach($wb_ad_sense->settings['segments'] as $i=>$seg)
		{
			echo "<tr>";
			echo "<td><a href='".admin_url('admin.php?page=wb-ads-segments')."&as_action=deletesegment&as_segmentindex=$i' title='Delete' onClick=\"return confirm('".__( 'Are you sure you want to delete? All ad layouts associated with this filter will also be deleted.', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )."')\">X</a></td>";
			echo "<td>$seg[segmentname]</td>";
			echo "<td>$seg[segmentabbrev]</td>";
			echo "<td>$seg[criteria]";
			if(!empty($seg['criteriaparam'])) echo ": $seg[criteriaparam]";
			echo "</td>";
			echo "<td><input type='text' name='priority[$i]' value='".($i+1)."'/></td>";
			
			echo "</tr>";
		}
		echo "<tr><td colspan=4><a href='".admin_url('admin.php?page=wb-ads-segments')."&as_newsegment=1'>".__( 'Create New Filter', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )."</a></td><td><button type='submit' class='btn btn-primary'>".__( 'Reorder Filters', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )."</button></td></tr>";
		echo "</form>";
	}
	else
	{
		echo "<tr><td colspan=5>".__( 'No filters. Add one below!', WBCOM_ADS_ROTATOR_TEXT_DOMIAN )."</td></tr>";
	}
	?>
</table>
	<?php
}
?>
</div>