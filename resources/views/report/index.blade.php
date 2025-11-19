<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Summary Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h2 class="mb-4">Product Order Summary Report</h2>

    <form method="get" action="{{ route('report.index') }}" class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <input type="date" name="start" class="form-control" value="{{ request('start', $start) }}">
        </div>
        <div class="col-auto">
            <input type="date" name="end" class="form-control" value="{{ request('end', $end) }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Filter</button>
        </div>
        <!-- Export to Excel-->
        <div class="col-auto">
            <a href="{{ route('report.export', ['start'=>$start, 'end'=>$end]) }}" class="btn btn-success">Download Excel</a>
        </div>
    </form>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-sm-3 mb-2">
            <div class="card p-3">
                <div class="card-title"><b>Total Orders</b></div>
                <div class="h3">{{ number_format($totalOrders) }}</div>
            </div>
        </div>

        <div class="col-sm-3 mb-2">
            <div class="card p-3">
                <div class="card-title"><b>Total Revenue</b></div>
                <div class="h3">RM {{ number_format($totalRevenue,2) }}</div>
            </div>
        </div>

        <div class="col-sm-3 mb-2">
            <div class="card p-3">
                <div class="card-title"><b>Average Order Value</b></div>
                <div class="h3">RM {{ number_format($avgOrderValue,2) }}</div>
            </div>
        </div>

        <div class="col-sm-3 mb-2">
            <div class="card p-3">
                <div class="card-title"><b>Top 3 Products</b></div>
                <div>
                    <ol class="mb-0">
                        @foreach($topProducts as $p)
                            <li>
                                @if($p->product)
                                    {{ $p->product->name }} ({{ $p->total_qty }})
                                @else
                                    Product #{{ $p->product_id }} ({{ $p->total_qty }})
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Table -->
    <div class="card">
        <div class="card-body" style="background: grey;">
            <h3 style="color: white;">Detailed Orders</h3>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Order Date</th>
                        <th>Customer</th>
                        <th>State</th>
                        <th>Category</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price (RM)</th>
                        <th>Subtotal (RM)</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($orders as $order)

                        @php
                            $orderTotal = 0;
                            $rowDisplayed = false; 
                        @endphp

                        @foreach($order->items ?? [] as $item)

                            @php
                                $subtotal = $item->quantity * $item->unit_price;
                                $orderTotal += $subtotal;
                            @endphp

                            <tr>
                                <!-- Show order date and customer only for the first row -->
                                @if(!$rowDisplayed)
                                    <td>{{ $order->order_date }}</td>
                                    <td>{{ $order->customer->name }}</td>
                                    <td>{{ $order->customer->state }}</td>
                                    @php $rowDisplayed = true; @endphp
                                @else
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                @endif

                                <td>{{ optional($item->product->category)->name }}</td>      <!--optinal() : prevents errors if the product has no category-->
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ number_format($subtotal, 2) }}</td>
                            </tr>

                        @endforeach

                        <!-- Sum of the total row -->
                        <tr style="font-weight: bold;">
                            <td colspan="8">
                                <div class="d-flex justify-content-between">
                                    <span>Order #{{ $order->id }}</span>
                                    <span>Total: RM {{ number_format($orderTotal, 2) }}</span>
                                </div>
                            </td>
                        </tr>

                        <!-- Blank space between orders -->
                        <tr>
                            <td colspan="8" style="background: grey;"></td>
                        </tr>

                    @endforeach

                </tbody>
            </table>

        </div>
    </div>

</div>
</body>
</html>
