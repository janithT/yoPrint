<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileUploads extends Model
{
    use HasFactory;

    protected $table = 'files';

    protected $fillable = [
        'filename',
        'status',
    ];
    public $timestamps = true;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Store file data in the database.
     *
     * @param string $fileName
     * @param string $fileStatus
     * @param string $filePath
     * @param string $fileType
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function storeFileData($fileName, $fileStatus, $fileType)
    {
        return $this->create([
            'filename' => $fileName,
            'status' => $fileStatus,
        ]);
    }
}
