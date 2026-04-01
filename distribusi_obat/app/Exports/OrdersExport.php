<?php

namespace App\Exports;

use App\Models\ProductOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * Ambil data dari database
     */
    public function collection()
    {
        return ProductOrder::with(['user', 'status', 'type'])->latest()->get();
    }

    /**
     * Header kolom di Excel
     */
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Nama Unit/Faskes',
            'Jenis Pengiriman',
            'Status',
            'Total Nilai (Rp)',
            'Catatan',
            'Tanggal Pengajuan'
        ];
    }

    /**
     * Map data ke kolom yang sesuai
     */
    public function map($order): array
    {
        return [
            '#ORDER-' . substr($order->id, 0, 8),
            $order->user->name,
            $order->type->name ?? 'N/A',
            $order->status->name ?? 'Pending',
            number_format($order->total, 2, ',', '.'),
            $order->notes ?? '-',
            $order->created_at->format('d/m/Y H:i')
        ];
    }
}
