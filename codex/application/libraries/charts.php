<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
|-------------------------------------------------------------------------
| Code Igniter Charts Class Version 1.3
| Functionality is draw 2/3D pie, line,cubic line and bar  charts. 
|
| Copyright (C) 2009  Andrea Consigli
| Website: http://insistema.com
|-------------------------------------------------------------------------
| This library is free software; you can redistribute it and/or
| modify it under the terms of the GNU Lesser General Public
| License as published by the Free Software Foundation; either
| version 2.1 of the License, or (at your option) any later version.
| 
| This library is distributed in the hope that it will be useful,
| but WITHOUT ANY WARRANTY; without even the implied warranty of
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
| Lesser General Public License for more details.
| 
| You should have received a copy of the GNU Lesser General Public
| License along with this library; if not, write to the Free Software
| Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
| 
| Last changed:
| 20 Jan '09 Andrea Consigli, aconsigli at insistema dot com
|
*/

class Charts {

	var $obj;
	private $_ext_path = "";
	private $_img_path = "";

	function Charts()
	{
		$this->obj =& get_instance();
		$this->obj->config->load('charts');
		$this->_ext_path = $this->obj->config->item('charts_ext_path');  
		$this->_img_path = $this->obj->config->item('charts_img_path'); 
		include_once($this->_ext_path."/classes/pData.class");  
		include_once($this->_ext_path."/classes/pChart.class"); 
	}

