<?php

class LineGraph
{
	// attributes
	private $image;
	
	private $width;
	private $height;
	
	// padding stuff
	private $sidePadding;
	private $heightPadding;
	
	// -- AXIS -- 
	
	// axis markers
	private $markersArray;
	private $markerLength;
	private $divisionCount;
	private static $H_OFFSET = 15;
	private static $V_OFFSET = 3;
	
	// axis information
	private $beginYear = 2010;
	private $axisInfoArray;
	
	// --- GRAPH --
	private $inputArray1;
	private $inputArray1Min;
	private $inputArray1Max;
	
	private $inputArray2;
	private $inputArray2Min;
	private $inputArray2Max;
	private $lineThickness = 2;
	
	// colors
	private $backgroundColor;
	private $borderColor;
	private $lineColor;
	private $textColor;
	
	private $workingGraphLineColor1;
	private $workingGraphLineColor2;
	// space colors
	private $residentsColor;
	private $workersColor;
	// mobility colors
	private $networkValueColor;
	private $travelersColor;
	
	// fonts
	private static $FONT = 'verdana.ttf';
	private static $FONT_SIZE = 14;
	
	public function __construct($width, $height)
	{
		$this->image = imagecreatetruecolor($width, $height);
		imageantialias($this->image, true);
		
		$this->SetSize($width, $height); 		
		$this->SetColors();  // change colors here
		// $this->SetPaddingParts(0.07, 0.13);	// parts of whole
		$this->SetPaddingFixed(75,30);
		
		$this->SetDivisionCount(5);
		$this->SetMarkers($this->divisionCount); 
		$this->SetMarkerLength(5);
		$this->SetAxisInformation();
	}
	
	// set-methods
	public function SetSize($width, $height)
	{
		$this->width = $width;
		$this->height = $height;
	}
	
	// change colors here
	private function SetColors()
	{
		//$this->backgroundColor = imagecolorallocatealpha($this->image, 0, 0, 0 , 127); //transparant
		$this->backgroundColor = imagecolorallocate($this->image, 238, 244, 247);
		$this->borderColor = imagecolorallocate($this->image, 0, 0, 0);
		$this->lineColor = imagecolorallocatealpha($this->image, 50, 50, 50, 20); //grayish
		$this->textColor = imagecolorallocate($this->image, 0, 0, 0);
		
		// colors are colorpicked from flash
		$this->residentsColor = imagecolorallocate($this->image, 216, 93, 93); 
		$this->workersColor = imagecolorallocate($this->image, 187, 136, 177);
		$this->networkValueColor = imagecolorallocate($this->image, 107, 111, 112);
		$this->travelersColor = imagecolorallocate($this->image, 175, 177, 178);
		
		$this->SetToSpaceColors(); // default
	}
	
	public function SetToSpaceColors()
	{
		$this->workingGraphLineColor1 = $this->residentsColor;
		$this->workingGraphLineColor2 = $this->workersColor;
	}
	
	// uses mobility colors instead
	public function SetToMobilityColors()
	{
		$this->workingGraphLineColor1 = $this->networkValueColor;
		$this->workingGraphLineColor2 = $this->travelersColor;
	}
	
	// width and height parts are in pieces of 1, like 0.03 
	public function SetPaddingParts($widthPart, $heightPart)
	{
		$this->sidePadding = $this->width*$widthPart;
		$this->heightPadding = $this->height*$heightPart;
	}
	
	public function SetPaddingFixed($sidePadding, $heightPadding)
	{
		$this->sidePadding = $sidePadding;
		$this->heightPadding = $heightPadding;
	}
	
	public function SetDivisionCount($count)
	{
		$this->divisionCount = $count;
	}
	
	// Fills the markersArray with xPositions according to the divisionCount
	private function SetMarkers($divisionCount)
	{
		$this->markersArray = array();
		$graphWidth = $this->width - 2*$this->sidePadding;
		$divisionWidth = $graphWidth / $divisionCount; //This is a float
		
		// We will now fill the markersArray with xPositions
		for($i = 0; $i < $divisionCount + 1 ; $i++) //+1 because we need 6 lines
		{
			$this->markersArray[$i] = $this->sidePadding + $i*$divisionWidth;
		}
	}
	
