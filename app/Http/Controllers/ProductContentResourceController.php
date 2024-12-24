<?php

namespace App\Http\Controllers;

use App\Models\ProductContentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductContentResourceController extends Controller
{
    /**
     * Create resource from upload
     *
     * @param Illuminate\Http\Request $request
     */
    public static function upload(Request $request)
    {
        // Init
        $input_release_relative_persistent = ($request->input('release_relative_persistent') == 'on') ? true : false;

        $request->validate([
            'coverimage_file' => 'image|max:32678',
            'resource_file'   => 'required|max:32678',
        ]);

        // Determine the type of resource
        $type = $request->input('type');
        $coverimage_path = null;
        $resource_path = null;

        // Upload cover image to Cloudflare if present
        if ($request->hasFile('coverimage_file')) {
            $coverimage_path = self::uploadToCloudflare($request->file('coverimage_file'), env('CLOUDFLARE_IMAGE_TOKEN'));
        }

        // Upload resource file based on type
        if ($type === 'video') {
            // Use Cloudflare Stream token for videos
            $resource_path = self::uploadToCloudflareStream($request->file('resource_file'), env('CLOUDFLARE_STREAM_TOKEN'));
        } elseif ($type === 'image') {
            // Use Cloudflare Image token for images
            $resource_path = self::uploadToCloudflare($request->file('resource_file'), env('CLOUDFLARE_IMAGE_TOKEN'));
        } else {
            // Store locally for other types
            $resource_path = $request->file('resource_file')->store('public/product_content_resources');
            $resource_path = str_ireplace('public/', "", $resource_path); // Adjust path for local storage
        }

        // Find playlist position
        $resource_list_pos = ($request->input('resource_list_pos') != null) ? $request->input('resource_list_pos') : ProductContentResource::where('resource_list', 'like', '%' . $request->input('resource_list') . '%')->count() + 1;

        // Create ProductContentResource
        $resource = ProductContentResource::create([
            'name'                        => $request->input('name'),
            'description'                 => $request->input('description'),
            'type'                        => $request->input('type'),
            'audience'                    => json_encode([$request->input('audience')]),
            'resource_list'               => json_encode([$request->input('resource_list') => ['pos' => $resource_list_pos]]),
            'published'                   => true,
            'release_relative_day'        => $request->input('release_relative_day'),
            'release_relative_persistent' => $input_release_relative_persistent,
            'coverimage_path'             => $coverimage_path,
            'resource_location'           => $resource_path,
        ]);
        $resource->url_id       = \App\Helpers\CustomHelper::generateUrlId($request->input('name'));
        $resource->date_created = date('Y-m-d H:i:s');
        $resource->save();

        return view('feedback', ['title' => 'Resource Upload Success', 'message' => 'Resource file was uploaded successfully.']);
    }

    private static function uploadToCloudflare($file, $token)
    {
        if (!$file) {
            return null;
        }
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->attach(
            'file',
            file_get_contents($file->getRealPath()),
            "Shadow_".$file->getClientOriginalName()
        )->post('https://api.cloudflare.com/client/v4/accounts/' . env('CLOUDFLARE_ACCOUNT_ID') . '/images/v1');

        if ($response->successful()) {
            return $response->json()['result']['variants'][0]; // Return the Cloudflare image URL
        }
        return null;
    }

    private static function uploadToCloudflareStream($file, $token)
    {
        if (!$file) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->attach(
            'file',
            file_get_contents($file->getRealPath()),
            "Shadow_".$file->getClientOriginalName()
        )->post('https://api.cloudflare.com/client/v4/accounts/' . env('CLOUDFLARE_ACCOUNT_ID') . '/stream?direct_user=true');
        
        if ($response->successful()) {
            return $response->json()['result']['uid'];
        }
        
        return null;
    }

    public function edit($id)
    {
        $resource = ProductContentResource::find($id);
        return view('admin.edit_resource', compact('resource'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'coverimage_file'             => 'nullable|image|max:32678',
            'name'                        => 'required|string|max:255',
            'description'                 => 'required|string',
            'resource_list'               => 'nullable|string',
            'resource_list_pos'           => 'nullable',
            'release_relative_day'        => 'nullable|integer',
            'release_relative_persistent' => 'nullable',
            'resource_file'               => 'nullable|max:32678',
        ]);

        // Find the resource to update
        $resource = ProductContentResource::find($id);
        if (!$resource) {
            return view('error', [
                'title'   => 'Resource Not Found',
                'message' => 'The resource you are trying to update does not exist.',
            ]);
        }

        // Handle cover image upload to Cloudflare
        $coverimage_path = $resource->coverimage_path;
        if ($request->hasFile('coverimage_file')) {
            $coverimage_path = self::uploadToCloudflare($request->file('coverimage_file'), env('CLOUDFLARE_IMAGE_TOKEN'));
            if (!$coverimage_path) {
                return view('error', [
                    'title'   => 'Image Upload Failed',
                    'message' => 'There was an error uploading the cover image to Cloudflare.',
                ]);
            }
        }

        // Handle resource file upload based on type
        $resource_location = $resource->resource_location;
        if ($request->hasFile('resource_file')) {
            // Delete old resource file if it exists
            if ($resource_location && Storage::exists('public/product_content_resources/' . $resource_location)) {
                Storage::delete('public/product_content_resources/' . $resource_location);
            }

            // Determine the type of resource
            $type = $request->input('type');
            if ($type === 'video') {
                // Upload to Cloudflare Stream
                $resource_location = self::uploadToCloudflareStream($request->file('resource_file'), env('CLOUDFLARE_STREAM_TOKEN'));
                if (!$resource_location) {
                    return view('error', [
                        'title'   => 'Video Upload Failed',
                        'message' => 'There was an error uploading the video to Cloudflare Stream.',
                    ]);
                }
            } elseif ($type === 'image') {
                // Upload to Cloudflare Images
                $resource_location = self::uploadToCloudflare($request->file('resource_file'), env('CLOUDFLARE_IMAGE_TOKEN'));
                if (!$resource_location) {
                    return view('error', [
                        'title'   => 'Image Upload Failed',
                        'message' => 'There was an error uploading the image to Cloudflare.',
                    ]);
                }
            } else {
                // Store locally for other file types
                $resource_location = $request->file('resource_file')->store('public/product_content_resources');
                $resource_location = str_ireplace('public/', '', $resource_location);
            }
        }

        // Determine resource list position
        $resource_list_pos = $request->input('resource_list_pos') !== null
        ? $request->input('resource_list_pos')
        : ProductContentResource::where('resource_list', 'like', '%' . $request->input('resource_list') . '%')->count() + 1;

        // Prepare the update data
        $updateData = [
            'name'                        => $request->input('name'),
            'description'                 => $request->input('description'),
            'audience'                    => json_encode([$request->input('audience')]),
            'published'                   => true,
            'release_relative_day'        => $request->input('release_relative_day'),
            'release_relative_persistent' => $request->filled('release_relative_persistent'),
            'coverimage_path'             => $coverimage_path,
            'resource_location'           => $resource_location,
            'resource_list'               => json_encode([$request->input('resource_list') => ['pos' => $resource_list_pos]]),
            'resource_list_pos'           => $resource_list_pos,
        ];

        // Update the resource
        $resource->update($updateData);

        return view('feedback', [
            'title'   => 'Resource Update Success',
            'message' => 'Resource was updated successfully.',
        ]);
    }

    public function destroy(Request $request, $model_class_name, $id)
    {
        $ProductContentResource = ProductContentResource::find($id);
        $ProductContentResource->delete();

        // Redirect or return a response
        return redirect()->back()->with('status', 'Resource deleted successfully!');
    }
}
