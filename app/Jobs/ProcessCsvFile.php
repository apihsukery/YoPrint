<?php

namespace App\Jobs;

use App\Models\FileUpload;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessCsvFile implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public FileUpload $fileUpload
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update status to processing
            $this->fileUpload->update(['status' => 'processing']);

            // Get file path
            $filePath = Storage::path('uploads/' . $this->fileUpload->file_name);

            if (!file_exists($filePath)) {
                throw new \Exception('File not found: ' . $filePath);
            }

            // Open and read CSV file with UTF-8 encoding
            $file = fopen($filePath, 'r');

            // Skip BOM if present
            $bom = fread($file, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($file);
            }

            // Skip header row
            $header = fgetcsv($file);

            // Process rows in chunks
            $products = [];
            $chunkSize = 100;

            while (($row = fgetcsv($file)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Clean and convert each field to UTF-8
                $cleanRow = array_map(function($field) {
                    if ($field === null || $field === '') {
                        return null;
                    }
                    // Convert to UTF-8 and remove invalid characters
                    $field = mb_convert_encoding($field, 'UTF-8', 'UTF-8');
                    // Remove non-UTF-8 characters
                    $field = iconv('UTF-8', 'UTF-8//IGNORE', $field);
                    // Trim whitespace
                    return trim($field);
                }, $row);

                // Map CSV columns to database fields
                $products[] = [
                    'unique_key' => $cleanRow[0] ?? null,
                    'product_title' => $cleanRow[1] ?? null,
                    'product_description' => $cleanRow[2] ?? null,
                    'style#' => $cleanRow[3] ?? null,
                    'sanmar_mainframe_color' => $cleanRow[28] ?? null,
                    'size' => $cleanRow[18] ?? null,
                    'color_name' => $cleanRow[14] ?? null,
                    'piece_price' => $cleanRow[21] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Upsert in chunks for performance
                if (count($products) >= $chunkSize) {
                    Product::upsert(
                        $products,
                        ['unique_key'],
                        ['product_title', 'product_description', 'style#', 'sanmar_mainframe_color', 'size', 'color_name', 'piece_price', 'updated_at']
                    );
                    $products = [];
                }
            }

            // Upsert remaining products
            if (!empty($products)) {
                Product::upsert(
                    $products,
                    ['unique_key'],
                    ['product_title', 'product_description', 'style#', 'sanmar_mainframe_color', 'size', 'color_name', 'piece_price', 'updated_at']
                );
            }

            fclose($file);

            // Update status to completed
            $this->fileUpload->update(['status' => 'completed']);

        } catch (\Exception $e) {
            // Log error
            Log::error('CSV processing failed: ' . $e->getMessage(), [
                'file_upload_id' => $this->fileUpload->id,
                'file_name' => $this->fileUpload->file_name,
            ]);

            // Update status to failed
            $this->fileUpload->update(['status' => 'failed']);

            throw $e;
        }
    }
}
