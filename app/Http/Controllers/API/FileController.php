<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Storage;
use File as StorageFile;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return File::select('*')->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $activeUserId = auth()->user()->id;
        $existingFiles = null;
        
        for($i = 0; $i < $request->get('filesCount'); $i++) {
            $file = $request->file('file' . $i);
            if (count(File::where('file_name', $file->getClientOriginalName())->get())) {
                // array_push($existingFiles, $file->getClientOriginalName());
                $existingFiles === null ? 
                    $existingFiles = $file->getClientOriginalName() : 
                    $existingFiles = $existingFiles . ', ' . $file->getClientOriginalName();
            } else {
                Storage::disk('public', $file->getClientOriginalName())
                ->put(
                    $file->getClientOriginalName(), 
                    StorageFile::get($file)
                );
                File::create([
                    'uploaded_by_user_id' => $activeUserId,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'file_path' => '/public/config-uploads',
                ]);
            }
        }
        
        return response()->json([
            'error' => $existingFiles !== null ? true : false,
            'message' => $existingFiles !== null ? 'These files already exist' : 'All files uploaded successfully',
            $existingFiles !== null ? 'not_uploaded_files' : null => $existingFiles,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        //
    }
}
