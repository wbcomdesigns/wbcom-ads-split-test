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
<h2>Create New Filter</h2>
<p>Set up filters if you want to have specific ads show (or not show) on particular pages. <a href="#" target="_blank">Learn how</a></p>

<form method='post' class='form-horizontal' id="theform" action="<?php echo admin_url('admin.php?page=wb-ads-segments') ?>">
<input type='hidden' name='as_action' value='addsegment'>

<div class='form-group'>
	<label class='col-sm-2 control-label' for='as_criteria'>Criteria</label>
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
				}?>
			<option value='homepage'>Home Page</option>
			<option value='mobile'>Mobile Users</option>
		</select>
	</div>
</div>
<div class='form-group as_ex_in_id'>
	<label class='col-sm-2 control-label' for='as_exclude'>Exclude Ids</br>(eg- 1,2,3,...)</label>
	<div class='col-sm-6'>
		<input type='text' class='form-control' name='as_exclude' id='as_exclude'>
	</div>
</div>
<div class='form-group as_ex_in_id'>
	<label class='col-sm-2 control-label' for='as_include'>Include Ids</br>(eg- 1,2,3,...)</label>
	<div class='col-sm-6'>
		<input type='text' class='form-control' name='as_include' id='as_include'>
	</div>
</div>
<div class='form-group'>
	<label class='col-sm-2 control-label' for='as_segmentname'>Filter Name</label>
	<div class='col-sm-6'>
		<input type='text' class='form-control' name='as_segmentname' id='as_segmentname'>
	</div>
</div>
<div class='form-group'>
	<label class='col-sm-2 control-label' for='as_segmentabbrev'>Filter Abbreviation</label>
	<div class='col-sm-6'>
		<input type='text' class='form-control' name='as_segmentabbrev' id='as_segmentabbrev' maxlength='3'>
		<br/>
		3-letter abbreviation to be used as a prefix in reports
	</div>
</div>
<div class='form-group'>
	<div class="col-sm-offset-2 col-sm-6">
		<button type='submit' class='btn btn-primary'>Save Filter</button>
	</div>
</div>
</form>

	<?php
}
else
{
	?>
<h2>Filters</h2>
<h4>Filters are a way to separate your traffic into specific categories</h4>
<p>Need help? <a href="#" target="_blank">How to use filters</a></p>
<?php if($message) echo "<div class='as_statusmessage'>$message</div>"; ?>
<table class='table table-hover'>
	<tr><th></th><th>Name</th><th>Abbrev.</th><th>Criteria</th><th>Priority</th></tr>
	<?php
	if(count($wb_ad_sense->settings['segments']))
	{
		echo "<form method='post'>";
		echo "<input type='hidden' name='as_action' value='reordersegments'>";
		foreach($wb_ad_sense->settings['segments'] as $i=>$seg)
		{
			echo "<tr>";
			echo "<td><a href='".admin_url('admin.php?page=wb-ads-segments')."&as_action=deletesegment&as_segmentindex=$i' title='Delete' onClick=\"return confirm('Are you sure you want to delete? All ad layouts associated with this filter will also be deleted.')\">X</a></td>";
			echo "<td>$seg[segmentname]</td>";
			echo "<td>$seg[segmentabbrev]</td>";
			echo "<td>$seg[criteria]";
			if(!empty($seg['criteriaparam'])) echo ": $seg[criteriaparam]";
			echo "</td>";
			echo "<td><input type='text' name='priority[$i]' value='".($i+1)."'/></td>";
			
			echo "</tr>";
		}
		echo "<tr><td colspan=4><a href='".admin_url('admin.php?page=wb-ads-segments')."&as_newsegment=1'>Create New Filter</a></td><td><button type='submit' class='btn btn-primary'>Reorder Filters</button></td></tr>";
		echo "</form>";
	}
	else
	{
		echo "<tr><td colspan=5>No filters. Add one below!</td></tr>";
	}
	?>
</table>
	<?php
}
?>
</div>