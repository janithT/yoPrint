<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'PRODUCT_TITLE',
        'UNIQUE_KEY',
        'PRODUCT_DESCRIPTION',
        'STYLE#',
        'SANMAR_MAINFRAME_COLOR',
        'SIZE',
        'COLOR_NAME',
        'PIECE_PRICE',
    ];

    public $timestamps = false;

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}