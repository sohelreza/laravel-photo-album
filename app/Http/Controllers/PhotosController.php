<?php

namespace App\Http\Controllers;

use App\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotosController extends Controller
{
    public function create($album_id)
    {
        return view('photos.create')->with('album_id', $album_id);
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'title' => 'required',
            'photo' => 'image|max:1999',
        ]);

        $fileNameWithExtension = $request->file('photo')->getClientOriginalName();
        $fileNameWithoutExtension = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);
        $extension = $request->file('photo')->getClientOriginalExtension();
        $fileNameToStore = $fileNameWithoutExtension . '_' . time() . '.' . $extension;
        $request->file('photo')->storeAs('public/photos/' . $request->input('album_id'), $fileNameToStore);

        $photo = new Photo;
        $photo->album_id = $request->input('album_id');
        $photo->title = $request->input('title');
        $photo->description = $request->input('description');
        $photo->size = $request->file('photo')->getSize();
        $photo->photo = $fileNameToStore;
        $photo->save();

        return redirect('/albums/' . $request->input('album_id'))->with('success', 'Image Uploaded');

    }

    public function show($id)
    {
        $photo = Photo::find($id);
        return view('photos.show')->with('photo', $photo);
    }

    public function destroy($id)
    {
        $photo = Photo::find($id);
        if (Storage::delete('public/photos/' . $photo->album_id . '/' . $photo->photo)) {
            $photo->delete();

            return redirect('/')->with('success', 'Image Deleted');
        }
    }
}