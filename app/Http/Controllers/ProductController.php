<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $product =  Product::with('product_variant_prices')->paginate(2);
        $Variant = Variant::with('ProductVariant')->orderBy('id','asc')->get();
        $firstProduct = Product::orderBy('created_at','asc')->first();
        return view('products.index',['is_filtered'=>0,'product'=>$product,'Variant'=>$Variant,'firstProduct'=>$firstProduct]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $title = $request->title;
        $variant = $request->variant;
        $price_from = $request->price_from;
        $price_to = $request->price_to;
        $date = $request->date;
        if(!is_null($price_from) && !is_null($price_to)){
            $products = Product::with(['product_variant_prices' => function ($q) use ($price_from,$price_to) {
            $q->whereBetween('price',  [$price_from,$price_to]);
            },'ProductVariant' => function ($q) use ($variant) {
            $q->where('variant',$variant);
            }])->orWhere('title','LIKE','%'.$title.'%')
            ->paginate(10);

        }elseif(!is_null($price_from) && is_null($price_to))
        {
           $products = Product::with(['product_variant_prices' => function ($q) use ($price_from) {
            $q->where('price','>=',  $price_from);
            },'ProductVariant' => function ($q) use ($variant) {
            $q->where('variant',$variant);
            }])->orWhere('title','LIKE','%'.$title.'%')
            ->paginate(10);
        }elseif(is_null($price_from) && !is_null($price_to))
        {
            $products = Product::with(['product_variant_prices' => function ($q) use ($price_to) {
            $q->where('price','<=',  $price_to);
            },'ProductVariant' => function ($q) use ($variant) {
            $q->where('variant',$variant);
            }])->orWhere('title','LIKE','%'.$title.'%')
            ->paginate(10);
        }elseif(is_null($price_from) && is_null($price_to) && is_null($variant))
        {
            $products = Product::with(['product_variant_prices','ProductVariant'])->orWhere('title','LIKE','%'.$title.'%')
            ->paginate(10);
        }elseif(is_null($price_from) && is_null($price_to)){
            $products = Product::with(['product_variant_prices','ProductVariant' => function ($q) use ($variant) {
            $q->where('variant',$variant);
            }])->orWhere('title','LIKE','%'.$title.'%')
            ->paginate(10);
        }

     $Variant = Variant::with('ProductVariant')->orderBy('id','asc')->get();
        $firstProduct = Product::orderBy('created_at','asc')->first();
        return view('products.index',['is_filtered'=>1,'product'=>$products,'Variant'=>$Variant,'firstProduct'=>$firstProduct]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
