<?php

namespace App\Services;

use App\Jobs\ProcessCsvUpload;

class FileUploadService
{

    public function uploadFile($file, $id)
    {

        $path = $file->store('csv_uploads');

        ProcessCsvUpload::dispatch($path, $id);

        return $id;
    }

}