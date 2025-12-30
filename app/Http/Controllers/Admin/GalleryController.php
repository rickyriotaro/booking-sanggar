<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = Gallery::with('uploader');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $galleries = $query->latest()->paginate(8);
        return view('admin.gallery.index', compact('galleries'));
    }

    public function create()
    {
        return view('admin.gallery.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:Jasa Tari,Jasa Rias,Kostum,Umum',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Store image directly to public/storage/gallery
        $file = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Create directory if not exists
        $uploadDir = public_path('storage/gallery');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Save file directly to public/storage/gallery
        $file->move($uploadDir, $filename);
        $imagePath = 'gallery/' . $filename;

        Gallery::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'image_path' => $imagePath,
            'uploaded_by' => auth()->id()
        ]);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Foto galeri berhasil ditambahkan');
    }

    public function destroy(Gallery $gallery)
    {
        if ($gallery->image_path) {
            // Delete from public/storage folder
            $filePath = public_path('storage/' . $gallery->image_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $gallery->delete();

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Foto galeri berhasil dihapus');
    }

    public function edit(Gallery $gallery)
    {
        return view('admin.gallery.edit', compact('gallery'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:Jasa Tari,Jasa Rias,Kostum,Umum',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image from public/storage
            if ($gallery->image_path) {
                $filePath = public_path('storage/' . $gallery->image_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Store new image directly to public/storage/gallery
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Create directory if not exists
            $uploadDir = public_path('storage/gallery');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Save file directly to public/storage/gallery
            $file->move($uploadDir, $filename);
            $validated['image_path'] = 'gallery/' . $filename;
        }

        $gallery->update($validated);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Foto galeri berhasil diperbarui');
    }
}
