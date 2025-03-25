<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }
        $data['posts'] = Post::all();
        return response()->json([
            'status' => true,
            'message' => 'All Posts Data',
            'data' => $data,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // **Check Authentication**
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        // **Validate Request**
        $validatorUser = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg,gif',
        ]);

        if ($validatorUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validatorUser->errors()->all(),
            ], 422);
        }

        // **Process Image**
        $img = $request->file('image');
        $imageName = time() . '.' . $img->getClientOriginalExtension();
        $img->move(public_path('uploads'), $imageName);

        // **Create Post with Authenticated User**
        $post = Post::create([
            'user_id' => $user->id,  // Ensure user association
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Post Created Successfully',
            'post' => $post,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // **Check Authentication**
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        // **Find Post**
        $post = Post::select('id', 'title', 'description', 'image')
            ->where('id', $id)
            ->first();

        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Post not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Your Single Post',
            'data' => $post,
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // ✅ **Check Authentication**
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        // ✅ **Find Post**
        $post = Post::where('id', $id)->first();
        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        // ✅ **Validate Request**
        $validatorUser = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'image' => 'nullable|mimes:png,jpg,jpeg,gif', // Image optional kiya
        ]);

        if ($validatorUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validatorUser->errors()->all(),
            ], 422);
        }

        // ✅ **Handle Image Upload & Delete Old Image**
        $imageName = $post->image; // Default old image rakho

        if ($request->hasFile('image')) {
            $path = public_path('uploads');

            // **Delete Old Image if Exists**
            if ($post->image && file_exists($path . '/' . $post->image)) {
                unlink($path . '/' . $post->image);
            }

            // **Upload New Image**
            $img = $request->file('image');
            $imageName = time() . '.' . $img->getClientOriginalExtension();
            $img->move($path, $imageName);
        }

        // ✅ **Update Post**
        $post->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Post Updated Successfully',
            'post' => $post,
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {


        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }
        // Get the post image
        $post = Post::select('image')->where('id', $id)->first();

        if ($post && $post->image) {
            $filePath = public_path('/uploads/' . $post->image);

            // Check if file exists before deleting
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        // Delete the post
        $deleted = Post::where('id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Post Deleted Successfully',
            'post' => $deleted,
        ], 200);
    }
}