	public function SetMarkerLength($length)
	{
		$this->markerLength = $length;
	}
	
	// Sets the information that needs to be displayed
	// in array
	private function SetAxisInformation()
	{
		$this->axisInfoArray = array();
		
		for($i = 0; $i < $this->divisionCount + 1; $i++) //+1 because we need 6 lines
		{
			$this->axisInfoArray[$i] = $this->beginYear + 4*$i;
		}
	}
	
	public function SetInputArray($inputArray, $min, $max)
	{
		if($this->inputArray1 == NULL)
		{
			$this->inputArray1 = $inputArray;
			$this->inputArray1Min = $min;
			$this->inputArray1Max = $max;
		}
		elseif ($this->inputArray2 == NULL)
		{
			$this->inputArray2 = $inputArray;
			$this->inputArray2Min = $min;
			$this->inputArray2Max = $max;
		}
		else
			echo 'ERROR: max is two inputs';
	}
	

	
	private function DrawStuff()
	{
		$this->DrawBackground();
		//$this->DrawBorder();		
		$this->DrawAxis();
		$this->DrawMarkers();
		$this->DrawAxisInformation();
	}
	
	// PRE: inputArray1 must be set
	private function DrawGraph()
	{
		if($this->inputArray1 != NULL)
		{
			$min = $this->inputArray1Min;
			$max = $this->inputArray1Max;
			
			$this->DrawGraphLines($this->inputArray1, $min, $max);
		}
		if($this->inputArray2 != NULL)
		{
			$min = $this->inputArray2Min;
			$max = $this->inputArray2Max;
			
			$this->DrawGraphLines($this->inputArray2, $min, $max);
		}
		else
		{
			$text = '';
			
			$this->DrawText($text,
				$this->sidePadding + 10,
				0.5*$this->height,
				$this->textColor);
		}
	}
	
	private function DrawVerticalAxisInformation($isArray2, $min, $max)
	{
		if(!$isArray2)
		{
			$textColor = $this->workingGraphLineColor1;
			$xPosition = 0.2 * $this->sidePadding;
		}
		else
		{
			$textColor = $this->workingGraphLineColor2;
			$xPosition = $this->width - (0.8 * $this->sidePadding);
		}
		
		
		$yMin = $this->height - $this->heightPadding;
		$yMax = $this->heightPadding;
	
		$this->DrawText($min, $xPosition, $yMin, $textColor);
		$this->DrawText($max, $xPosition, $yMax, $textColor);
	}
	
	// Here we actually draw graph lines based on an input array
	// and a specified color.
	// PRE: Arraylength must be more than 1
	private function DrawGraphLines($inputArray, $min, $max)
	{
		$length = count($inputArray);
		if($length > 1)
		{
			if($inputArray == $this->inputArray1)
				$lineColor = $this->workingGraphLineColor1;
			elseif($inputArray == $this->inputArray2)
				$lineColor = $this->workingGraphLineColor2;
			
			//normalize all entries in inputArray
			for($i = 0; $i < $length ; $i++)
			{
				$inputArray[$i] = $this->Normalize($inputArray[$i], $min, $max);
			}	
			
			$topEnd = $this->heightPadding;
			$bottomEnd = $this->height - $this->heightPadding;
			$verticalAxisLength = $bottomEnd - $topEnd;
			
			for($i = 1; $i < $length; $i++)
			{
				$y1 = $inputArray[$i - 1]*$verticalAxisLength;
				$correctedY1 = $bottomEnd - $y1;
				
				$y2 = $inputArray[$i]*$verticalAxisLength;
				$correctedY2 = $bottomEnd - $y2;
				
				
				$this->DrawGraphLine(
					$this->markersArray[$i - 1],
					$correctedY1,
					$this->markersArray[$i],
					$correctedY2, 
					$lineColor);
				
			}
			
		}
	}
	
	private function Normalize($value, $min, $max)
	{
		$difference = $max - $min;
		if ($difference != 0)
			$part = ($value - $min) / $difference;
		else
			$part = 0;
		return $part;
	}
		
	private function DrawGraphLine($x1, $y1, $x2, $y2, $lineColor)
	{	
		$this->Imagelinethick($this->image, $x1, $y1, $x2, $y2, $lineColor, $this->lineThickness);
	}

