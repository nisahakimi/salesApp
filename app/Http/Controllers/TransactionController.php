<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $transactions = Transaction::all();
        return response()->json($transactions);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        //
        $request->validate([
            'product_id' => 'required|exists:products,id',

            'quantity' => 'required|integer|min:1',
        ]);

        $totalPrice = $product->price * $request->quantity;
        // Retrieve product and check stock
        $product = Product::find($request->product_id);
        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Not enough stock'], 400);
        }

        // Create the transaction
        $transaction = Transaction::create([
            'id' => Str::uuid(),
            'product_id' => $request->product_id,
            'user_id' => $user->id,
            'total_price' => $totalPrice,
            'quantity' => $request->quantity,
            'status' => 'pending',
        ]);

        // Update product stock
        $product->stock -= $request->quantity;
        $product->save();

        return response()->json($transaction, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:pending,completed,refunded',
        ]);

        // Find the transaction
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Check if the transaction is already completed
        if ($transaction->status === 'completed') {
            return response()->json(['message' => 'Completed transactions cannot be updated'], 400);
        }

        $product = Product::find($transaction->product_id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Update quantity and recalculate total price if quantity changes
        if ($request->has('quantity')) {
            $newQuantity = $request->quantity;

            // Calculate stock adjustment
            $stockAdjustment = $newQuantity - $transaction->quantity;

            if ($product->stock < $stockAdjustment) {
                return response()->json(['message' => 'Not enough stock available'], 400);
            }

            // Update product stock
            $product->stock -= $stockAdjustment;
            $product->save();

            // Update transaction quantity and total price
            $transaction->quantity = $newQuantity;
            $transaction->total_price = $product->price * $newQuantity;
        }

        // Handle status update
        if ($request->has('status')) {
            $transaction->status = $request->status;
        }

        // Save transaction changes
        $transaction->save();

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction,
        ], 200);
    }

    public function refund($id)
    {
        // Retrieve the transaction
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found',
            ], 404);
        }

        // Check if the transaction is completed
        if ($transaction->status == 'completed') {
            return response()->json([
                'message' => 'Only not completed transactions can be refunded',
            ], 400);
        }

        // Update product stock
        $product = Product::find($transaction->product_id);

        if ($product) {
            $product->stock += $transaction->quantity; // Increment stock
            $product->save();
        } else {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        // Update transaction status
        $transaction->status = 'refunded';
        $transaction->save();

        return response()->json([
            'message' => 'Transaction refunded successfully',
            'transaction' => $transaction,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Check if the transaction is completed
        if ($transaction->status === 'completed') {
            return response()->json(['message' => 'Completed transactions cannot be deleted'], 400);
        }

        // Retrieve the associated product
        $product = Product::find($transaction->product_id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Adjust the product stock
        $product->stock += $transaction->quantity;
        $product->save();

        // Delete the transaction
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully'], 200);
    }

}
