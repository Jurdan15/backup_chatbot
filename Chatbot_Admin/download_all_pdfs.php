<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require('fpdf/fpdf.php');

// --- Generate PDFs ---
function generate_pdf($filename, $content) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',12);
    $pdf->MultiCell(0,10, $content);
    $pdf->Output('F', $filename);
}

// Example content - replace with your real data
generate_pdf("generated_nlu.pdf", "This is your NLU data here...");
generate_pdf("generated_domain.pdf", "This is your DOMAIN data here...");
generate_pdf("generated_rules.pdf", "This is your RULES data here...");

// --- Zip files ---
$zip = new ZipArchive();
$zipName = 'rasa_files.zip';
$files = ['generated_nlu.pdf', 'generated_domain.pdf', 'generated_rules.pdf'];

if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    foreach ($files as $file) {
        if (file_exists($file)) {
            $zip->addFile($file, basename($file)); // make sure only filename inside zip
        }
    }
    $zip->close();

    // --- Download zip ---
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename='.$zipName);
    header('Content-Length: ' . filesize($zipName));
    readfile($zipName);

    // Clean up
    unlink($zipName);
    foreach ($files as $file) {
        unlink($file); // remove individual PDFs
    }
    exit;
} else {
    echo 'Could not create ZIP file.';
}
?>
