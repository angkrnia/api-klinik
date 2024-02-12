<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::all();

        return response()->json([
            'code'      => 201,
            'status'    => true,
            'data'      => $facilities
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg,webp'],
        ]);

        $imageFile = $request->file('image');
        $imageName = date('md_His') . '_' . $imageFile->getClientOriginalName();
        $imagePath = $imageFile->move(public_path('facilities'), $imageName);
        $imageUrl = url('facilities/' . $imageName);

        $data = Facility::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imageUrl,
        ]);

        return response()->json([
            'code'      => 201,
            'status'    => true,
            'message'   => 'Fasilitas baru berhasil ditambahkan.',
            'data'      => $data
        ], 201);
    }

    public function destroy(Facility $facility)
    {
        $imageName = basename($facility->image);
        $imagePath = public_path('facilities/' . $imageName);

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $facility->delete();

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'message'   => 'Fasilitas berhasil dihapus.',
        ]);
    }
}
