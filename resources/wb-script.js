// JavaScript Document
jQuery(document).ready(function(e) {
		//show/hide sidebar assistance, and suggest padding
		jQuery('#wb_adlocation').change(suggestPadding);
		function suggestPadding() {
			var val = jQuery('#wb_adlocation').val();
			var padding = "10px"; //default
			if(val=='SA' || val=='SB' || val=='SC')
			{
				jQuery('#sidebarwarning').show();
			}
			else
			{
				jQuery('#sidebarwarning').hide();
				
				if(val=='AP') padding = "0px 0px 10px 0px";
				if(val=='IR') padding = "0px 0px 10px 10px";
				if(val=='IL') padding = "0px 10px 10px 0px";
				if(val=='BP') padding = "10px 0px 0px 0px";
				
			}
			jQuery('#wb_adpadding').val(padding);
		}
		
		
		//show/hide custom color
		jQuery('#wb_colordefault, #wb_colorcustom').change(toggleAdColor);
		function toggleAdColor() {
			if(jQuery('#wb_colorcustom').attr('checked'))
			{
				jQuery('.as_adcustomcolorrow').show();
			}
			else
			{
				jQuery('.as_adcustomcolorrow').hide();
			}
		}
		toggleAdColor();
		
 function toggleCriteriaSettings() {
	var val = jQuery('#as_criteria').val();
	
	//set name and abbrev
	if(val=='allpages')
	{
		jQuery('#as_segmentname').val('All Pages');
		jQuery('#as_segmentabbrev').val('Pgs');
	}
	else if(val=='allposts')
	{
		jQuery('#as_segmentname').val('All Posts');
		jQuery('#as_segmentabbrev').val('Pts');
	}
	else if(val=='homepage')
	{
		jQuery('#as_segmentname').val('Home Page');
		jQuery('#as_segmentabbrev').val('Hom');
	}
	else if(val=='page')
	{
		var pgname = jQuery('#as_criteriaparam_page option:selected').text();
		jQuery('#as_segmentname').val(pgname);
		jQuery('#as_segmentabbrev').val(pgname.substring(0,3));
	}
	else if(val=='post')
	{
		var postname = jQuery('#as_criteriaparam_post option:selected').text();
		jQuery('#as_segmentname').val(postname);
		jQuery('#as_segmentabbrev').val(postname.substring(0,3));
	}
	else if(val=='mobile')
	{
		jQuery('#as_segmentname').val('Mobile');
		jQuery('#as_segmentabbrev').val('Mob');
	}
	
	//show/hide other fields
	if(val=='page')
	{
		jQuery('.as_criteriasetting_post').hide();
		jQuery('.as_criteriasetting_page').show();
	}
	else if(val=='post')
	{
		jQuery('.as_criteriasetting_page').hide();
		jQuery('.as_criteriasetting_post').show();
	}
	else
	{
		jQuery('.as_criteriasetting_page').hide();
		jQuery('.as_criteriasetting_post').hide();
	}
}
jQuery('#as_criteria, #as_criteriaparam_page, #as_criteriaparam_post').change(toggleCriteriaSettings);
toggleCriteriaSettings(); //call now to set on first page load

//validation
jQuery('#theform').submit(function() {
	//segmentname
	if(jQuery('#as_segmentname').val()=="")
	{
		alert('Please enter the segment name');
		jQuery('#as_segmentname').focus();
		return false;
	}
	//segmentabbrev
	if(jQuery('#as_segmentabbrev').val()=="")
	{
		alert('Please enter the segment abbreviation');
		jQuery('#as_segmentabbrev').focus();
		return false;
	}
	if(jQuery('#as_segmentabbrev').val().length>3)
	{
		alert('Segment abbreviation must be 3 letters or less');
		jQuery('#as_segmentabbrev').focus();
		return false;
	}
	return true;
});
    
});