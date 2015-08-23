<?php
/////////////// WIDGETS ////////////////////

class WbAdsSidebarA extends WP_Widget
{
	public function __construct()
	{
		// widget actual processes
		parent::__construct(
			'WbAdsSidebarA', // Base ID
			'WbAds Sidebar A', // Name
			array( 'description' => __( 'A container for split testing AdSense on a sidebar', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ), ) // Args
		);
	}

	public function widget( $args, $instance )
	{
		// outputs the content of the widget
		global $wb_ad_sense;
		if(count($wb_ad_sense->widgetA_renderers))
		{
			foreach($wb_ad_sense->widgetA_renderers as $renderer)
			{
				$adhtml = $renderer->render_ad();
				$padding = (isset($renderer->ad['adpadding']) && $renderer->ad['adpadding']!="") ? "padding: ".$renderer->ad['adpadding'] : "";
				echo $renderer->watermark."<div style='width:100%; text-align:center; $padding'><span><img style='float: left;padding: 2px;' src='http://modernlensmagazine.com/files/2015/04/ad-header.png' /></span><br />".$adhtml."</div>";
			}
		}		
	}
	//don't need form() or update() since no options
}

class WbAdsSidebarB extends WP_Widget
{
	public function __construct()
	{
		// widget actual processes
		parent::__construct(
			'WbAdsSidebarB', // Base ID
			'WbAds Sidebar B', // Name
			array( 'description' => __( 'A container for split testing AdSense on a sidebar', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ), ) // Args
		);
	}

	public function widget( $args, $instance )
	{
		// outputs the content of the widget
		global $wb_ad_sense;
		if(count($wb_ad_sense->widgetB_renderers))
		{
			foreach($wb_ad_sense->widgetB_renderers as $renderer)
			{
				$adhtml = $renderer->render_ad();
				$padding = (isset($renderer->ad['adpadding']) && $renderer->ad['adpadding']!="") ? "padding: ".$renderer->ad['adpadding'] : "";
				echo $renderer->watermark."<div style='width:100%; text-align:center; $padding'><span><img style='float: left;padding: 2px;' src='http://modernlensmagazine.com/files/2015/04/ad-header.png' /></span><br />".$adhtml."</div>";
			}
		}
	}
	//don't need form() or update() since no options
}

class WbAdsSidebarC extends WP_Widget
{
	public function __construct()
	{
		// widget actual processes
		parent::__construct(
			'WbAdsSidebarC', // Base ID
			'WbAds Sidebar C', // Name
			array( 'description' => __( 'A container for split testing AdSense on a sidebar', WBCOM_ADS_ROTATOR_TEXT_DOMIAN ), ) // Args
		);
	}

	public function widget( $args, $instance )
	{
		// outputs the content of the widget
		global $wb_ad_sense;
		if(count($wb_ad_sense->widgetC_renderers))
		{
			foreach($wb_ad_sense->widgetC_renderers as $renderer)
			{
				$adhtml = $renderer->render_ad();
				$padding = (isset($renderer->ad['adpadding']) && $renderer->ad['adpadding']!="") ? "padding: ".$renderer->ad['adpadding'] : "";
				echo $renderer->watermark."<div style='width:100%; text-align:center; $padding'><span><img style='float: left;padding: 2px;' src='http://modernlensmagazine.com/files/2015/04/ad-header.png' /></span><br />".$adhtml."</div>";
			}
		}
	}
	//don't need form() or update() since no options
}
?>