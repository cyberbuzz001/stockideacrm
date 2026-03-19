<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

class DocumentWatermarkService
{
    public function watermarkPdf(string $sourcePath, string $destPath, string $watermarkText): void
    {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($sourcePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            $pdf->SetFont('Helvetica', 'B', 12);
            $pdf->SetTextColor(190, 190, 190);
            $pdf->SetXY(10, $size['height'] - 15);
            $pdf->Cell(0, 10, $watermarkText);
        }

        $pdf->Output($destPath, 'F');
    }
}
