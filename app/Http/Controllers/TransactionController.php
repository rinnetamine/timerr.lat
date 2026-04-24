<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;

class TransactionController extends Controller
{
    /**
     * export transactions
     */
    public function exportPdf(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $transactions = $user->transactions()->latest()->get();


        return response()->view('transactions.pdf', [
            'user' => $user,
            'transactions' => $transactions,
            'pdf_missing' => true
        ]);
    }

    public function download(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $transactions = $user->transactions()->latest()->get();

        $filename = 'transakcijas-' . $user->id . '-' . now()->format('Ymd') . '.pdf';

        // configure domPDF options
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        
        // create new domPDF instance
        $dompdf = new Dompdf($options);
        
        // load HTML content
        $html = view('transactions.pdf', [
            'user' => $user,
            'transactions' => $transactions
        ])->render();
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return Response::make($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    public function exportCsv(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $transactions = $user->transactions()->latest()->get();

        $filename = 'transakcijas-' . $user->id . '-' . now()->format('Ymd') . '.csv';

        // Create CSV content
        $csvContent = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel compatibility
        $csvContent .= "Datums,Pakalpojuma nosaukums,Partneris,Statuss,Ilgums\n";

        foreach ($transactions as $transaction) {
            $date = $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i') : '';
            $description = $transaction->description;
            $partner = $transaction->amount > 0 ? 'Sa\u0146\u0113m\u0113js' : 'Sniedz\u0113js';
            $status = 'Pabeigta';
            $duration = abs($transaction->amount) . 'h';
            
            // Escape commas and quotes in CSV fields
            $csvContent .= '"' . $date . '","' . 
                          str_replace('"', '""', $description) . '","' . 
                          $partner . '","' . 
                          $status . '","' . 
                          $duration . '"' . "\n";
        }

        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    public function exportExcel(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $transactions = $user->transactions()->latest()->get();

        $filename = 'transakcijas-' . $user->id . '-' . now()->format('Ymd') . '.xlsx';

        // Create HTML content for Excel (Excel can open HTML tables)
        $htmlContent = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transakciju v\u0113sture - ' . $user->first_name . ' ' . $user->last_name . '</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .positive { color: green; }
        .negative { color: red; }
    </style>
</head>
<body>
    <h2>Transakciju v\u0113sture</h2>
    <p>Lietot\u0101js: ' . $user->first_name . ' ' . $user->last_name . '</p>
    <p>E-pasts: ' . $user->email . '</p>
    <p>Izveidots: ' . now()->format('Y-m-d H:i:s') . '</p>
    
    <table>
        <thead>
            <tr>
                <th>Datums</th>
                <th>Pakalpojuma nosaukums</th>
                <th>Partneris</th>
                <th>Statuss</th>
                <th>Ilgums</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($transactions as $transaction) {
            $date = $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i') : '';
            $description = htmlspecialchars($transaction->description);
            $partner = $transaction->amount > 0 ? 'Sa\u0146\u0113m\u0113js' : 'Sniedz\u0113js';
            $status = 'Pabeigta';
            $duration = abs($transaction->amount) . 'h';
            
            $htmlContent .= '<tr>
                <td>' . $date . '</td>
                <td>' . $description . '</td>
                <td>' . $partner . '</td>
                <td>' . $status . '</td>
                <td>' . $duration . '</td>
            </tr>';
        }

        $htmlContent .= '</tbody>
    </table>
</body>
</html>';

        return Response::make($htmlContent, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}
