<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return Product::updateOrCreate(
            ['code' => $row['kode']],
            [
                'name' => $row['nama'],
                'price' => $row['harga'],
                'description' => $row['deskripsi'] ?? null,
                'is_active' => true,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'kode.required' => 'Kolom Kode Barang wajib diisi.',
            'nama.required' => 'Kolom Nama Barang wajib diisi.',
            'harga.required' => 'Kolom Harga wajib diisi.',
            'harga.numeric' => 'Kolom Harga harus berupa angka.',
        ];
    }
}
