<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use Illuminate\Support\Str;



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

    public function category()
    {
        // Paginate categories with 10 items per page
        $categories = Category::all();
        // dd($categories);

        // Pass paginated products and categories to the view
        return view('admin.category.category', compact('categories'));
    }

    public function add_product(Request $request)
    {


        dd($request->all());
        $request->validate([
            'id' => 'required',
            'category_id' => 'required|exists:categories,id',
            'p_name' => 'required|string|max:255|unique:products,p_name',
            'stock_status' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',

        ]);

        $product = Product::find($request->id);
        $original_name = $product->p_name;

        $category = Category::findOrFail($request->input('category_id'));

        if (!$category) {
            return redirect()->back()->with('error', 'Category not found.');
        }
        $category_name = $category->name;


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $category_name . '_' . $request->input('p_name') . '.' . $image->getClientOriginalExtension();
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

        if ($category_name . '_' . $request->p_name == $original_name) {
            return redirect()->back()->with('error', 'Product name already exists.');
        }


        // dd($request->all());
        $product = new Product();
        $product->p_name = $product_name;
        $product->category_id = $request->input('category_id');
        $product->image = $imageName;
        $product->stock_status = $request->input('stock_status');
        $$product->save();
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


        $category = Category::where('name', $request->name)->first();

        if ($category) {
            return redirect()->back()->with('error', 'Category already exists.');
        }



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

    public function delete_category($id)
    {
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

    public function addToCart(Request $request)
    {
        // Get the product IDs from the request
        $productIds = json_decode($request->input('product_ids'), true);

        // Check if product IDs are provided
        if (empty($productIds)) {
            return redirect()->back()->with('error', 'No products selected.');
        }

        // Store the cart_id in the session

        // Generate a unique cart ID for this batch
        $cartId = Str::random(10); // You can use any other method to generate a unique ID
        session(['cart_id' => $cartId]);

        // Retrieve the selected products from the database
        $products = Product::whereIn('id', $productIds)->get();

        // Store each product's ID and image in the cart table
        foreach ($products as $product) {
            // Create a new Cart entry for each product
            $cart = new Cart();
            $cart->cart_id = $cartId; // Store the generated cart ID for this batch
            $cart->image = $product->image;
            $cart->product_id = (string) $product->id; // Store product ID as a string
            $cart->p_name = $product->p_name; // Store the product name
            $cart->category_name = $product->category_name; // Store the product category name
            $cart->save(); // Save the cart entry to the database
        }

        return redirect()->route('cart')->with('success', 'Products added to cart.');
    }

    public function cart()
    {
        // Get the cart_id from the session
        $cartId = session('cart_id');

        // Check if cart_id exists in session
        if (!$cartId) {
            return redirect()->route('cart')->with('error', 'No cart found.');
        }

        // Retrieve the products using the cart_id
        $products = Cart::where('cart_id', $cartId)->get();
        // dd($products);

        return view('admin.product.cart_product', compact('products'));
    }

    public function clearCart(Request $request)
    {

        // Get the cart_id from the session
        $cartId = session('cart_id');

        // Check if cart_id exists in session
        if (!$cartId) {
            return redirect()->route('cart')->with('error', 'No cart found.');
        }

        // Retrieve the products using the cart_id
        $products = Cart::where('cart_id', $cartId)->get();

        foreach ($products as $product) {
            $product->delete();
        }

        return redirect()->route('cart')->with('success', 'Cart cleared successfully.');
    }

    public function cart_detail()
    {
        // Retrieve the cart_id from the session
        $cartId = session('cart_id');

        // If there is no cart_id in the session, return an error message
        if (!$cartId) {
            return redirect()->route('cart')->with('error', 'No cart found.');
        }

        // Retrieve cart items based on the cart_id
        $products = Cart::where('cart_id', $cartId)->get();

        // Return the cart detail view with the cart items
        return view('admin.product.cart_product', compact('products'));
    }
}
