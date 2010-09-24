<?php

class GraphImage
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

	// axis information
	private $beginYear = 2010;
	private $axisInfoArray;
	
	
	// colors
	private $backgroundColor;
	private $borderColor;
	private $lineColor;
	private $textColor;
	
	private $residentsColor;
	private $workersColor;

	
	public function __construct()
	{
	}
	
	public function MakeGraph($width, $height)
	{
		$this->image = imagecreate($width, $height);
		
		$this->SetSize($width, $height); 		
		$this->SetColors();  // change colors here
		$this->SetPadding(0.07, 0.13);	// parts of whole
		
		$this->SetDivisionCount(5);
		$this->SetMarkers($this->divisionCount); 
		$this->SetMarkerLength(10);
		$this->SetAxisInformation();
	
		$this->DrawStuff();
		$this->DrawGraphLines();
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
		$this->backgroundColor = imagecolorallocatealpha($this->image, 0, 0, 0 , 127); //transparant
		$this->borderColor = imagecolorallocate($this->image, 0, 0, 0);
		$this->lineColor = imagecolorallocatealpha($this->image, 100, 100, 100, 20); //grayish
		$this->textColor = imagecolorallocate($this->image, 100, 100, 100);
		$this->residentsColor = imagecolorallocate($this->image, 230, 172, 173); 
		$this->workersColor = imagecolorallocate($this->image, 201, 165, 196);
	}
	
	// width and height parts are in pieces of 1, like 0.03 
	public function SetPadding($widthPart, $heightPart)
	{
		$this->sidePadding = $this->width*$widthPart;
		$this->heightPadding = $this->height*$heightPart;
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
			//echo " - ".$i ." :". $arr[$i];
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
	
	private function DrawStuff()
	{
		$this->DrawBackground();
		$this->DrawBorder();
		// $this->DrawTestLine();
		
		$this->DrawAxis();
		$this->DrawMarkers();
		$this->DrawAxisInformation();
		
		// $this->DrawTestText();
	}
	
	private function DrawGraphLines()
	{
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
		imageline($this->image, $x1, $y1, $x2, $y2, $this->lineColor);
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
		
		$this->DrawLine($xPlacing, $this->heightPadding, $xPlacing, $this->height - $this->heightPadding);
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
	// This places all information on the image
	private function DrawAxisInformation()
	{
		//Draw the element in the axisInfoArray at each xPosition in markersArray
		for($i = 0; $i < $this->divisionCount + 1; $i++)
		{
			$this->DrawText($this->axisInfoArray[$i], $this->markersArray[$i], $this->height - 10); //magic number, temp
		}
	}
	
	private function DrawText($text, $xPosition, $yPosition)
	{
		$font = $_SERVER['DOCUMENT_ROOT'].'/fonts/corbel.ttf';
		$fontSize = 12.0;
		$angle = 0.0;
		imagettftext($this->image, $fontSize, $angle, $xPosition, $yPosition, $this->textColor, $font, $text);
	}
		
	// other
	
	public function GetImage()
	{
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
	
	private function DrawTestText()
	{
		$text = "Testin yoooo";
		$font = $_SERVER['DOCUMENT_ROOT'].'/fonts/corbel.ttf';
		imagettftext($this->image, 12.0, 0.0, 50, 50, $this->textColor, $font, $text);
	}

}


?>