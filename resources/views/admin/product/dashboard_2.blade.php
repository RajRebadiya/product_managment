@extends('admin.layout.template')
@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Apply on hover */
        select.form-select-sm:hover {
            background-color: #e2e6ea !important;
            /* Light grey hover effect */
        }

        /* Apply on focus */
        select.form-select-sm:focus {
            background-color: #fff3cd !important;
            /* Light yellow when focused */
            border-color: #ffeeba !important;
            color: #856404;
        }
    </style>
    @if (session('success'))
        <div class="alert alert-secondary alert-dismissible fade show " role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show " role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif



    <div class="mb-6">
        <h3>Products List</h3>
        <div class="d-flex mb-3" style="
        justify-content: end;
    ">

            <form id="cart-form" action="{{ route('add_to_cart') }}" method="POST">
                @csrf
                <input type="hidden" id="cart-product-ids" name="product_ids">
            </form>


            {{-- <div class="d-flex flex-between-center mb-3 search-box navbar-top-search-box d-none d-lg-block"
                style="width:25rem;">
                <form class="position-relative" id="searchForm" data-bs-toggle="search" data-bs-display="static">
                    <input id="searchInput" class="form-control search-input fuzzy-search rounded-pill form-control-sm"
                        type="search" placeholder="Search..." aria-label="Search" />
                    <span class="fas fa-search search-box-icon"></span>
                </form>
                <div class="btn-close position-absolute end-0 top-50 translate-middle cursor-pointer shadow-none"
                    data-bs-dismiss="search">
                    <button class="btn btn-link p-0" aria-label="Close"></button>
                </div>
            </div> --}}

            <!-- Results will be displayed here -->
            {{-- <ul id="products-container"></ul> --}}
            <button id="add-to-cart-btn" class="btn btn-primary me-4">Add to Cart</button>
            <button id="cart" class="btn btn-primary me-4" onclick="window.location='{{ route('cart_detail') }}'">Cart</button>

            <button class="btn btn-primary me-4" type="button" data-bs-toggle="modal" data-bs-target="#addDealModal"
                aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><svg
                    class="svg-inline--fa fa-plus me-2" aria-hidden="true" focusable="false" data-prefix="fas"
                    data-icon="plus" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                    data-fa-i2svg="">
                    <path fill="currentColor"
                        d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z">
                    </path>
                </svg><!-- <span class="fas fa-plus me-2"></span> Font Awesome fontawesome.com -->Add New Product</button>
        </div>
        {{-- @dd($products); --}}

        <div id="tableExample3"
            data-list='{"valueNames":["no","category_name","name","image" ,"stock_status" , "status"],"page":10,"pagination":true}'>
            <div class="search-box mb-3 mx-auto">
                <form class="position-relative">
                    <input class="form-control rounded-pill search-input search form-control-sm" type="search"
                        placeholder="Search" aria-label="Search" />
                    <!-- Search icon here -->
                </form>

            </div>
            <div class="table-responsive">
                <table class="table table-striped table-sm fs-9 mb-0">
                    <thead>
                        <tr>
                            <th class="white-space-nowrap fs-9 align-middle ps-0" style="max-width:20px; width:18px;">
                                <div class="form-check mb-0 fs-8">
                                    <input class="form-check-input" id="select-all-products" type="checkbox" style='width: 20px; height: 20px;' />
                                </div>
                            </th>
                            <th class="sort border-top border-translucent ps-3" data-sort="no">No</th>
                            <th class="sort border-top" data-sort="image">image</th>
                            <th class="sort border-top" data-sort="name">Product Name</th>
                            <th class="sort border-top border-translucent ps-3" data-sort="category_name">Category Name</th>
                            <th class="sort border-top border-translucent ps-3" data-sort="stock_status">Stock Status</th>
                            <th class="sort border-top border-translucent ps-3" data-sort="status">Status</th>
                            <th class="sort text-middle align-middle pe-0 border-top" scope="col">Edit</th>
                            <th class="sort text-middle align-middle pe-0 border-top" scope="col">Delete</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        @foreach ($products as $item)
                            <tr>
                                <td class="align-middle" style="max-width:20px; width:18px;">
                                    <input type="checkbox" class="form-check-input product-select"
                                        data-product-id="{{ $item['id'] }}"
                                        style="
                                        height: 20px;
                                        width: 20px;
                                    ">
                                </td>
                                <td class="align-middle ps-3 no">{{ $loop->iteration }}</td>
                                <td class="align-middle image">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal"
                                        onclick="showImage('{{ asset('storage/images/' . $item['category_name'] . '/' . $item['image']) }}')">
                                        <img src="{{ asset('storage/images/' . $item['category_name'] . '/' . $item['image']) }}"
                                            alt="{{ $item['name'] }}" style="width: 50px; height: auto;" />
                                    </a>
                                </td>

                                <!-- Modal Structure for Full Image View -->
                                <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="imageModalLabel">Image Preview</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <!-- Full-Size Image Displayed in Modal -->
                                                <img id="modalImage" src="" style="width: auto; height: 100%;"
                                                    class="img-fluid rounded" alt="Full-Size Image">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <td class="align-middle name text-bold" style="font-weight: bold">{{ $item['name'] }}</td>
                                <td class="align-middle ps-3 category_name">{{ $item['category_name'] }}</td>
                                <!-- Stock Status Dropdown with Colors -->
                                <td class="align-middle stock_status">
                                    <!-- Hidden span for searchable text -->
                                    {{-- <span
                                        class="d-none">{{ $item['stock_status'] == 'in_stock' ? 'IN STOCK' : 'OUT OF STOCK' }}</span> --}}
                                    <form action="{{ route('update_stock_status') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                        <select name="stock_status" class="form-select form-select-sm"
                                            style="background-color: {{ $item['stock_status'] == 'in_stock' ? '#d4edda' : '#f8d7da' }}; color: {{ $item['stock_status'] == 'in_stock' ? '#155724' : '#721c24' }}; width: 50%;"
                                            onchange="this.form.submit()">
                                            <option value="in_stock"
                                                {{ $item['stock_status'] == 'in_stock' ? 'selected' : '' }}>IN STOCK
                                            </option>
                                            <option value="out_of_stock"
                                                {{ $item['stock_status'] == 'out_of_stock' ? 'selected' : '' }}>OUT OF
                                                STOCK</option>
                                        </select>
                                    </form>
                                </td>

                                <!-- Status Dropdown with Colors -->
                                <td class="align-middle status">
                                    <form action="{{ route('update_status') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                        <select name="status" class="form-select form-select-sm"
                                            style="background-color: {{ $item['status'] == 'Active' ? '#d4edda' : '#f8d7da' }}; color: {{ $item['status'] == 'Active' ? '#155724' : '#721c24' }}; width: 50%;"
                                            onchange="this.form.submit()">
                                            <option value="Active" {{ $item['status'] == 'Active' ? 'selected' : '' }}>
                                                ACTIVE</option>
                                            <option value="Inactive"
                                                {{ $item['status'] == 'Inactive' ? 'selected' : '' }}>INACTIVE</option>
                                        </select>
                                    </form>
                                </td>

                                <td class="align-middle text-middle pe-0">
                                    <!-- Edit Product Form -->
                                    <form action="{{ route('edit_product') }}" method="GET" style="display:inline;">
                                        <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                        <button type="submit" class="btn btn-warning btn-sm content-icon">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    </form>
                                </td>

                                <td class="align-middle text-middle pe-0">
                                    <button class="btn btn-danger btn-sm content-icon"
                                        onclick="confirmDeletion('{{ $item['id'] }}')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between mt-3"><span class="d-none d-sm-inline-block"
                    data-list-info="data-list-info"></span>
                <div class="d-flex"><button class="page-link" data-list-pagination="prev"><span
                            class="fas fa-chevron-left"></span></button>
                    <ul class="mb-0 pagination"></ul><button class="page-link pe-0" data-list-pagination="next"><span
                            class="fas fa-chevron-right"></span></button>
                </div>
            </div>
        </div>
    </div>




    <form action="{{ route('add-product') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="addDealModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="addDealModal" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content bg-body-highlight p-6">
                    <div class="modal-header justify-content-between border-0 p-0 mb-2">
                        <h3 class="mb-0">Add Product Detail</h3><button type="reset"
                            class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
                                class="fas fa-times text-danger"></span></button>
                    </div>
                    {{-- @dd($categories); --}}
                    <div class="modal-body px-0">
                        <div class="row g-4">
                            <div class="col-lg-6">

                                <div class="mb-4"><label class="text-body-highlight fw-bold mb-2">Select Product
                                        Category</label><select class="form-select" name='category_id'>
                                        <option>Select</option>
                                        @foreach ($categories as $item)
                                            <option name='category_id' value="{{ $item->id }}">{{ $item->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                    @error('category_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-4"><label class="text-body-highlight fw-bold mb-2">Product
                                        Name</label><input class="form-control" type="text" name='p_name'
                                        placeholder="Enter Product name" />
                                    @error('p_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <input type="hidden" name="stock_status" value="in_stock">
                                <div class="mb-4"><label class="text-body-highlight fw-bold mb-2">Image</label>
                                    <div class="input-group"><input class="form-control" type="file"
                                            placeholder="Enter image url" name='image' /><span
                                            class="input-group-text"><span
                                                class="fas fa-image text-body-tertiary"></span></span>
                                    </div>
                                    @error('image')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>


                            </div>

                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-6 px-0 pb-0"><button type="reset"
                            class="btn btn-link text-danger px-3 my-0" data-bs-dismiss="modal"
                            aria-label="Close">Cancel</button><button type="submit" class="btn btn-primary my-0">Create
                            Deal</button></div>
                </div>
    </form>
    </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script>
        function confirmDeletion(itemId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If confirmed, redirect to delete route
                    window.location.href = `delete_product/${itemId}`;
                }
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-open the modal if there are validation errors
            @if ($errors->any())
                var myModal = new bootstrap.Modal(document.getElementById('addDealModal'), {});
                myModal.show();
            @endif

            // Clear form fields and error messages when the modal is closed
            var productModal = document.getElementById('addDealModal');
            productModal.addEventListener('hide.bs.modal', function() {
                // Clear all input fields
                productModal.querySelectorAll('input').forEach(input => input.value = '');
                productModal.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

                // Remove error messages
                productModal.querySelectorAll('.text-danger').forEach(error => error.textContent = '');
            });
        });
    </script>

    <!-- JavaScript to Change Modal Image Source -->
    <script>
        function showImage(src) {
            document.getElementById('modalImage').src = src;
        }
    </script>

    <script>
        function editCategory(id) {
            // Redirect to edit page
            window.location.href = `/categories/${id}/edit`;
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Track selected product IDs globally
            let selectedProductIds = [];

            // Restore previously selected checkboxes
            function restoreSelections() {
                const productCheckboxes = document.querySelectorAll('.product-select');
                productCheckboxes.forEach((checkbox) => {
                    const productId = checkbox.getAttribute('data-product-id');
                    checkbox.checked = selectedProductIds.includes(productId);
                });
            }

            // Handle "Select All" functionality
            const selectAllCheckbox = document.getElementById('select-all-products');
            selectAllCheckbox.addEventListener('change', function() {
                const productCheckboxes = document.querySelectorAll('.product-select');
                productCheckboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                    const productId = checkbox.getAttribute('data-product-id');
                    if (this.checked && !selectedProductIds.includes(productId)) {
                        selectedProductIds.push(productId);
                    } else if (!this.checked) {
                        selectedProductIds = selectedProductIds.filter((id) => id !== productId);
                    }
                });
            });

            // Handle individual checkbox changes
            document.querySelectorAll('.product-select').forEach((checkbox) => {
                checkbox.addEventListener('change', function() {
                    const productId = this.getAttribute('data-product-id');
                    if (this.checked) {
                        if (!selectedProductIds.includes(productId)) {
                            selectedProductIds.push(productId);
                        }
                    } else {
                        selectedProductIds = selectedProductIds.filter((id) => id !== productId);
                        selectAllCheckbox.checked =
                            false; // Uncheck "Select All" if any are unchecked
                    }
                });
            });

            // Add to cart button functionality
            document.getElementById('add-to-cart-btn').addEventListener('click', function() {
                if (selectedProductIds.length > 0) {
                    document.getElementById('cart-product-ids').value = JSON.stringify(selectedProductIds);
                    document.getElementById('cart-form').submit();
                } else {
                    alert('Please select at least one product to add to the cart.');
                }
            });

            // Print selected products
            document.getElementById('print-selected-btn').addEventListener('click', function() {
                if (selectedProductIds.length === 0) {
                    alert('No products selected!');
                    return;
                }

                // Prepare printable content
                const printableArea = document.createElement('div');
                selectedProductIds.forEach((productId) => {
                    const row = document.querySelector(`[data-product-id="${productId}"]`).closest(
                        'tr').cloneNode(true);
                    row.querySelector('td:first-child').remove(); // Remove the checkbox column
                    printableArea.appendChild(row);
                });

                // Backup original content
                const originalContent = document.body.innerHTML;

                // Set printable content
                document.body.innerHTML = `<table class="table">${printableArea.innerHTML}</table>`;

                // Trigger print
                window.print();

                // Restore original content
                document.body.innerHTML = originalContent;
                window.location.reload(); // Reload to restore event listeners
            });

            // Restore selections when the page is loaded
            restoreSelections();
        });
    </script>
@endsection
