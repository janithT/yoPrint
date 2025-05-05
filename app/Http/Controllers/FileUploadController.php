<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\FileUploadService;
use App\Models\FileUploads;

class FileUploadController extends Controller
{
    protected $fileUploadService;
    protected $fileModal;

    public function __construct(FileUploadService $fileUploadService, FileUploads $fileModal) {
        $this->fileUploadService = $fileUploadService;
        $this->fileModal = $fileModal;
    }

    public function index()
    {
        // Get uploaded files (for demo, from storage/app/uploads)
        $files = $this->fileModal->orderBy('created_at', 'desc')->get();

        return view('home', compact('files'));
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'file' => 'required|file|max:512', 
            ]);

            // save file details to the database
            $fileName = $request->file('file')->getClientOriginalName();
            $fileType = $request->file('file')->getClientMimeType();
            $fileStatus = 'pending';

            $store = $this->fileModal->storeFileData(
                $fileName,
                $fileStatus,
                $fileType
            );

            if(!$store) {
                return redirect()->route('home')->with('error', 'File upload failed: Unable to save file data.');
            }

            // store the file in the storage
            // upload the file
            $this->fileUploadService->uploadFile($request->file('file'), $store->id);

            return redirect()->route('home')->with('success', 'File uploaded successfully!');
        } catch (\Throwable $th) { 
            //throw $th;
            return redirect()->route('home')->with('error', 'File upload failed: ' . $th->getMessage());
        }
        
    }
}