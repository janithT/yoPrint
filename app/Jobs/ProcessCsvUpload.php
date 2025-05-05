<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\FileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ProcessCsvUpload implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    protected $filePath;
    protected $id;

    public function __construct($filePath, $id)
    {
        $this->filePath = $filePath;
        $this->id = $id;
    }

    public function handle()
    {
        $file = FileUploads::find($this->id);

        if (!$file) {
            Log::error("File record not found for ID: {$this->id}");
            return;
        }

        try {
            // Set status to processing
            $file->update(['status' => 'processing']);

            // Start transaction
            DB::beginTransaction();

            $csv = Storage::get($this->filePath);
            $cleaned = mb_convert_encoding($csv, 'UTF-8', 'UTF-8//IGNORE');
            $lines = array_map('str_getcsv', explode(PHP_EOL, trim($cleaned)));

            if (empty($lines) || count($lines) < 2) {
                throw new \Exception('CSV file appears to be empty or malformed.');
            }

            $header = array_map('trim', array_shift($lines));

            foreach ($lines as $line) {
                if (count($line) < count($header)) continue;

                $row = array_combine($header, $line);
                if (!$row || empty($row['UNIQUE_KEY'])) continue;

                Product::updateOrCreate(
                    ['UNIQUE_KEY' => $row['UNIQUE_KEY']],
                    [
                        'file_id' => $file->id,
                        'PRODUCT_TITLE' => $row['PRODUCT_TITLE'] ?? '',
                        'PRODUCT_DESCRIPTION' => $row['PRODUCT_DESCRIPTION'] ?? '',
                        'STYLE#' => $row['STYLE#'] ?? '',
                        'SANMAR_MAINFRAME_COLOR' => $row['SANMAR_MAINFRAME_COLOR'] ?? '',
                        'SIZE' => $row['SIZE'] ?? '',
                        'COLOR_NAME' => $row['COLOR_NAME'] ?? '',
                        'PIECE_PRICE' => isset($row['PIECE_PRICE']) ? floatval($row['PIECE_PRICE']) : null,
                    ]
                );
            }

            // Commit transaction
            DB::commit();

            // Set final status
            $file->update([
                'status' => 'completed',
                'uploaded_at' => now(),
            ]);

            // Optionally delete or archive the processed file
            // Storage::delete($this->filePath);

        } catch (\Throwable $th) {
            DB::rollBack();


            $file->update(['status' => 'failed']);
            Log::error("CSV processing failed for file ID {$file->id}: " . $th->getMessage());


            // Optionally rethrow if needed
            // throw $th;
        }
    }
}

