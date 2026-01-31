<?php
namespace App\Imports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BooksImport implements ToModel, WithHeadingRow
{
   public function model(array $row)
{
    return new Book([
        'title'       => $row['ten_sach'],
        'author'      => $row['tac_gia'],
        'publisher'   => $row['nha_xuat_ban'],
        'price'       => $row['gia'],
        'description' => $row['mo_ta'],
        'quantity'    => $row['so_luong'],
        'category_id' => $row['id_the_loai'],
        // Lấy tên ảnh "ảnh 1.jpg" từ cột H trong Excel
        'cover_image' => $row['anh_bia'] ?? 'default.png', 
    ]);
}
    
}