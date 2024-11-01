<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Document</title>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">{{$product ? 'Update '.$product["name"] : 'Add New Product'}}</h2>
        
        <div id="status-card" role="status"></div>

        <form action="" method="POST" onsubmit="handleSubmit(event)" class="mb-4">
            @method($product ? 'PUT' : 'POST')
            @if ($product)
                <input type="hidden" name="id" value="{{$product['id']}}">
            @endif
            @csrf
            <div>
                <div class="mb-3 col-md-10 row">
                    <label for="name" class="form-label col-form-label col-lg-2">Product Name</label>
                    <div class="col-lg-10">
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter product name" value="{{$product ? $product['name'] : ''}}" required>
                    </div>
                </div>
                <div class="mb-3 col-md-10 row">
                    <label for="quantity" class="form-label col-form-label col-lg-2">Quantity</label>
                    <div class="col-lg-10">
                        <input type="number" id="quantity" name="quantity" class="form-control col-sm-10" placeholder="Enter quantity" value="{{$product ? $product['quantity'] : ''}}" required>
                    </div>
                </div>
                <div class="mb-3 col-md-10 row">
                    <label for="price" class="form-label col-form-label col-lg-2">Price per item</label>
                    <div class="col-lg-10">
                        <input type="number" id="price" name="price" class="form-control col-sm-10" placeholder="Enter price per item" step="0.01" value="{{$product ? $product['price'] : ''}}" required>
                    </div>
                </div>
            </div>
            <button type="submit" id="submit-button" class="btn btn-primary">{{$product ? 'Update' : 'Add'}} Product</button>
        </form>
    
        <h3>Product List</h3>
        <table class="table table-bordered mt-3">
            <thead class="table-light">
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total Value</th>
                    <th>Date Added</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr id="row-{{$product['id']}}">
                    <td>{{ $product['name'] }}</td>
                    <td>{{ $product['quantity'] }}</td>
                    <td>${{ number_format($product['price'], 2) }}</td>
                    <td>${{ number_format($product['quantity'] * $product['price'], 2) }}</td>
                    <td>{{ ( \Carbon\Carbon::parse($product['time-added']))->toDayDateTimeString() }}</td>
                    <td><a href={{"./". (string) $product['id']}}>Edit</a></td>
                </tr>
                @endforeach
                <tr id="sum-row">
                    <td><b>Sum</b></td>
                    <td></td>
                    <td></td>
                    <td id="sum-cell">${{ number_format($sumTotalValue, 2) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
<script>
    function formatDate(dateString) {
        const date = new Date(dateString);

        const options = {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            hour12: true,
            timeZone: 'UTC'
        };

        return new Intl.DateTimeFormat('en-US', options).format(date);
    }
    function formatAmount(amount, decimals = 2) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(amount);
    }

    function updateStatus(status, message) {
        const statusBanner = document.querySelector('#status-card');
        statusBanner.textContent = message;

        let className;
        if (status === 'success') statusBanner.className = 'p-2 mb-3 text-success-emphasis bg-success-subtle border border-success-subtle';
        else if (status === 'failed') statusBanner.className = 'p-2 mb-3 text-danger-emphasis bg-danger-subtle border border-danger-subtle';

        statusBanner.classList.add(className);

        setTimeout(() => {
            statusBanner.textContent = '';
            statusBanner.className = '';
        }, 6000);
    }

    function updateButton(status) {
        const button = document.querySelector('#submit-button');
        button.textContent = status === 'loading' ? 'Adding...' : 'Add Product';
        if (status === 'loading') button.disabled = true;
        else button.disabled = false;
    }

    function updateProductRow(product, sum, type) {
        // if product is existing update row else create new row
        const tableBody = document.querySelector('tbody');
        let row;
        if (type === 'update') {
            row = document.querySelector('tr#row-'+product['id']);
        } else if (type === 'add') {
            row = document.createElement('tr');
        }
        row.innerHTML = `
        <td>${product.name}</td>
        <td>${product.quantity}</td>
        <td>$${formatAmount(product.price)}</td>
        <td>$${formatAmount(product.price * product.quantity)}</td>
        <td>${formatDate(product['time-added'])}</td>
        <td><a href=${"./"+ product['id']}>Edit</a></td>
        `
        if (type === 'add') tableBody.insertBefore(row, document.querySelector('#sum-row'));

        // update sum
        const sumCell = document.querySelector('#sum-cell');
        sumCell.textContent = '$'+formatAmount(sum);
    }
    async function handleSubmit(e) {
        e.preventDefault();
        updateButton('loading');
        await new Promise((res) => setTimeout(res, 2000)); // artificial delay of 2s
        const formData = new FormData(e.target);
        const type = formData.get('_method') === 'PUT' ? 'update' : 'add';
        const url = type === 'update' ? `api/products/${formData.get('id')}` : 'api/products';
        const response = await fetch(url, {method: 'POST', body: formData});
        
        if (!response.ok) {
            updateStatus('failed', type === 'add' ? 'Could not add new product' : 'Could not update product');
            updateButton('done');
            return;
        }
        const data = await response.json();
        updateStatus('success', type === 'add' ? 'New product added' : 'Product updated');
        updateButton('done');
        updateProductRow(data.product, data['sum-total-value'], type);
    }
</script>
</html>
