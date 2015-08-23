<?php

//need to make seperate class so can associate ad with callback
class WbRenderer
{
	public $ad = null;
	public $watermark = "<!-- Ad split testing with WbAdsRotator: http://www.wbcomdesigns.com -->";
	
	public function inject_ad_in_content($thecontent)
	{
		$adhtml = $this->render_ad();
		$padding = (isset($this->ad['adpadding']) && $this->ad['adpadding']!="") ? "padding: ".$this->ad['adpadding'] : "";
		if(isset($this->ad['color']) && $this->ad['color']=='custom')
			{
				$css="";
				$css.="border:1px solid #".$this->ad['color_border'].";";
				$css.="background-color:#".$this->ad['color_bg'].";";
				$css.="color:#".$this->ad['color_text'].";";
				//$ad['color_url']=$this->ad['color_url'];
				//$ad['color_link']=$this->ad['color_link'];
			}
		
		if($this->ad['adlocation']=="AP")
		{
			//above post
			$thecontent = $this->watermark."<div style='width:100%; text-align:center; $css $padding'><span></span><br />".$adhtml."</div>".$thecontent;
		}
		elseif($this->ad['adlocation']=="IL")
		{
			//inline left
			$thecontent = $this->watermark."<div style='float:left; $css $padding'><span></span><br />".$adhtml."</div>".$thecontent;
		}
		elseif($this->ad['adlocation']=="IR")
		{
			//inline right
			$thecontent = $this->watermark."<div style='float:right; $css $padding'><span></span><br />".$adhtml."</div>".$thecontent;
		}
		elseif($this->ad['adlocation']=="PL")
		{
			//after 1st paragraph, left			
			$addiv = $this->watermark."<div style='float:left; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"P");
		}
		elseif($this->ad['adlocation']=="PC")
		{
			//after 1st paragraph, center			
			$addiv = $this->watermark."<div style='width:100%; text-align:center; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"P");
		}
		elseif($this->ad['adlocation']=="PR")
		{
			//after 1st paragraph, right			
			$addiv = $this->watermark."<div style='float:right; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"P");
		}
		elseif($this->ad['adlocation']=="1L")
		{
			//after 1/4 of content, left			
			$addiv = $this->watermark."<div style='float:left; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"1");
		}
		elseif($this->ad['adlocation']=="1C")
		{
			//after 1/4 of content, center			
			$addiv = $this->watermark."<div style='width:100%; text-align:center; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"1");
		}
		elseif($this->ad['adlocation']=="1R")
		{
			//after 1/4 of content, right			
			$addiv = $this->watermark."<div style='float:right; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"1");
		}
		elseif($this->ad['adlocation']=="2L")
		{
			//after 1/2 of content, left			
			$addiv = $this->watermark."<div style='float:left; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"2");
		}
		elseif($this->ad['adlocation']=="2C")
		{
			//after 1/2 of content, center			
			$addiv = $this->watermark."<div style='width:100%; text-align:center; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"2");
		}
		elseif($this->ad['adlocation']=="2R")
		{
			//after 1/2 of content, right			
			$addiv = $this->watermark."<div style='float:right; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"2");
		}
		elseif($this->ad['adlocation']=="3L")
		{
			//after 3/4 of content, left			
			$addiv = $this->watermark."<div style='float:left; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"3");
		}
		elseif($this->ad['adlocation']=="3C")
		{
			//after 3/4 of content, center			
			$addiv = $this->watermark."<div style='width:100%; text-align:center; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"3");
		}
		elseif($this->ad['adlocation']=="3R")
		{
			//after 3/4 of content, right			
			$addiv = $this->watermark."<div style='float:right; $css $padding'><span></span><br />".$adhtml."</div>";
			$thecontent = $this->inject_at_distance($thecontent,$addiv,"3");
		}
		elseif($this->ad['adlocation']=="BP")
		{
			//below post
			$thecontent = $thecontent.$this->watermark."<div style='width:100%; text-align:center; $css $padding'><span></span><br />".$adhtml."</div>";
		}
		
		//return since filter
		return $thecontent;
	}
	
	public function inject_at_distance($thecontent,$addiv,$distance)
	{
		//$distance = P/1/2/3
		//split content into paragraph arr
		//don't have <p>'s yet, still \n
		//$closingp = "\n\n";
		//$paragrapharr = explode($closingp, $thecontent);
		$paragrapharr = preg_split('/(\n\s*\n|<\/p>)/', $thecontent, -1, PREG_SPLIT_NO_EMPTY); //this is what wpautop() uses, except I added the |</p>
		
		
		//figure out index
		$insertindex = 0;
		if( $distance=="P" )
		{
			//after p1
			$insertindex = 1;
		}
		elseif( $distance=="1" )
		{
			// 1/4 way down
			$insertindex = round(count($paragrapharr)*.25);
		}
		elseif( $distance=="2" )
		{
			// 1/2 way down
			$insertindex = round(count($paragrapharr)*.5);
		}
		elseif( $distance=="3" )
		{
			// 3/4 way down
			$insertindex = round(count($paragrapharr)*.75);
		}
		
		//insert it
		array_splice( $paragrapharr, $insertindex, 0, array( $addiv ) );
		
		//join it all back
		return implode( "\n\n", $paragrapharr );
	}
	
	public function render_ad()
	{
		//return ad snippet
		if( $this->ad['custom'] == "html" )
		{
			return $this->ad['customcode'];
		}
		elseif( $this->ad['custom'] == "script" )
		{
			return "<script>".$this->ad['customcode']."</script>";
		}
	}
}
?>
