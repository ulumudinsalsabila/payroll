<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    @php
        $logoPath = public_path('assets/logos/logo.png');
        $logoData = null;
        if (is_file($logoPath)) {
            $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
    @endphp
    <style>
        body {
            font-size: 11pt;
            margin: 0;
            padding: 0;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .layout-table {
            border: none;
            margin-bottom: 10px;
        }

        .layout-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        /* Header Styles */
        .company-info h1 {
            margin: 0;
            font-size: 16pt;
            color: #000;
            text-transform: uppercase;
        }

        .company-info h2 {
            margin: 0 0 5px 0;
            font-size: 10pt;
            color: #000;
            font-weight: normal;
        }

        .company-info p {
            margin: 2px 0;
            font-size: 9pt;
            line-height: 1.2;
        }

        .invoice-info p {
            margin: 5px 0;
        }

        .customer-name {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 150px;
            font-style: italic;
        }

        /* Items Table Styles */
        .items-table {
            margin-bottom: 20px;
        }

        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 4px 5px;
        }

        .items-table th {
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
            color: #000;
        }

        .col-qty { width: 15%; text-align: center; }
        .col-name { width: 45%; }
        .col-price { width: 20%; text-align: right; }
        .col-total { width: 20%; text-align: right; }

        .items-table .empty-row td {
            height: 25px;
            border-top: none;
            border-bottom: none;
        }

        .items-table .last-item td {
            border-bottom: 1px solid #000;
        }

        /* Footer Styles */
        .bank-info strong {
            display: block;
            margin-bottom: 5px;
            color: #000;
        }
        
        .bank-info p {
            margin: 2px 0;
            font-size: 9pt;
        }

        .total-row td {
            font-size: 12pt;
            padding-bottom: 20px;
        }

        .total-label {
            font-weight: bold;
            color: #000;
            text-align: right;
            padding-right: 10px;
        }

        .total-value {
            border-bottom: 1px dotted #000;
            text-align: right;
        }

        .sign-table td {
            text-align: center;
            width: 50%;
        }

        .sign-table p {
            margin: 0;
            color: #000;
            font-weight: bold;
        }

        .sign-line {
            margin-top: 60px;
            border-bottom: 1px solid #000;
            width: 80%;
            display: inline-block;
        }
    </style>
</head>
<body>

    <!-- Header Layout -->
    <table class="layout-table">
        <tr>
            <!-- Left Side: Company Info -->
            <td style="width: 60%;">
                <table class="layout-table">
                    <tr>
                        <td class="company-info">
                            @if($logoData)
                                <img src="{{ $logoData }}" style="width: 200px; display: block;" alt="Logo" />
                            @endif
                            <p>Office :</p>
                            <p>Jl. Raya Cicalengka-Bandung, West Java 40395</p>
                            <p>Phone : 081214165911 / 08222330554</p>
                            <p>E-mail : hasnautama07@gmail.com</p>
                        </td>
                    </tr>
                </table>
            </td>
            
            <!-- Right Side: Invoice Info -->
            <td style="width: 20%;" class="invoice-info">
                <p>Bandung, {{ $invoice->issue_date->format('d M Y') }}</p>
                <p>Kepada Yth. Tuan / Toko :</p>
                <p class="customer-name">{{ $invoice->customer_name }}</p>
                <p>NO. {{ $invoice->invoice_number }}</p>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-qty">Banyaknya</th>
                <th class="col-name">NAMA BARANG</th>
                <th class="col-price">HARGA</th>
                <th class="col-total">JUMLAH</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr class="{{ $loop->last ? 'last-item' : '' }}">
                <td class="col-qty">{{ (float)$item->quantity }}</td>
                <td class="col-name">{{ $item->description }}</td>
                <td class="col-price">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="col-total">{{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <!-- Total Row moved here -->
            <tr>
                <td colspan="3"  class="total-label" style="border-left: 1px solid #000; padding: 5px;">Total Rp.</td>
                <td class="col-total" style="font-size: 12pt; font-weight: bold; padding: 5px;">{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer Layout -->
    <table class="layout-table">
        <tr>
            <!-- Left Side: Bank Info -->
            <td style="width: 50%;" class="bank-info">
                <strong>REK BANK. ASEP BAROK/NIDA NADZIRA</strong>
                <p>- REK MANDIRI : 131-00-1264641-2 (ASEP)</p>
                <p>- REK BRI : 3773-01-030215-53-2 (NIDA)</p>
                <p>- REK BCA : 2831377366 (NIDA)</p>
            </td>
            
            <!-- Right Side: Signatures -->
            <td style="width: 50%;">
                <table class="layout-table sign-table">
                    <tr>
                        <td>
                            <p>Tanda terima</p>
                            <span class="sign-line"></span>
                        </td>
                        <td>
                            <p>Hormat kami</p>
                            <span class="sign-line"></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
