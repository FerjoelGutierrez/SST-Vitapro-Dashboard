<?php
require_once 'conexion.php';
// Asegúrate de tener fpdf.php en tu carpeta api, si no, descárgalo de fpdf.org
require('fpdf/fpdf.php'); // O ajusta la ruta si lo tienes en otro lado

if (!isset($_GET['id'])) die("ID no especificado");
$id = $_GET['id'];

// Obtener datos
$stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$r) die("Reporte no encontrado");

class PDF extends FPDF {
    function Header() {
        // Logo (Ajusta la ruta de tu logo si tienes uno)
        // $this->Image('logo.png',10,6,30);
        $this->SetFont('Arial','B',15);
        $this->Cell(0,10,utf8_decode('REPORTE DE INCIDENTE SST - VITAPRO'),0,1,'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Detalles del Reporte
$pdf->SetFillColor(230,230,230);
$pdf->Cell(0,10, utf8_decode('DETALLES DEL HALLAZGO #' . $r['id']), 1, 1, 'C', true);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(40,10,'Fecha y Hora:',1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,10,$r['fecha'],1,1);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(40,10,'Reportante:',1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,10,utf8_decode($r['nombre']),1,1);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(40,10,'Tipo:',1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,10,utf8_decode($r['tipo_hallazgo']),1,1);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(40,10,'Nivel Riesgo:',1);
$pdf->SetFont('Arial','B',10);
if($r['nivel_riesgo'] == 'Alto') $pdf->SetTextColor(255,0,0);
$pdf->Cell(0,10,utf8_decode($r['nivel_riesgo']),1,1);
$pdf->SetTextColor(0,0,0); // Reset color

$pdf->SetFont('Arial','B',10);
$pdf->Cell(40,10,'Aviso SAP:',1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,10,utf8_decode($r['aviso_sap'] ? $r['aviso_sap'] : 'N/A'),1,1);

$pdf->Ln(5);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,10,utf8_decode('DESCRIPCIÓN:'),0,1);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,5,utf8_decode($r['descripcion_breve']));

$pdf->Ln(10);

// IMAGEN
if ($r['foto_path'] && file_exists('../' . $r['foto_path'])) {
    $pdf->Cell(0,10,'EVIDENCIA FOTOGRAFICA:',0,1);
    // Ajustar imagen para que quepa (máximo 100mm de alto)
    $pdf->Image('../' . $r['foto_path'], null, null, 0, 100); 
} else {
    $pdf->Cell(0,10,'(No hay imagen disponible o el archivo no se encuentra)',0,1);
}

$pdf->Output('I', 'Reporte_SST_'.$id.'.pdf');
?>
