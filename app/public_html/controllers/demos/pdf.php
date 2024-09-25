<?php
	class demos_pdf_controller extends Banshee\controller {
		public function execute() {
			if (library_exists("tcpdf")) {
				$pdf = new TCPDF;
				$library = "TCPDF";
			} else {
				$pdf = new FPDF;
				$library = "FPDF";
			}

			$pdf->AddPage();
			$pdf->SetFont("helvetica", "B", 16);
			$pdf->Cell(0, 10, $library." demo", 0, 1);
			$pdf->Ln();

			$pdf->SetFont("helvetica", "", 12);
			$pdf->SetFillColor(192, 192, 192);
			$pdf->Cell(40, 10, "Back", 1, 0, "C", 1);
			$pdf->Link(10, 30, 40, 10, $_SERVER["HTTP_SCHEME"]."://".$_SERVER["HTTP_HOST"]."/demos");

			$this->view->disable();
			$pdf->Output();
		}
	}
?>
