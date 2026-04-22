<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Response;

class ReportViewController extends Controller
{
    /**
     * Generate and stream a PDF report based on data stored in the session.
     *
     * @param Request $request
     * @return Response
     */
    public function view(Request $request): Response
    {
        if (!Session::has('pdf_report_data')) {
            abort(404, 'Nenhum relatório para visualizar.');
        }

        // Retrieve data and immediately clear it to prevent reuse
        $pdfReportData = Session::pull('pdf_report_data');

        $view = $pdfReportData['view'];
        $data = $pdfReportData['data'];
        $filename = $pdfReportData['filename'] ?? 'relatorio.pdf';

        // Some report data structures might have filters at the top level
        if (isset($pdfReportData['filters'])) {
            $data['filters'] = $pdfReportData['filters'];
        }

        $pdf = Pdf::loadView($view, $data);
        
        // Use output() to get the PDF content and return it in a response
        // with the 'inline' disposition to open it in the browser.
        return new Response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
