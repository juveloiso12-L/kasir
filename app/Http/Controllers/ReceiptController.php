<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ReceiptPrinter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    public function show(Order $order, ReceiptPrinter $receiptPrinter): StreamedResponse
    {
        $contents = $receiptPrinter->pdfContents($order);

        return response()->streamDownload(
            static function () use ($contents): void {
                echo $contents;
            },
            'struk-'.$order->order_id.'.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
