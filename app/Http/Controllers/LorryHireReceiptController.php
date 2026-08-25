<?php

namespace App\Http\Controllers;

use App\Models\LorryHire;
use Barryvdh\DomPDF\Facade\Pdf;

class LorryHireReceiptController extends Controller
{
    public function __invoke(LorryHire $hire)
    {
        $hire->load('lorry');

        $pdf = Pdf::loadView('pdf.lorry-hire-receipt', ['hire' => $hire]);

        return $pdf->stream("hire-receipt-{$hire->id}.pdf");
    }
}
