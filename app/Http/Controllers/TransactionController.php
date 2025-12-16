<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

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

        $filename = 'transactions-' . $user->id . '-' . now()->format('Ymd') . '.pdf';


        $html = view('transactions.pdf', [
            'user' => $user,
            'transactions' => $transactions,
            'pdf_missing' => true
        ])->render();

        return Response::make($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . str_replace('.pdf', '.html', $filename) . '"'
        ]);
    }
}
