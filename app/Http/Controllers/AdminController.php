<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class AdminController extends Controller
{
    /**
     * Enable access to local file storage for authenticated users with admin privileges only
     * 
     * @param Request $request
     * @param string $file The file path relative to the storage/app directory with '~~' standing in for '/'
     */
    public static function download($request, $file)
    {
        // Convert file_path
        $file_path = str_replace('~~', '/', $file);
        $file_path_exploded = explode('/', $file_path);
        $file_name = end($file_path_exploded);
        
        // Check if user is authenticated and the file exists
        if (Auth::check() && \App\Models\User::isAdmin() && Storage::exists($file_path)) {
            return Storage::download($file_path, $file_name, []);
        } else {
            abort(404);
        }
    }
}
