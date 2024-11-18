<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    //
    public function dashboard()
    {
        // Paginate categories with 10 items per page
        $categories = Category::paginate(8);

        // Load and paginate products with the required fields
        $products = Product::with(['category' => function ($query) {
            $query->select('id', 'name'); // Only load 'id' and 'name' from categories
        }])->paginate(8); // Paginate products with 10 items per page

        // Transform the products to include the category name directly
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->p_name,
                'category_name' => $product->category ? $product->category->name : null,
                'image' => $product->image,
                // 'description' => $product->description,
                // 'price' => $product->price,
                'category_id' => $product->category_id,
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Pass paginated products and categories to the view
        return view('admin.product.dashboard', compact('categories', 'products'));
    }
    public function dashboard_2()
    {
        // Paginate categories with 10 items per page
        $categories = Category::all();

        // Load and paginate products with the required fields
        $products = Product::with(['category' => function ($query) {
            $query->select('id', 'name'); // Only load 'id' and 'name' from categories
        }])->get(); // Paginate products with 10 items per page

        // Transform the products to include the category name directly
        $products =  $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->p_name,
                'category_name' => $product->category ? $product->category->name : null,
                'image' => $product->image,
                'status' => $product->status,
                'stock_status' => $product->stock_status,
                // 'description' => $product->description,
                // 'price' => $product->price,
                'category_id' => $product->category_id,
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            ];
        });
        // dd($products);

        // Pass paginated products and categories to the view
        return view('admin.product.dashboard_2', compact('categories', 'products'));
    }

    public function category(){
         // Paginate categories with 10 items per page
         $categories = Category::all();
        // dd($categories);
 
         // Pass paginated products and categories to the view
         return view('admin.category.category', compact('categories'));
    }

    public function add_product(Request $request)
    {


        // dd($request->all());
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'p_name' => 'required|string|max:255',
            'stock_status' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',

        ]);



        $category = Category::findOrFail($request->input('category_id'));

        if (!$category) {
            return redirect()->back()->with('error', 'Category not found.');
        }
        $category_name = $category->name;


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
            $destinationPath = public_path('storage/images/' . $category_name);
            $image->move($destinationPath, $imageName);
        } else {
            return redirect()->back()->with('error', 'Product image is required.');
        }

        $category = Category::findOrFail($request->input('category_id'));

        if (!$category) {
            return redirect()->back()->with('error', 'Category not found.');
        }
        $category_name = $category->name;
        $product_name = $category_name . '_' . $request->input('p_name');



        // dd($request->all());
        $product = new Product();
        $product->p_name = $product_name;
        $product->category_id = $request->input('category_id');
        $product->image = $imageName;
        $product->stock_status = $request->input('stock_status');
        $product->save();
        return redirect()->back()->with('success', 'Product added successfully');
    }

    public function add_category(Request $request)
    {

        $category = Category::all();
        return view('admin.product.add_category', compact('category'));
    }

    public function add_category_post(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->status = 'Active';
        $category->save();
        return redirect()->back()->with('success', 'Category added successfully');
    }

    public function search_products(Request $request)
    {
        $query = $request->input('query');

        // Retrieve products matching the query, along with the related category
        $products = Product::with('category') // Eager load the category relationship
            ->where('p_name', 'LIKE', "%{$query}%")
            ->get();

        // Map the products to include category name
        $products = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'p_name' => $product->p_name,
                'image' => $product->image,
                'category_name' => $product->category ? $product->category->name : null, // Accessing category name
                'stock_status' => $product->stock_status,
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Return the products as JSON
        return response()->json($products);
    }

    public function delete($id)
    {
        $data = Product::find($id);
        $data->status = 'Inactive';
        $data->save();
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }

    public function delete_category($id){
        $data = Category::find($id);
        $data->status = 'Inactive';
        $data->save();
        return redirect()->back()->with('success', 'Category deleted successfully.');
    }

    public function edit(Request $request)
    {
        $productId = $request->input('product_id');
        $product = Product::find($productId);

        // Your code to handle the edit view, passing the product data to the view
        return view('admin.product.edit_product', compact('product'));
    }

    // public function edit_category(Request $request){
    //     $categoryId = $request->input('category_id');
    //     $category = Category::find($categoryId);

    //     // Your code to handle the edit view, passing the product data to the view
    //     return view('admin.category.edit_category', compact('category'));
    // }

    public function update(Request $request)
    {

        // dd($request->all());

        $request->validate([
            'id' => 'required|exists:products,id',
            'p_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stock_status' => 'required|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
            $destinationPath = public_path('storage/images/');
            $image->move($destinationPath, $imageName);
        }

        $productId = $request->input('id');
        $product = Product::find($productId);


        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }


        $product->p_name = $request->input('p_name');
        $product->category_id = $request->input('category_id');
        $product->stock_status = $request->input('stock_status');
        $product->image = $imageName ?? $product->image;
        $product->status = $product->status;
        $product->category_name = $request->input('category_name');
        $product->save();
        // dd('fine');
        return redirect('dashboard_2')->with('success', 'Product updated successfully.');
    }

    public function updateStockStatus(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $product->stock_status = $request->stock_status;
        $product->save();

        return redirect()->back()->with('success', 'Stock status updated successfully.');
    }

    public function updateStatus(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $product->status = $request->status;
        $product->save();

        return redirect()->back()->with('success', 'Product status updated successfully.');
    }
}
