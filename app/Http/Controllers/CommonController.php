<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommonController extends Controller
{
    public function downloadFile(Request $request)
    {

        
        // Retrieve the file name from the request (assuming it's passed as a query parameter)
        $file = $request->file;

        // Validate if the file parameter is provided
        if (!$file) {
            return response()->json(['error' => 'File parameter is missing.'], 400);
        }

        // Define the file path (ensure the file is within the public directory)
        $filePath = public_path($file);

        // Check if the file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        // Return the file as a download response
        return response()->download($filePath);
    }
}
