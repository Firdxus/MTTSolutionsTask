<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderItem;
// use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use DB;

class SalesPerformanceReportExport implements FromArray, WithEvents, ShouldAutoSize
{
    protected $start;
    protected $end;
    protected $rows = [];

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
        $this->buildRows();
    }

    protected function buildRows()
    {
        $totalOrders = Order::whereBetween('order_date', [$this->start, $this->end])->count();
        $totalRevenue = Order::whereBetween('order_date', [$this->start, $this->end])->sum('total_amount');

        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('order', function($q) {
                $q->whereBetween('order_date', [$this->start, $this->end]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(3)
            ->get()
            ->map(function($itm) {
                return ($itm->product ? $itm->product->name : "Product #{$itm->product_id}") . " ({$itm->total_qty})";
            })->toArray();

        $avgOrderValue = $totalOrders ? ($totalRevenue / $totalOrders) : 0;

        $this->rows[] = ["Product Order Summary Report"];
        $this->rows[] = ["Date Range", "{$this->start} to {$this->end}"];
        $this->rows[] = ["Total Orders", $totalOrders];
        $this->rows[] = ["Total Revenue", "RM " . number_format($totalRevenue,2)];
        $this->rows[] = ["Top 3 Products", implode(" | ", $topProducts)];
        $this->rows[] = ["Average Order Value", "RM " . number_format($avgOrderValue, 2)];
        $this->rows[] = [""];

        $this->rows[] = ['Order Date', 'Customer', 'State', 'Category', 'Product', 'Quantity', 'Unit Price (RM)', 'Subtotal (RM)'];

        $orders = Order::with(['customer', 'items.product.category'])
            ->whereBetween('order_date', [$this->start, $this->end])
            ->orderBy('order_date', 'desc')
            ->get();

        foreach ($orders as $order) {
            $orderSubtotal = 0;
            $firstRow = true;

            foreach ($order->items as $item) {
                $subtotal = $item->quantity * $item->unit_price;
                $orderSubtotal += $subtotal;

                $this->rows[] = [
                    $firstRow ? $order->order_date : '',
                    $firstRow ? $order->customer->name : '',
                    $firstRow ? $order->customer->state : '',
                    optional($item->product->category)->name ?? '',
                    $item->product->name ?? '',
                    $item->quantity,
                    number_format($item->unit_price,2),
                    number_format($subtotal,2),
                ];

                $firstRow = false;                                   // only display order info in the first row
            }
            // subtotal row for the order
            $this->rows[] = [
                "Order #{$order->id}",
                "", "", "", "", "", "Total",
                number_format($orderSubtotal, 2)
            ];
            // blank row between orders
            $this->rows[] = ["", "", "", "", "", "", "", ""];
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class=>function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:H1');                                      // Merge first row across 8 columns (A1:H1)
                $sheet->getStyle('A1')->getFont()->setBold(true)->getSize(17);    
                $sheet->getStyle('A2:A6')->getFont()->setBold(true);              // Bold labels in column A

                $sheet->getStyle('A8:H8')->getFont()->setBold(true)->getSize(17);         // Bold header row 8
                $sheet->getStyle('A8:H8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $lastRow = $sheet->getHighestRow();
                $range = 'A8:H' . $lastRow;
                $sheet->getStyle($range)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                for ($row = 9; $row <= $lastRow; $row++) {
                    $cellA = $sheet->getCell("A$row")->getValue();
                    $cellG = $sheet->getCell("G$row")->getValue();

                    if (str_starts_with($cellA, 'Order #')) {
                        $sheet->mergeCells("A$row:G$row");                      // Merge columns A to G
                        $sheet->getStyle("A$row:H$row")->getFont()->setBold(true);
                        $sheet->getStyle("A$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);       
                    }

                    if ($cellG === 'Total') {
                        $sheet->mergeCells("A$row:G$row");                       // Merge columns A to G for total label
                        $sheet->getStyle("A$row:H$row")->getFont()->setBold(true);
                        $sheet->getStyle("A$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);     // Align Total text to right
                    }
                    
                    $isEmptyRow = true;                   // Check if the row is empty
                    for ($col = 'A'; $col <= 'H'; $col++) {
                        if ($sheet->getCell("$col$row")->getValue() !== null && $sheet->getCell("$col$row")->getValue() !== '') {
                            $isEmptyRow = false;
                            break;
                        }
                    }
                    
                    if ($isEmptyRow) { 
                        $sheet->mergeCells("A$row:H$row");                   // Merge empty row across A-H
                    }
                }
            }
        ];
    }
}
