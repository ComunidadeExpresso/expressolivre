<?php
/*******************************************************************************
 * FPDF                                                                         *
 *                                                                              *
 * Version: 1.6                                                                 *
 * Date:    2008-08-03                                                          *
 * Author:  Olivier PLATHEY                                                     *
 *******************************************************************************/

require('class.fpdf.inc.php');

class uireports_fpdf extends FPDF
{
	var $a = "1";
	var $b = "1";

	var $widths;
	var $aligns;

	function __construct($orientation = "P") {
		parent::__construct($orientation);
	}
	 
	function SetWidths($w)
	{
		//Set the array of column widths
		$this->widths=$w;
	}

	function SetAligns($a)
	{
		//Set the array of column alignments
		$this->aligns=$a;
	}

	function Row($data)
	{
		//Calculate the height of the row
		$nb=0;
        $data_count = count($data);
		for($i=0;$i< $data_count;++$i)
		$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
		$h=5*$nb;
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		//Draw the cells of the row
        $data_count = count($data);
		for($i=0;$i< $data_count;++$i)
		{
			$w=$this->widths[$i];
			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
			//Save the current position
			$x=$this->GetX();
			$y=$this->GetY();
			//Draw the border
			$this->Rect($x,$y,$w,$h);
			//Print the text
			$this->MultiCell($w,5,$data[$i],0,$a);
			//Put the position to the right of the cell
			$this->SetXY($x+$w,$y);
		}
		//Go to the next line
		$this->Ln($h);
	}

	function CheckPageBreak($h)
	{
		//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger)
		$this->AddPage($this->CurOrientation);
	}

	function NbLines($w,$txt)
	{
		//Computes the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
		$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
		$nb--;
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				++$i;
				$sep=-1;
				$j=$i;
				$l=0;
				++$nl;
				continue;
			}
			if($c==' ')
			$sep=$i;
			$l+=$cw[$c];
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
					++$i;
				}
				else
				$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				++$nl;
			}
			else
			++$i;
		}
		return $nl;
	}

	function Footer()
	{
		// Esta função foi implemantada para realizar o rodapé dos relatórios
		$titulo_system = $GLOBALS['phpgw_info']['apps']['reports']['title'];
		$SubTitulo = $GLOBALS['phpgw_info']['apps']['reports']['subtitle'];

		//Seleciona Arial itálico 8
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		$this->SetTextColor(0,0,100);
		$this->SetFillColor(224,235,255);

		//Imprime o número da página
		$this->Rect(9,281,197,6,'D');
		$this->Cell(55,4,$titulo_system,0,0,'L',1);
		$this->Cell(100,4,$SubTitulo,0,0,'C',1);
		$this->Cell(40,4,'Página n. '.$this->PageNo(),0,1,'R',1);
	}

}
?>
