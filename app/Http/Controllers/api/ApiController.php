<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    //
    public function category_data()
    {


        $search = request()->search;


        $categories = Category::where('name', 'LIKE', "%{$search}%")->orderBy('id', 'desc')->get();
        // dd($categories);

        // dd($categories);


        // Add category name directly to each product
        $categories = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
            ];
        });

        return response()->json([
            'status_code' => 200,
            'message' => 'Category successfully loaded',
            'data' => $categories
        ]);
    }

    public function product_data()
    {
        // Load products with only the required fields from category
        $products = Product::with(['category' => function ($query) {
            $query->select('id', 'name'); // Only load 'id' and 'name' from categories
        }])->get();

        // Add category name directly to each product
        $products = $products->map(function ($product) {
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

        return response()->json([
            'status_code' => 200,
            'message' => 'Product successfully loaded',
            'data' => $products
        ]);
    }

    public function search_products(Request $request)
    {
        $rules = [
            'input' => 'required',
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'status_code' => 400,
                'message' => $errorMessage,
                'data' => []
            ]);
        }

        $input = $request->input;
        $products = Product::where('p_name', 'LIKE', "%{$input}%")->get();

        // Add category name directly to each product
        $products = $products->map(function ($product) {
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

        return response()->json([
            'status_code' => 200,
            'message' => 'Product successfully loaded',
            'data' => $products
        ]);
    }

    public function product_add(Request $request)
    {
        // Define validation rules (remove unique rule from p_name)
        $rules = [
            'p_name' => 'required|string|max:255',
            'stock_status' => 'required',
            'image' => 'required',
            'category_id' => 'required',
            'category_name' => 'required'
        ];

        $messages = [
            'p_name.required' => 'Product name is required.',
            'p_name.string' => 'Product name must be a string.',
            'p_name.max' => 'Product name should not exceed 255 characters.',
        ];

        $categories = Category::find($request->category_id);

        if (!$categories) {
            return response()->json([
                'status_code' => 400,
                'message' => 'Category not found.',
                'data' => []
            ]);
        }

        $category_name = $categories->name;
        $prefixedProductName = $request->p_name;

        // Custom validation to check if prefixed product name already exists
        if (Product::where('p_name', $prefixedProductName)->where('category_id', $request->category_id)->exists()) {
            return response()->json([
                'status_code' => 400,
                'message' => 'This Product Name already exists.',
                'data' => []
            ]);
        }

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'status_code' => 400,
                'message' => $errorMessage,
                'data' => []
            ]);
        }

        // Handle the image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
            $destinationPath = public_path('storage/images/' . $category_name);
            $image->move($destinationPath, $imageName);
        } else {
            return redirect()->back()->with('error', 'Product image is required.');
        }

        // Save the product
        $product = new Product();
        $product->p_name = $request->p_name;
        $product->stock_status = $request->stock_status;
        $product->image = $imageName;
        $product->category_id = $request->category_id;
        $product->category_name = $request->category_name;
        $product->status = 'Active';
        $product->save();

        return response()->json([
            'status_code' => 200,
            'message' => 'Product added successfully',
            'data' => [$product]
        ]);
    }


    public function category_add(Request $request)
    {
        // dd($request->all());

        $rules = [
            'name' => 'required|string|max:255|unique:categories,name',
        ];

        $message = [
            'name.required' => 'Category is alredy exists.',
            'name.unique' => 'This Category is already exists.',

        ];


        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules, $message);

        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'status_code' => 400,
                'message' => $errorMessage,
                'data' => []
            ]);
        }

        $category = new Category();
        $category->name = $request->name;
        $category->status = 'Active';
        $category->save();
        // dd('fine');

        return response()->json([
            'status_code' => 200,
            'message' => 'Category add succesfully',
            'data' => [$category]
        ]);
    }

    public function all_products_with_pagination(Request $request)
    {
        // dd($request->all());
        $rules = [
            'limit' => 'required|integer',
            'page' => 'required|integer',
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'status_code' => 400,
                'message' => $errorMessage,
                'data' => []
            ]);
        }

        $search = $request->search;
        $limit = $request->limit;
        $page = $request->page;

        $productsQuery = Product::where(function ($query) use ($search) {
            $query->where('p_name', 'LIKE', "%{$search}%")
                ->orWhere('category_name', 'LIKE', "%{$search}%");
        })->where('status', 'Active')
            ->orderBy('id', 'desc');



        // Paginate the query
        $products = $productsQuery->paginate($limit, ['*'], 'page', $page);






        // Add category name directly to each product
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->p_name,
                'category_name' => $product->category ? $product->category->name : null,
                'image' => $product->image,
                'stock_status' => $product->stock_status,
                'category_id' => $product->category_id,
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Return a simplified response with only the data and essential pagination details
        return response()->json([
            'status_code' => 200,
            'message' => 'Products successfully loaded',
            'data' => $products->items(),

        ]);
    }

    public function all_category_with_pagination(Request $request)
    {
        // dd($request->all());
        $rules = [
            'limit' => 'required|integer',
            'page' => 'required|integer',
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'status_code' => 400,
                'message' => $errorMessage,
                'data' => []
            ]);
        }

        $search = $request->search;
        $limit = $request->limit;
        $page = $request->page;


        // Use page and limit for pagination
        $categoryQuery = Category::where('name', 'LIKE', "%{$search}%")->where('status', 'Active')->orderBy('id', 'desc');



        // Paginate the query
        $categorys = $categoryQuery->paginate($limit, ['*'], 'page', $page);



        // Add category name directly to each category
        $categorys->getCollection()->transform(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'status' => $category->status,
                // 'created_at' => $category->created_at->format('Y-m-d H:i:s'),
                // 'updated_at' => $category->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Return a simplified response with only the data and essential pagination details
        return response()->json([
            'status_code' => 200,
            'message' => 'categories successfully loaded',
            'data' => $categorys->items(),

        ]);
    }

    public function delete_product(Request $request)
    {
        // dd($request->all());
        $rules = [
            'id' => 'required',
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'status_code' => 400,
                'message' => $errorMessage,
                'data' => []
            ]);
        }
        $product = Product::find($request->id);
        if (!$product) {
            return response()->json([
                'status_code' => 400,
                'message' => 'Product not found.',
                'data' => []
            ]);
        }
        $product->status = 'Inactive';
        $product->save();
        return response()->json([
            'status_code' => 200,
            'message' => 'Product deleted successfully',
            'data' => [$product]
        ]);
    }

    public function delete_category(Request $request)
    {
        // dd($request->all());
        $rules = [
            'id' => 'required',
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'status_code' => 400,
                'message' => $errorMessage,
                'data' => []
            ]);
        }
        $category = Category::find($request->id);
        if (!$category) {
            return response()->json([
                'status_code' => 400,
                'message' => 'Category not found.',
                'data' => []
            ]);
        }
        $category->status = 'Inactive';
        $category->save();
        return response()->json([
            'status_code' => 200,
            'message' => 'Category deleted successfully',
            'data' => [$category]
        ]);
    }

    public function edit_category(Request $request)
    {
        // dd($request->all());
        $rules = [
            'id' => 'required',
            'name' => 'required|string|max:255',
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'status_code' => 400,
                'message' => $errorMessage,
                'data' => []
            ]);
        }
        $category = Category::find($request->id);
        if (!$category) {
            return response()->json([
                'status_code' => 400,
                'message' => 'Category not found.',
                'data' => []
            ]);
        }
        $category->name = $request->name;
        $category->save();
        return response()->json([
            'status_code' => 200,
            'message' => 'Category updated successfully',
            'data' => [$category]
        ]);
    }

    public function edit_product(Request $request)
    {
        // dd($request->all());
        $rules = [
            'id' => 'required',
            'p_name' => 'required',
            'category_id' => 'required',
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'status_code' => 400,
                'message' => $errorMessage,
                'data' => []
            ]);
        }

        $categories = Category::findOrFail($request->category_id);

        if (!$categories) {
            return response()->json([
                'status_code' => 400,
                'message' => 'Category not found.',
                'data' => []
            ]);
        }
        $category_name = $categories->name;

        $product = Product::find($request->id);
        if (!$product) {
            return response()->json([
                'status_code' => 400,
                'message' => 'Product not found.',
                'data' => []
            ]);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
            $destinationPath = public_path('storage/images/' . $category_name);
            $image->move($destinationPath, $imageName);
            // $product->image = $imageName;
        }

        if (Product::where('p_name', $request->p_name)->where('category_id', $request->category_id)->exists()) {
            return response()->json([
                'status_code' => 400,
                'message' => 'This Product Name already exists.',
                'data' => []
            ]);
        }




        $product->p_name =  $request->p_name;
        $product->image = $imageName ?? $product->image;
        $product->category_id = $request->category_id;
        $product->save();

        return response()->json([
            'status_code' => 200,
            'message' => 'Product updated successfully',
            'data' => [$product]
        ]);
    }

    public function product_stock_update(Request $request)
    {
        // dd($request->all());
        $rules = [
            'id' => 'required',
            'stock_status' => 'required',
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'status_code' => 400,
                'message' => $errorMessage,
                'data' => []
            ]);
        }

        $product = Product::find($request->id);
        if (!$product) {
            return response()->json([
                'status_code' => 400,
                'message' => 'Product not found.',
                'data' => []
            ]);
        }
        $product->stock_status = $request->stock_status;
        $product->save();
        return response()->json([
            'status_code' => 200,
            'message' => 'Product stock updated successfully',
            'data' => [$product]
        ]);
    }
}
