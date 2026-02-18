<?php
function simple_pdf_output(string $title, array $lines, string $filename): void
{
    $content = "BT\n/F1 12 Tf\n50 800 Td\n";
    $content .= '(' . pdf_escape($title) . ") Tj\n";
    $yShift = -20;
    foreach ($lines as $line) {
        $content .= "0 {$yShift} Td\n(" . pdf_escape($line) . ") Tj\n";
        $yShift = -16;
    }
    $content .= "ET";

    $objects = [];
    $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
    $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
    $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj";
    $objects[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";
    $objects[] = "5 0 obj << /Length " . strlen($content) . " >> stream\n{$content}\nendstream endobj";

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $obj) {
        $offsets[] = strlen($pdf);
        $pdf .= $obj . "\n";
    }

    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $pdf;
    exit;
}

function pdf_escape(string $text): string
{
    $text = iconv('UTF-8', 'windows-1252//TRANSLIT', $text) ?: $text;
    $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    return $text;
}
