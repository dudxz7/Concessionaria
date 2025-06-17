<?php
require_once __DIR__ . '/fpdf/fpdf.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Exemplo de PDF gerado via PHP!',0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Ln(10);
$pdf->Cell(0,10,'Este arquivo foi salvo diretamente no disco.',0,1,'C');

// Salva o PDF no disco (na pasta php)
$pdf->Output('F', __DIR__ . '/exemplo_gerado_php.pdf');

echo 'PDF gerado e salvo como exemplo_gerado_php.pdf na pasta php.';