	// code from official PHP Manual
	private function Imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
	{
		/* this way it works well only for orthogonal lines
		imagesetthickness($image, $thick);
		return imageline($image, $x1, $y1, $x2, $y2, $color);
		*/
		if ($thick == 1) {
			return imageline($image, $x1, $y1, $x2, $y2, $color);
		}
		$t = $thick / 2 - 0.5;
		if ($x1 == $x2 || $y1 == $y2) {
			return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
		}
		$k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
		$a = $t / sqrt(1 + pow($k, 2));
		$points = array(
			round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
			round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
			round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
			round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
		);
		imagefilledpolygon($image, $points, 4, $color);
		return imagepolygon($image, $points, 4, $color);
	}

	
	private function DrawBackground()
	{
		imagefilledrectangle($this->image, 
			0,0, $this->width -1, $this->height - 1, 
			$this->backgroundColor);
	}
	
	private function DrawBorder()
	{
		imagerectangle($this->image, 
			0,0, $this->width - 1, $this->height - 1, 
			$this->borderColor);
	}
	
	private function DrawLine($x1, $y1, $x2, $y2)
	{
		$this->Imagelinethick($this->image, $x1, $y1, $x2, $y2, $this->lineColor, 2);
	}
	
	private function DrawAxis()
	{
		$this->DrawHorizontalAxis();
		$this->DrawVerticalAxis();
	}
	
	private function DrawHorizontalAxis()
	{
		$yPlacing = $this->height - $this->heightPadding;
		
		$this->DrawLine($this->sidePadding, $yPlacing, 
			$this->width - $this->sidePadding, $yPlacing);
		
	}
	
	private function DrawVerticalAxis()
	{
		$xPlacing = $this->sidePadding;
		
		$this->DrawLine($xPlacing, $this->heightPadding, 
			$xPlacing, $this->height - $this->heightPadding);
		
		if($this->inputArray2 != NULL)
		{
			$xPlacing = $this->width - $this->sidePadding;
			$this->DrawLine($xPlacing, $this->heightPadding, 
				$xPlacing, $this->height - $this->heightPadding);
		}
	}
	
	private function DrawMarkers()
	{
		$arr = $this->markersArray;
		$halfLength = $this->markerLength * 0.5;
		$graphBottom = $this->height - $this->heightPadding;
		
		foreach ($arr as $xPosition)
		{
			$this->DrawLine($xPosition, $graphBottom - $halfLength,
				$xPosition, $graphBottom + $halfLength);
		}
	}
	
	// PRE: Need divisionCount
	// This places some information on the image
	private function DrawAxisInformation()
	{
		//Draw the element in the axisInfoArray at each xPosition in markersArray
		for($i = 0; $i < $this->divisionCount + 1; $i++)
		{
			$this->DrawText($this->axisInfoArray[$i], 
				$this->markersArray[$i] - LineGraph::$H_OFFSET, 
				$this->height - LineGraph::$V_OFFSET,
				$this->textColor);
		}
		
		if($this->inputArray1 != NULL)
		{
			$min = $this->inputArray1Min;
			$max = $this->inputArray1Max;
			$this->DrawVerticalAxisInformation(false, $min, $max);
		}
		if($this->inputArray2 != NULL)
		{
			$min = $this->inputArray2Min;
			$max = $this->inputArray2Max;
			$this->DrawVerticalAxisInformation(true, $min, $max);
		}
	}
	
	private function DrawText($text, $xPosition, $yPosition, $textColor)
	{
		$font = $_SERVER['DOCUMENT_ROOT'].'/SprintStad/fonts/'.LineGraph::$FONT;
		$fontSize = LineGraph::$FONT_SIZE;
		$angle = 0.0;
		imagettftext($this->image, $fontSize, $angle, $xPosition, $yPosition, $textColor, $font, $text);
	}
	
	// other
	public function GetImage()
	{
		$this->DrawStuff();
		$this->DrawGraph();
		
		return $this->image;
	}
	

	
	// TEST METHODS
	private function DrawTestLine()
	{
		$padding = 5;
		imageline($this->image, 
			$padding, $padding, $this->width - $padding, $this->height - $padding, 
			$this->lineColor);
	}
}
?>