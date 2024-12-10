<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    //
    public function laporan(Request $request)
    {
        // Ambil parameter tanggal mulai dan tanggal akhir dari request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');


        try {
            if ($startDate) {
                Carbon::parse($startDate); // This will throw an exception if the date is invalid
            }
        } catch (InvalidFormatException $e) {
            return response()->json(['message' => 'Invalid start date format. Use YYYY-MM-DD.'], 400);
        }

        try {
            if ($endDate) {
                Carbon::parse($endDate); // This will throw an exception if the date is invalid
            }
        } catch (\Exception $e) { // Catch any exception, including InvalidFormatException
            return response()->json(['message' => 'Invalid end date format. Use YYYY-MM-DD.'], 400);
        }
        // Query untuk mendapatkan laporan transaksi berdasarkan tanggal
        $transactions = Transaction::query();

        // Jika ada tanggal mulai, filter transaksi setelah tanggal tersebut
        if ($startDate) {
            $transactions->whereDate('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }

        // Jika ada tanggal akhir, filter transaksi sebelum tanggal tersebut
        if ($endDate) {
            $transactions->whereDate('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        // Ambil data transaksi yang sudah difilter
        $transactions = $transactions->get();

        return response()->json($transactions);
    }
}
