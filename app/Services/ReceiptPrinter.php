<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use RuntimeException;

class ReceiptPrinter
{
    public function print(Order $order): void
    {
        $order->loadMissing('items');

        if (config('pos.receipt.driver') === 'pdf') {
            $path = 'receipts/'.$order->order_id.'.pdf';

            if (! Storage::disk('local')->put($path, $this->pdfContents($order))) {
                throw new RuntimeException('Struk PDF gagal disimpan.');
            }

            return;
        }

        if (config('pos.receipt.driver') !== 'escpos') {
            throw new InvalidArgumentException('Driver struk tidak dikenal.');
        }

        $connectorType = config('pos.receipt.connector');
        File::ensureDirectoryExists(config('pos.receipt.output_path'));
        $connector = match ($connectorType) {
            'file' => new FilePrintConnector(
                config('pos.receipt.output_path').DIRECTORY_SEPARATOR.$order->order_id.'.escpos',
            ),
            'windows' => new WindowsPrintConnector(config('pos.receipt.printer_name')),
            default => throw new InvalidArgumentException('Konektor printer tidak dikenal.'),
        };

        $printer = new Printer($connector);

        try {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text(config('pos.store_name')."\n");
            $printer->setEmphasis(false);
            $printer->text($order->order_id."\n");
            $printer->text($order->created_at->format('d/m/Y H:i:s')."\n");
            $printer->text(str_repeat('-', 32)."\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            foreach ($order->items as $item) {
                $printer->text($item->product_name."\n");
                $printer->text(\sprintf(
                    '%d x Rp%s  Rp%s'."\n",
                    $item->quantity,
                    number_format($item->unit_price, 0, ',', '.'),
                    number_format($item->line_total, 0, ',', '.'),
                ));
            }

            $printer->text(str_repeat('-', 32)."\n");
            $printer->text('Subtotal: Rp'.number_format($order->subtotal, 0, ',', '.')."\n");
            $printer->text('Diskon: Rp'.number_format($order->discount, 0, ',', '.')."\n");
            $printer->setEmphasis(true);
            $printer->text('Total: Rp'.number_format($order->gross_amount, 0, ',', '.')."\n");
            $printer->setEmphasis(false);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Terima kasih!\n\n");
            $printer->cut();
        } finally {
            $printer->close();
        }
    }

    public function pdfContents(Order $order): string
    {
        $order->loadMissing('items');
        $height = max(240, 190 + $order->items->count() * 36);

        return Pdf::loadView('receipts.pdf', ['order' => $order])
            ->setPaper([0, 0, 226.77, $height])
            ->output();
    }
}
