<?php
global $wb_ad_sense;

//If form submitted, handle action
$message = "";
if( !empty( $_POST ) || count($_GET)>1 ) {
	if(isset($_REQUEST['as_action'])) $message = $wb_ad_sense->handle_action($_REQUEST['as_action']);
}

?>
<!--<link href="<?php echo WBCOM_ADS_ROTATOR_URL; ?>resources/aswrapped-bootstrap-3.0.3.css" rel="stylesheet" type="text/css">
<link href="<?php echo WBCOM_ADS_ROTATOR_URL; ?>resources/as_style.css" rel="stylesheet" type="text/css">-->
<div class='wbadssense'>
<?php
if(!empty($_GET['as_newsegment']))
{
	?>
<h2>Create New Segment</h2>
<p>Set up segments if you want to have specific ads show (or not show) on particular pages. <a href="http://www.ampedsense.com/creating-segments" target="_blank">Learn how</a></p>

<form method='post' class='form-horizontal' id="theform" action="<?php echo admin_url('edit.php?post_type=wb_ads_rotator&page=wb-ads-segments') ?>">
<input type='hidden' name='as_action' value='addsegment'>

<div class='form-group'>
	<label class='col-sm-2 control-label' for='as_criteria'>Criteria</label>
	<div class='col-sm-6'>
		<select name='as_criteria' id='as_criteria'>
			<option value='allpages'>All Pages</option>
			<option value='allposts'>All Posts</option>
			<option value='homepage'>Home Page</option>
			<option value='page'>Specific Page</option>
			<option value='post'>Specific Post</option>
			<option value='mobile'>Mobile Users</option>
		</select>
	</div>
</div>
<div class='form-group as_criteriasetting_page'>
	<label class='col-sm-2 control-label' for='as_criteriaparam_page'>Page</label>
	<div class='col-sm-6'>
		<select name='as_criteriaparam_page' id='as_criteriaparam_page'>
			<?php
			$args = array(
				'post_type' => 'page',
				'post_status' => 'publish'
				//defaults to all pages
			); 
			$pages = get_pages($args);
			foreach($pages as $page)
			{
				echo "<option value='".$page->ID."'>".$page->post_title."</option>";
			}
			?>
		</select>
	</div>
</div>
<div class='form-group as_criteriasetting_post'>
	<label class='col-sm-2 control-label' for='as_criteriaparam_post'>Post</label>
	<div class='col-sm-6'>
		<select name='as_criteriaparam_post' id='as_criteriaparam_post'>
			<?php
			$args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => -1 //defaults to only 5, -1 is undocumented, but saw some code samples that use it. Seems to work
			); 
			$posts = get_posts($args);
			foreach($posts as $post)
			{
				echo "<option value='".$post->ID."'>".$post->post_title."</option>";
			}
			?>
		</select>
	</div>
</div>
<div class='form-group'>
	<label class='col-sm-2 control-label' for='as_segmentname'>Segment Name</label>
	<div class='col-sm-6'>
		<input type='text' class='form-control' name='as_segmentname' id='as_segmentname'>
	</div>
</div>
<div class='form-group'>
	<label class='col-sm-2 control-label' for='as_segmentabbrev'>Segment Abbreviation</label>
	<div class='col-sm-6'>
		<input type='text' class='form-control' name='as_segmentabbrev' id='as_segmentabbrev' maxlength='3'>
		<br/>
		3-letter abbreviation to be used as a prefix in reports
	</div>
</div>
<div class='form-group'>
	<div class="col-sm-offset-2 col-sm-6">
		<button type='submit' class='btn btn-primary'>Save Segment</button>
	</div>
</div>
</form>

<script>
//show ir settings if enabled

</script>
	<?php
}
else
{
	?>
<h2>Segments</h2>
<h4>Segments are a way to separate your traffic into specific categories</h4>
<p>Need help? <a href="http://www.ampedsense.com/creating-segments" target="_blank">How to use segments</a></p>
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
			echo "<td><a href='".admin_url('edit.php?post_type=wb_ads_rotator&page=wb-ads-segments')."&as_action=deletesegment&as_segmentindex=$i' title='Delete' onClick=\"return confirm('Are you sure you want to delete? All ad recipes associated with this segment will also be deleted.')\">X</a></td>";
			echo "<td>$seg[segmentname]</td>";
			echo "<td>$seg[segmentabbrev]</td>";
			echo "<td>$seg[criteria]";
			if(!empty($seg['criteriaparam'])) echo ": $seg[criteriaparam]";
			echo "</td>";
			echo "<td><input type='text' name='priority[$i]' value='".($i+1)."'/></td>";
			
			echo "</tr>";
		}
		echo "<tr><td colspan=4></td><td><button type='submit' class='btn btn-primary'>Reorder Segments</button></td></tr>";
		echo "</form>";
	}
	else
	{
		echo "<tr><td colspan=5>No segments. Add one below!</td></tr>";
	}
	?>
</table>
<br/>
<a href='<?php echo admin_url('edit.php?post_type=wb_ads_rotator&page=wb-ads-segments'); ?>&as_newsegment=1'>Create New Segment</a>
	<?php
}
?>
</div>