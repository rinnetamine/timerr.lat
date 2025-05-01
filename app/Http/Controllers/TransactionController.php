<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        // Get the authenticated user's transactions
        $transactions = Auth::user()->transactions()->latest()->paginate(10);
        
        return view('transactions.index', [
            'transactions' => $transactions
        ]);
    }
}
