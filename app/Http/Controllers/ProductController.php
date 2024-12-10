<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $products = Product::all();
        return response()->json($products);
        // return view('product.index', ['products' => $products]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $products = Product::all();
        // return view('product.create', ['products' => $products]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        try {
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
            ]);

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product,
            ], 201);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Add data product failed!',
                'error' => $e->getMessage(),
            ], 409);
        }
        // return redirect()->route('produks.index')->with('pesan',"Penambahan data {$validateData['nama_produk']} berhasil");
        // return redirect()->route('products.index')->with('pesan', "Penambahan data {$request->name} berhasil");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //

        // $product = Product::find($id);
        // return view('product.edit', ['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id)
    {
        //
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404); // 404 status code for not found
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
           'stock' => $request->stock,
        ]);


        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $product = Product::find($id);

        // Check if the product exists
        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        // delete if found
        $productName = $product->name;
        $product->delete();
        return response()->json([
            'message' => "Product '$productName' deleted successfully",
            'product' => $productName,
        ], 200);
    }
}
