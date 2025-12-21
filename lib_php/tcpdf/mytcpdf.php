<?php
class MyTCPDF extends TCPDF {
	var $htmlHeader;
	var $htmlFooter;
	var $img_file='../misc/white.jpg';
	public function setHtmlHeader($htmlHeader) {
		$this->htmlHeader = $htmlHeader;
	}
	public function setHtmlFooter($htmlFooter) {
		$this->htmlFooter = $htmlFooter;
	}
	public function setImgBg($img_file) {
		$this->img_file = $img_file;
	}

	public function Header() {
		if(file_exists($this->img_file)){
			$this->SetAutoPageBreak(false, 0);
			$this->Image($this->img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
		}
		$this->SetAutoPageBreak(true, $this->footer_margin);
		$this->writeHTMLCell(
			$w = 0, $h = 0, $x = '', $y = '',
			$this->htmlHeader, $border = 0, $ln = 1, $fill = 0,
			$reseth = true, $align = 'top', $autopadding = true);
		
		
	}
	public function Footer() {
        $this->SetY(-30);
        $this->SetFont('helvetica', 'I', 7);
		$this->writeHTMLCell(
			$w = 0, $h = 0, $x = '', $y = '',
			$this->htmlFooter. '<br />Pag. ' . $this->getAliasNumPage() . ' de ' .
                    $this->getAliasNbPages().'     -      Torresoft DataFEED', $border = 0, $ln = 1, $fill = 0,
			$reseth = true, $align = 'top', $autopadding = true);
    }
}
?>