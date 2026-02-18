<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 10px;">
        <h2 style="color: #0d6efd; text-align: center;">E-PHARMA SYSTEM</h2>
        <hr>
        <p>Halo, <strong>{{ $drugRequest->user->name }}</strong></p>
        <p>Pemberitahuan resmi mengenai pesanan Anda dengan nomor ID: <strong>#REQ-{{ $drugRequest->id }}</strong></p>

        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center;">
            Status Saat Ini: <br>
            <strong style="font-size: 1.2rem; color: #198754;">{{ strtoupper($statusLabel) }}</strong>
        </div>

        <h4 style="margin-top: 25px;">Rincian Obat:</h4>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #eee;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Nama Obat</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($drugRequest->items as $item)
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        {{ $item->drug ? $item->drug->name : $item->custom_drug_name }}
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                        {{ $item->quantity }} {{ $item->drug ? $item->drug->unit : $item->custom_unit }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top: 25px;">Silakan pantau pengiriman Anda secara real-time melalui dashboard customer kami.</p>
        <hr>
        <footer style="font-size: 11px; color: #777; text-align: center;">
            &copy; 2024 E-Pharma Logistics Management System. <br>
            Email ini dikirim otomatis oleh sistem, mohon tidak membalas.
        </footer>
    </div>
</body>
</html>