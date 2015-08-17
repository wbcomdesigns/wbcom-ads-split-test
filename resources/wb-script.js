// JavaScript Document
jQuery(document).ready(function(e) {
		//show/hide sidebar assistance, and suggest padding
		jQuery('.wb_adlocation').on('change',function(){
			var val = jQuery(this).val();
			var padding = "10px"; //default
			if(val=='SA' || val=='SB' || val=='SC')
			{
				jQuery(this).closest('.single-ad').find('.sidebarwarning').show();
			}
			else
			{
				jQuery(this).closest('.single-ad').find('.sidebarwarning').hide();
				
				if(val=='AP') padding = "0px 0px 10px 0px";
				if(val=='IR') padding = "0px 0px 10px 10px";
				if(val=='IL') padding = "0px 10px 10px 0px";
				if(val=='BP') padding = "10px 0px 0px 0px";
				
			}
				jQuery(this).closest('.single-ad').find('input[name*="wb_adpadding"]').val(padding);
			
		});
		
		
		//show/hide custom color
		jQuery('input[name*="wb_color"]').change(toggleAdColor);
		function toggleAdColor() {
			jQuery('input[name*="wb_color"]:checked').each(function( index ) {
				if(jQuery(this).attr('checked') && jQuery(this).hasClass('wb_colorcustom'))
				{
					jQuery(this).closest('.single-ad').find('.as_adcustomcolorrow').show();
				}
				else
				{
					jQuery(this).closest('.single-ad').find('.as_adcustomcolorrow').hide();
				}
			});
		}
		toggleAdColor();
		
 function toggleCriteriaSettings() {
	var val = jQuery('#as_criteria').val();
	
	//set name and abbrev
	if(val=='mobile')
	{
		jQuery('#as_segmentname').val('Mobile');
		jQuery('#as_segmentabbrev').val('Mob');
	}
	else if(val=='homepage')
	{
		jQuery('#as_segmentname').val('Home Page');
		jQuery('#as_segmentabbrev').val('Hom');
	}
	else
	{
		lable=jQuery('#as_criteria option:selected').text();
		jQuery('#as_segmentname').val(lable);
		jQuery('#as_segmentabbrev').val(lable.substr(0,3));
	}
	
	//show/hide other fields
	if(val=='mobile' || val=='homepage')
	{
		jQuery('.as_ex_in_id').hide();
	}
	else
	{
		jQuery('.as_ex_in_id').show();
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

	function revealAdOptions()
		{
			var numads = jQuery('#as_numads').val();
			for(var i=1; i<=6; i++)
			{
				var divid = "#adoptions"+i;
				if(i<=numads) 
				jQuery(divid).show();
				else jQuery(divid).hide();
			}
			
		}
	jQuery('#as_numads').change(revealAdOptions);
	revealAdOptions(); //call now to set on first page load
    
	jQuery('.new_ad_option').hide();
	
});
	function show_new_ad(i)
	{
		jQuery("#adoptions"+i+" .current-ad").slideUp('slow');
		jQuery("#adoptions"+i+" .new_ad_option").slideDown('slow');
		jQuery('input[name="new_ad['+i+']"]').val('yes');
	}
	function hide_new_ad(i)
	{
		jQuery("#adoptions"+i+" .current-ad").slideDown('slow');
		jQuery("#adoptions"+i+" .new_ad_option").slideUp('slow');
		jQuery('input[name="new_ad['+i+']"]').val('');
	}