<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCsvFile;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function index()
    {
        $files = FileUpload::orderBy('created_at', 'desc')->get();
        return view('welcome', compact('files'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            // Generate unique filename
            $fileName = time() . '_' . $originalName;

            // Store file in storage/app/uploads
            $file->storeAs('uploads', $fileName);

            // Create database record
            $fileUpload = FileUpload::create([
                'file_name' => $fileName,
                'original_name' => $originalName,
                'status' => 'pending',
            ]);

            // Dispatch job to process CSV file in background
            ProcessCsvFile::dispatch($fileUpload);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file' => $fileUpload,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function status($id)
    {
        $fileUpload = FileUpload::findOrFail($id);

        return response()->json([
            'id' => $fileUpload->id,
            'status' => $fileUpload->status,
            'original_name' => $fileUpload->original_name,
            'created_at' => $fileUpload->created_at->format('Y-m-d g:ia'),
            'relative_time' => $fileUpload->created_at->diffForHumans(),
        ]);
    }
}
