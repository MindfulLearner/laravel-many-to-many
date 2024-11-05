<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Type;
use Illuminate\Support\Facades\Storage;
class ProductController extends Controller
{




public function indexFiltered(Request $request)
{
    $query = Product::query();
    if ($request->type) {
        $query->whereHas('types', function ($query) use ($request) {
            $query->where('id', $request->type);
        });
    }
    
    $products = $query->get();
    $types = Type::get();
    
    return view('products.index', compact('products', 'types'));
}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::get();
        $types = Type::get();
        return view('products.index', compact('products', 'types')); // pass the products to the view
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // gestire il file
        // $cover_image = $request->file('cover_image');
        // $cover_image_path = $cover_image->store('cover_images', 'public');
        // dd($cover_image_path);

        
             // validazione dei dati
        $request->validate([
            'name' => 'required | string',
            'description' => 'required | string',
            'price' => 'required | numeric | min:0',
            'cover_image' => 'required | image | max:2048',
            'slug' => 'nullable | string',
            'likes' => 'nullable | integer | min:0',
            'published' => 'nullable | boolean',
        ]);

        // creo lo slug da name
        $request['slug'] = $this->createSlug($request->name);

        $pathImage = Storage::put('cover_images', $request->cover_image);
        $request['cover_image'] = $pathImage;


        dd($pathImage);
        // non mi carica slug e cover_image



        Product::create($request->all());


        return redirect()->route('products.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $types = Type::get();
        return view('products.edit', compact('product', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required | string',
            'description' => 'required | string',
            'price' => 'required | numeric | min:0',
            'cover_image' => 'required | image | max:2048',
            'types' => 'array',
            'remove_cover_image' => 'nullable | boolean',
        ]);

        // se cover_image è presente, allora salvo l'immagine e aggiorno il path cancella l'immagine precedente
        if (isset($request->cover_image)) {
            if ($product->cover_image) {
                Storage::delete($product->cover_image);
            }
            $pathImage = Storage::put('cover_images', $request->cover_image);
            $request['cover_image'] = $pathImage;
        }
        // se remove_cover_image è true, allora elimino l'immagine diventa nulla per il db
        if ($request->remove_cover_image) {
            Storage::delete($product->cover_image);
            $request['cover_image'] = null;
        }

        // link https://laravel.com/docs/11.x/eloquent-collections#method-except
        // surprisely working ?
        $product->update($request->except('types'));

        // link https://laravel.com/docs/11.x/eloquent-relationships#syncing-associations
        // sync utilizzimo per aggiornare la relazione many to many
        // ho messo che deve esserci perforza
        if ($request->has('types')) {
            $product->types()->sync($request->types);
        }

        return redirect()->route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {

        $product->cover_image = Storage::delete($product->cover_image);

        $productFound = Product::find($product->id);
        $productFound->delete();

        return redirect()->route('products.index');
    }

    // creo un metodo statico per creare lo slug
    public static function createSlug($name, string $divider = '-')
    {
        // replace non letter or digit by divider
        $name = preg_replace('~[^\pL\d]+~u', $divider, $name);
        
        // transliterate
        $name = iconv('utf-8', 'us-ascii//TRANSLIT', $name);
        
        // remove unwanted characters
        $name = preg_replace('~[^-\w]+~', '', $name);
        
        // trim
        $name = trim($name, $divider);
        
        // remove duplicate divider
        $name = preg_replace('~-+~', $divider, $name);
        
        // lowercase
        $name = strtolower($name);
        
        if (empty($name)) {
            return 'n-a';
        }
        
        return $name;
    }
}
