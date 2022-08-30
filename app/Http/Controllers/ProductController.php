<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\ProductImage;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Http\Requests\ProductStoreRequest;
use DB;
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
        $scope ='create';
        $variants = Variant::all();
        return view('products.create', compact('variants','scope'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductStoreRequest $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data['title'] = $request->title;
        $data['description']=$request->description;
        $data['sku'] = $request->sku;
        // INSERT IN PRODUCT VARIANT PRICES TABLE

        $product = Product::create($data);
        //  INSERT IN PRODUCT VARIANT TABLE

        foreach($request->product_variant as $variants){
            $variant_option = $variants['option'];
            foreach($variants['tags'] as $tags)
            {
                $product_variants = ProductVariant::create(['product_id'=>$product->id,'variant_id'=>$variant_option,'variant'=>$tags]);

            }
        }
        $Variant = Variant::all();
        $product_variant_one = [];
        $product_variant_two = [];
        $product_variant_three = [];
        $i = 1;
        foreach($Variant as $v)
        {
            $pro_variants = ProductVariant::where(['variant_id'=>$v->id,'product_id'=>$product->id])->get();

            if($i == 1)
            {
                foreach($pro_variants as $pv)
                {
                    array_push($product_variant_one, $pv->id);
                }
            }
            if($i == 2)
            {
                foreach($pro_variants as $pv)
                {
                    array_push($product_variant_two, $pv->id);
                }
            }
            if($i == 3)
            {
                foreach($pro_variants as $pv)
                {
                    array_push($product_variant_three, $pv->id);
                }
            }
            $i++;
        }
        $mPricedata = [];
        $m=0;
            for($j = 0 ; $j<count($product_variant_one);$j++)
            {
                for($k=0; $k<count($product_variant_two);$k++)
                {
                    for($l=0; $l<count($product_variant_three);$l++)
                    {
                        $ProductVariantPriceData['product_variant_one'] = $product_variant_one[$j];
                        $ProductVariantPriceData['product_variant_two'] = $product_variant_two[$k];
                        $ProductVariantPriceData['product_variant_three'] = $product_variant_three[$l];
                        $ProductVariantPriceData['price'] = $request->product_variant_prices[$m]['price'];
                        $ProductVariantPriceData['stock'] = $request->product_variant_prices[$m]['stock'];
                        $ProductVariantPriceData['product_id'] = $product->id;
                        array_push($mPricedata,$ProductVariantPriceData);
                        ProductVariantPrice::create($ProductVariantPriceData);
                        $m++;
                    }
                }
            }
        // ProductVariantPrice
        return response()->json(["data"=>$data,"msg"=>"Sucessfully Inserted"], 200);

    }
    public function image_upload(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $image = $request->file('file');
        $imageName = $image->getClientOriginalName();
        $image->move(public_path('images'), $imageName);

        $tableStatus = DB::select("show table status from med_question where Name = 'products'");

        $imageUpload= DB::table('product_images')->insert(['product_id'=>$tableStatus[0]->Auto_increment,'thumbnail'=>1,'file_path'=>'images/'.$imageName]);
        return response()->json(['success' => $imageUpload]);
        // ProductImage
    }
    public function image_update(Request $request,$id)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $image = $request->file('file');
        $imageName = $image->getClientOriginalName();
        $image->move(public_path('images'), $imageName);

        $tableStatus = DB::select("show table status from med_question where Name = 'products'");

        $imageUpload= DB::table('product_images')->insert(['product_id'=>$id,'thumbnail'=>1,'file_path'=>'images/'.$imageName]);
        return response()->json(['success' => $imageUpload]);
        // ProductImage
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
    public function getProductById($id)
    {
        $product = Product::with(['product_variant_prices','ProductImage'])->where("id",$id)->first();
        $Variant = Variant::all();
        $product_variant =[];
        $i=0;
         foreach($Variant as $v)
         {
            $ProductVariant = ProductVariant::where(['variant_id'=>$v->id,'product_id'=>$product->id]);
            if($ProductVariant->count() > 0){
                $ProductVariant = $ProductVariant->get();
                $product_variant[$i]['option'] = $v->id;
                $j=0;
                foreach($ProductVariant as $pv)
                {
                    $product_variant[$i]['tags'][$j] = $pv->variant;
                    $j++;
                }
                $i++;
            }
         }
        $product['product_variant']=$product_variant;
        $k=0;
         foreach ($product->product_variant_prices as $value):
            $title = '';
            if (!is_null($value->ProductVariant('product_variant_one'))):
                $title .= $value->ProductVariant('product_variant_one')->variant.'/';
            endif;

            if (!is_null($value->ProductVariant('product_variant_two'))):
                $title .= $value->ProductVariant('product_variant_two')->variant .'/';
            endif;

            if ($value->ProductVariant('product_variant_three')):
                $title .= $value->ProductVariant('product_variant_three')->variant;
            endif;
            $price = $value->price;
            $stock = $value->stock;
            $product['product_variant_prices'][$k]['price']=$price;
            $product['product_variant_prices'][$k]['stock']=$stock;
            $product['product_variant_prices'][$k]['title']=$title;
            $k++;
        endforeach;
        return response()->json([
            'status'=>200,
            'data'=>$product
        ], 200);
    }
    public function edit($id)
    {
        $scope ='edit';
        $variants = Variant::all();
        return view('products.edit', ['variants'=>$variants,'id'=>$id,'scope'=>$scope]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $product_id)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        // INSERT IN PRODUCT VARIANT PRICES TABLE
        $data = $request->only('title','description','sku');
        Product::where('id',$product_id)->update($data);
        $product = Product::where('id',$product_id)->first();
        //  INSERT IN PRODUCT VARIANT TABLE

        ProductVariant::where(['product_id'=>$product->id])->delete();
        foreach($request->product_variant as $variants){
            $variant_option = $variants['option'];
            foreach($variants['tags'] as $tags)
            {
                $product_variants = ProductVariant::create(['product_id'=>$product->id,'variant_id'=>$variant_option,'variant'=>$tags]);


            }
        }
        $Variant = Variant::all();
        $product_variant_one = [];
        $product_variant_two = [];
        $product_variant_three = [];
        $i = 1;
        foreach($Variant as $v)
        {
            $pro_variants = ProductVariant::where(['variant_id'=>$v->id,'product_id'=>$product->id])->get();

            if($i == 1)
            {
                foreach($pro_variants as $pv)
                {
                    array_push($product_variant_one, $pv->id);
                }
            }
            if($i == 2)
            {
                foreach($pro_variants as $pv)
                {
                    array_push($product_variant_two, $pv->id);
                }
            }
            if($i == 3)
            {
                foreach($pro_variants as $pv)
                {
                    array_push($product_variant_three, $pv->id);
                }
            }
            $i++;
        }
        $mPricedata = [];
        $m=0;
        ProductVariantPrice::where(['product_id'=>$product->id])->delete();
            for($j = 0 ; $j<count($product_variant_one);$j++)
            {
                for($k=0; $k<count($product_variant_two);$k++)
                {
                    for($l=0; $l<count($product_variant_three);$l++)
                    {
                        $ProductVariantPriceData['product_variant_one'] = $product_variant_one[$j];
                        $ProductVariantPriceData['product_variant_two'] = $product_variant_two[$k];
                        $ProductVariantPriceData['product_variant_three'] = $product_variant_three[$l];
                        $ProductVariantPriceData['price'] = $request->product_variant_prices[$m]['price'];
                        $ProductVariantPriceData['stock'] = $request->product_variant_prices[$m]['stock'];
                        $ProductVariantPriceData['product_id'] = $product->id;
                        array_push($mPricedata,$ProductVariantPriceData);
                        ProductVariantPrice::create($ProductVariantPriceData);
                        $m++;
                    }
                }
            }
        // ProductVariantPrice
        return response()->json(["data"=>$data,"msg"=>"Sucessfully Updated"], 200);
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