	function pieChart($r, $percents, $legend,$imgname='', $title='',$bottom_label='',$config=array())
	{
		$settings = array('Skew'     => 90,
										'SpliceHeight'=>10,
										'SpliceDistance'=>0,
										'PieFontSize' => 8,
										'TitleFontSize' => 10,
										'LegendFontSize' => 8,
										'LabelFontSize' => 10,
										'PieFontName' => 'tahoma.ttf',
										'TitleFontName' => 'tahoma.ttf',
										'LegendFontName' => 'tahoma.ttf',
										'LabelFontName' => 'tahoma.ttf',
										'TitleBGR' => 0,
										'TitleBGG' => 0,
										'TitleBGB' => 0,
										'TitleFGR' => 0,
										'TitleFGG' => 0,
										'TitleFGB' => 0,
										'ImgR' => 240,
										'ImgG' => 240,
										'ImgB' => 240,
										'BorderR' => 0,
										'BorderG' => 0,
										'BorderB' => 0,
										'LegendR' => 250,
										'LegendG' => 250,
										'LegendB' => 250,
										'LabelBGR' => 0,
										'LabelBGG' => 0,
										'LabelBGB' => 0,
										'LabelFGR' => 0,
										'LabelFGG' => 0,
										'LabelFGB' => 0);
										
		// Get the custom settings
		if(is_array($config))
		{
			foreach ($config as $key => $val)
				$settings[$key] = $val;
		}	

		if ($settings['SpliceHeight'] == 0) { $settings['SpliceHeight'] = 1; } // avoid division by zero in pChart

		$PieFontSize    = $settings['PieFontSize'];
		$TitleFontSize  = $settings['TitleFontSize'];
		$LegendFontSize = $settings['LegendFontSize'];
		$LabelFontSize  = $settings['LabelFontSize'];
		$PieFontName    = $this->_ext_path."/fonts/".$settings['PieFontName'];
		$TitleFontName  = $this->_ext_path."/fonts/".$settings['TitleFontName'];
		$LegendFontName = $this->_ext_path."/fonts/".$settings['LegendFontName'];
		$LabelFontName  = $this->_ext_path."/fonts/".$settings['LabelFontName'];
		$Skew           = $settings['Skew'];
			
		// Calc legend size
		$Wmax = 0;
		$TextHeight=0;
		foreach($legend as $lg)
		{
			$Position   = imageftbbox($LegendFontSize,0,$LegendFontName,$lg); 
			$TextWidth  = $Position[2]-$Position[0];
      $TextHeight = $TextHeight + $Position[1]-$Position[7];
			$Wmax = ($TextWidth > $Wmax) ? $TextWidth : $Wmax; // Maximum width of the legend
		}	
		$legendW = $Wmax + 30; 
		if(strlen($title) > 0) 
		{
			$Position   = imageftbbox($TitleFontSize,0,$TitleFontName,$title); 
			$titleWidth  = $Position[2]-$Position[0] + 40;
			$titleHeight = $Position[1]-$Position[7] + 8;
		}
		else
		{
			$titleWidth = 0;		
			$titleHeight = 0;
		}
		if(strlen($bottom_label) > 0) 
		{
			$Position   = imageftbbox($LabelFontSize,0,$LabelFontName,$bottom_label); 
			$labelWidth  = $Position[2]-$Position[0] + 8;
			$labelHeight = $Position[1]-$Position[7] + 8;
		}
		else
		{
			$labelWidth = 0;
			$labelHeight = 0;		
		}
		// Calculate  pie size based on fontsize radius and skew
		$Position  = imageftbbox($PieFontSize,0,$PieFontName,'199%'); 
		$pieWidth  = ($Position[2]-$Position[0])*2+($r*2);
		$sin = ($Skew != 90) ? abs(sin(deg2rad($Skew))) : 1; // if skewed the height goes with sin($skew)
		$SpliceHeight  = ($Skew != 90) ? $settings['SpliceHeight'] : 0; // if skewed add the SpliceHeight
		$SpliceDistance= $settings['SpliceDistance']*2+30;
		$pieHeight = intval(($Position[1]-$Position[7])*2+($r*2*$sin)+$SpliceHeight);

		$h = intval($pieHeight+$SpliceDistance*$sin); // Img Height 
		$y = intval(($pieHeight- $SpliceHeight)/2 + $SpliceDistance/2) ; // center y pos
		$x = intval($pieWidth/2) + $SpliceDistance/2; // center x pos

		$w = ($x*2)	+ (($legendW > $labelWidth)?$legendW:$labelWidth)+10;	
		if($titleWidth>$w)
			$w=$titleWidth;
		$h2 = $labelHeight + $titleHeight + $TextHeight+60; // If the legend has big height
		if($h2 > $h) { $h=$h2; }  
		
		//Add pixels for the title
		$h = $h + $titleHeight+10; // Real height
		$y = $y + $titleHeight+10; // Real center y 

		// Dataset definition	
		$DataSet = new pData;  
		$DataSet->AddPoint($percents,"Serie1");  
		$DataSet->AddPoint($legend,"Serie2");  
		$DataSet->AddAllSeries();  
		$DataSet->SetAbsciseLabelSerie("Serie2");  
		 
		// Initialise the graph  
		$Test = new pChart($w,$h);  
		$red  = $settings['ImgR'];
		$g    = $settings['ImgG'];
		$b    = $settings['ImgB'];
		$Test->drawFilledRoundedRectangle(7,7,$w-7,$h-7,5,$red,$g,$b);  
		$red  = $settings['BorderR'];
		$g    = $settings['BorderG'];
		$b    = $settings['BorderB'];
		$Test->drawRoundedRectangle(5,5,$w-5,$h-5,5,$red,$g,$b);  
		 
		// Draw the pie chart  
		$Test->setFontProperties($PieFontName,$PieFontSize);  
		if($Skew != 90) // 3D pie
		{	
				$Test->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),
														$x,$y,$r,PIE_PERCENTAGE,TRUE,$Skew,$settings['SpliceHeight'],$settings['SpliceDistance']);  
		}
		else // 2D pie
		{
			if($settings['SpliceDistance'] == 0)
			{
				$Test->drawFilledCircle($x+2,$y+2,$r,0,0,0); // This will draw a shadow under the pie chart  
				$Test->drawBasicPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),$x,$y,$r,PIE_PERCENTAGE,255,255,218);  
			}	
			else
			{
				$Test->setShadowProperties(2,2,200,200,200);  
				$Test->drawFlatPieGraphWithShadow($DataSet->GetData(),$DataSet->GetDataDescription(),
																					$x,$y,$r,PIE_PERCENTAGE,$settings['SpliceDistance']);  
			}
		}
		// Draw the legend
		$x1 = $x*2-5;
		$r  = $settings['LegendR'];
		$g  = $settings['LegendG'];
		$b  = $settings['LegendB'];
		$Test->setFontProperties($LegendFontName,$LegendFontSize);  
		$Test->drawPieLegend($x1,$titleHeight+30,$DataSet->GetData(),$DataSet->GetDataDescription(),$r,$g,$b);  
		//Draw the title
		if(strlen($title) > 0) 
		{
			$Test->setFontProperties($TitleFontName,$TitleFontSize);  
			$r  = $settings['TitleFGR'];
			$g  = $settings['TitleFGG'];
			$b  = $settings['TitleFGB'];
			$r1 = $settings['TitleBGR'];
			$g1 = $settings['TitleBGG'];
			$b1 = $settings['TitleBGB'];
			$Test->drawTextBox(6,6,$w-5,$titleHeight+10,$title,0,$r,$g,$b,ALIGN_CENTER,FALSE,$r1,$g1,$b1,50);  
		}
		if(strlen($bottom_label) > 0) //Draw the bottom_label
		{
			$Test->setFontProperties($LabelFontName,$LabelFontSize);  
			$r  = $settings['LabelFGR'];
			$g  = $settings['LabelFGG'];
			$b  = $settings['LabelFGB'];
			$r1 = $settings['LabelBGR'];
			$g1 = $settings['LabelBGG'];
			$b1 = $settings['LabelBGB'];
			$x1 = $x*2-10;
			$Test->drawTextBox($x1,$h-$labelHeight-15,$w-10,$h-10,$bottom_label,0,$r,$g,$b,ALIGN_CENTER,FALSE,$r1,$g1,$b1,20);  
		}	
		if(strlen($imgname))
			$imgname = $this->_img_path."/".$imgname;  
		else
		{
			$this->obj->load->helper('string');
			$imgname = $this->_img_path."/pie-".random_string('alnum', 16).".png";  
		}	
		$Test->Render($imgname);  
		return  array("name" => '/'.$imgname,"w" => $w, "h" => $h);
	}
	function cartesianChart($type,$x,$y,$w,$h,$imgname='',$config=array())
	{
		$w-=4;
		$h-=4;
		$settings = array('FontName' => 'tahoma.ttf',
											'FontSize' => 8,
											'LegendFontSize' => 8,
											'LegendFontName' => 'tahoma.ttf',
											'Logo' => '',
											'LogoTransparency' => 20,
											'XAxisFormat' => 'number',
											'XAxisUnit' => '',
											'YAxisFormat' => 'number',
											'YAxisUnit' => '',
											'XLogo' => 0,
											'YLogo' => 0,
											'Xlabel' => 'x label',
											'XAngle' => 0,
											'Ylabel' => 'y label',
											'Legend'=>'',
											'Textbox' => '',
											'TextboxFontSize' => 8,
											'TextboxFontName' => 'tahoma.ttf',
											'ImgR' => 132,
											'ImgG' => 173,
											'ImgB' => 131,
											'Decay' => 80,
											'BGR' => 163,
											'BGG' => 203,
											'BGB' => 167,
											'Decay2' => 80,
											'Filled' => '',
											'DataR' => 191,
											'DataG' => 120,
											'DataB' => 71,
											'LBR' => 226,
											'LBG' => 228,
											'LBB' => 230,
											'LR' => 0,
											'LG' => 0,
											'LB' => 0);
										
		// Get the custom settings
		if(is_array($config))
		{
			foreach ($config as $key => $val)
				$settings[$key] = $val;
		}	

		$DataSet = new pData;
		$DataSet->AddPoint($y,"Serie1");  
		$DataSet->AddPoint($x,"Serie2");  
		$DataSet->AddAllSeries();  
		$DataSet->RemoveSerie("Serie2");  
		$DataSet->SetAbsciseLabelSerie("Serie2");  
		$DataSet->SetSerieName($settings['Legend'],"Serie1");  
		$DataSet->SetYAxisName($settings['Ylabel']);  
		$DataSet->SetXAxisName($settings['Xlabel']);  

		$DataSet->SetXAxisFormat($settings['XAxisFormat']);
		if(strlen($settings['XAxisUnit'])){ $DataSet->SetXAxisUnit($settings['XAxisUnit']);}
		if(strlen($settings['YAxisUnit'])){ $DataSet->SetYAxisUnit($settings['YAxisUnit']);}
		$DataSet->SetYAxisFormat($settings['YAxisFormat']);
		// Initialise the graph  
		$Test = new pChart($w,$h);  
		$Test->drawGraphAreaGradient($settings['ImgR'],$settings['ImgG'],$settings['ImgB'],$settings['Decay'],TARGET_BACKGROUND);  
		$FontSize  = $settings['FontSize'];
		$FontName  = $this->_ext_path."/fonts/".$settings['FontName'];
		$Test->setFontProperties($FontName,$FontSize);  
		//Calc Textbox Height
		if(strlen($settings['Textbox']))
		{
			$TextboxFontSize  = $settings['TextboxFontSize'];
			$TextboxFontName   = $this->_ext_path."/fonts/".$settings['TextboxFontName'];
			$Position   = imageftbbox($TextboxFontSize,0,$TextboxFontName,$settings['Textbox']); 
			$TextboxHeight = $Position[1]-$Position[7] + 8;
		}
		else
			$TextboxHeight = 0;
		// Maximize The graph area
		//on Y axis
		if($settings['XAxisFormat']=='time')
		{
			$xdata="99:99:99";
			$Position   = imageftbbox($FontSize,0,$FontName,$xdata); 
			$WXmax  = $Position[2]-$Position[0];
      $TextHeightX = $Position[1]-$Position[7];
		}
		elseif($settings['XAxisFormat']=='date')
		{
			$xdata="99/99/9999";
			$Position   = imageftbbox($FontSize,0,$FontName,$xdata); 
			$WXmax  = $Position[2]-$Position[0];
      $TextHeightX = $Position[1]-$Position[7];
		}
		else //number
		{
			$WXmax = 0;
			foreach($x as $xdata)
			{
				$xdata .=$settings['XAxisUnit'];
				$Position   = imageftbbox($FontSize,0,$FontName,$xdata); 
				$TextWidth  = $Position[2]-$Position[0];
	      $TextHeightX = $Position[1]-$Position[7];
		    $WXmax = ($TextWidth > $WXmax) ? $TextWidth : $WXmax; 
			}
		}	
		if($settings['XAngle']>0) //Calc projection of x labels
		{
			$sin = abs(sin(deg2rad($settings['XAngle']))); 
			$cos = abs(cos(deg2rad($settings['XAngle']))); 
			$HXmax = ($WXmax*$sin)+($TextHeightX*$cos);
		}
		else
			$HXmax = $TextHeightX;
		
		//on Y axis...
		if($settings['YAxisFormat']=='time')
		{
			$ydata="99:99:99";
			$Position   = imageftbbox($FontSize,0,$FontName,$ydata); 
			$WYmax  = $Position[2]-$Position[0];
      $TextHeightY = $Position[1]-$Position[7];
		}
		elseif($settings['YAxisFormat']=='date')
		{
			$ydata="99/99/9999";
			$Position   = imageftbbox($FontSize,0,$FontName,$ydata); 
			$WYmax  = $Position[2]-$Position[0];
      $TextHeightY = $Position[1]-$Position[7];
		}
		else //number
		{
			$WYmax = 0;
			foreach($y as $ydata)
			{
				$ydata .=$settings['YAxisUnit'];
				//echo $ydata."<br>";
				$Position   = imageftbbox($FontSize,0,$FontName,$ydata); 
				$TextWidth  = $Position[2]-$Position[0];
	      $TextHeightY = $Position[1]-$Position[7];
				$WYmax = ($TextWidth > $WYmax) ? $TextWidth : $WYmax; 
			}
		}	

		$Test->setGraphArea($WYmax+$TextHeightY+35,20,$w-25,$h-$HXmax-$TextHeightX-$TextboxHeight-20);  
		//$Test->setGraphArea(60,20,$w-25,($settings['XAngle']==0)?$h-70:$h-100);  
		$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_ADDALL,213,217,221,TRUE,$settings['XAngle'],0,TRUE);  
		$Test->drawGraphAreaGradient($settings['BGR'],$settings['BGG'],$settings['BGB'],$settings['Decay2']);  
		$Test->drawGrid(4,TRUE,230,230,230,20);  

		// This will put the picture "logo.png" with transparency
		if(strlen($settings['Logo']))
		{
			$XLogo = $WYmax+$TextHeightY+35+$settings['XLogo'];
			$YLogo = 20+$settings['XLogo'];
			$logo=$settings['Logo'];
			//Fing extension of logo : png,gif or jpg
			if($this->_findexts($logo)=="png")
			{
				echo "png!";
				$Test->drawFromPNG($logo,$XLogo,$YLogo,$settings['LogoTransparency']);  
			}	
			elseif	($this->_findexts($logo)=="gif")
			{
				echo "gif!";
				$Test->drawFromGIF($logo,$XLogo,$YLogo,$settings['LogoTransparency']);  
			 }	
			elseif	($this->_findexts($logo)=="jpg")
			{
				echo "jpg";
				$Test->drawFromJPG($logo,$XLogo,$YLogo,$settings['LogoTransparency']);  
			}	
		}
		
		$Test->setColorPalette(0,$settings['DataR'],$settings['DataG'],$settings['DataB']);  
		if($type == "bar") // Draw the bar chart  
			$Test->drawStackedBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),70);  
		elseif($type == "line") // Draw the line chart  
		{
			$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());   
			$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,0,-1,-1,-1,TRUE);
		}
		elseif($type == "cubic") // Draw the line chart  
		{ 
			$Test->setShadowProperties(3,3,0,0,0,30,4);
			$Test->drawCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription());
			$Test->clearShadow();
			if($settings['Filled'] == 'yes')
				 $Test->drawFilledCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription(),.1,30);
			$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,0,-1,-1,-1,TRUE);
		}
		// Draw the textbox  
		if(strlen($settings['Textbox']))
		{
			$Test->setFontProperties($TextboxFontName,$TextboxFontSize);  
			$Test->drawTextBox(0,$h-$TextboxHeight,$w,$h,$settings['Textbox'],0,255,255,255,ALIGN_CENTER,TRUE,0,0,0,30);  
		}	
		// Draw the legend  
		if(strlen($settings['Legend']))
		{
			$LegendFontSize  = $settings['LegendFontSize'];
			$LegendFontName  = $this->_ext_path."/fonts/".$settings['LegendFontName'];
			$Position = imageftbbox($LegendFontSize,0,$LegendFontName,$settings['Legend']); 
			$LegendW  = $Position[2]-$Position[0]+40;
			$Test->setFontProperties($LegendFontName,$LegendFontSize);  
			$Test->drawLegend($w-$LegendW,10,$DataSet->GetDataDescription(),$settings['LBR'],
				$settings['LBG'],$settings['LBB'],52,58,82,$settings['LR'],$settings['LG'],$settings['LB'],TRUE);  
		}
		// Render the picture  
		$Test->addBorder(2);  
		if(strlen($imgname)) //custom image name
			$imgname = $this->_img_path."/".$imgname;  
		else //random image name
		{
			$this->obj->load->helper('string');
			$imgname = $this->_img_path."/$type-".random_string('alnum', 16).".png";  
		}	
			
		$Test->Render($imgname);  
		return  array("name" => '/'.$imgname,"w" => $w+4, "h" => $h+4);
	}

	private function _findexts ($filename)
	{
		$filename = strtolower($filename) ;
		$exts = split("[/\\.]", $filename) ;
		$n = count($exts)-1;
		$exts = $exts[$n];
		return $exts;
	} 
}
// END CI_Charts class

/* End of file Charts.php */
/* Location: ./system/application/libraries/Charts.php */
