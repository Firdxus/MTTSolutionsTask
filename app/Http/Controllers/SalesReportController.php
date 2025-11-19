<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Exports\SalesPerformanceReportExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->input('start') ?? now()->subDays(30)->toDateString();
        $end = $request->input('end') ?? now()->toDateString();

        $totalOrders = Order::whereBetween('order_date', [$start, $end])->count();

        $totalRevenue = Order::whereBetween('order_date', [$start, $end])->sum('total_amount');

        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))->whereHas('order', function($q) use ($start, $end) {
            $q->whereBetween('order_date', [$start, $end]);
        })
        ->groupBy('product_id')
        ->orderByDesc('total_qty')
        ->take(3)
        ->with('product')
        ->get();

        $avgOrderValue = $totalOrders ? ($totalRevenue / $totalOrders) : 0;

        $orders = Order::with(['customer', 'items.product.category'])
            ->whereBetween('order_date', [$start, $end])
            ->orderBy('order_date', 'desc')
            ->get();

        return view('report.index', compact('orders','totalOrders','totalRevenue','topProducts','avgOrderValue','start','end'));
    }

    public function export(Request $request)
    {
        $start = $request->input('start') ?? now()->subDays(30)->toDateString();
        $end = $request->input('end') ?? now()->toDateString();

        $filename = "sales-performance-report-{$start}_to_{$end}.xlsx";
        return Excel::download(new SalesPerformanceReportExport($start, $end), $filename);
    }
}
