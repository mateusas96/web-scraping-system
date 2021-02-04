<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Storage;
use File as StorageFile;
use Carbon\Carbon;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return File::select(
            'uuid',
            'uploaded_by_user_username',
            'version',
            'file_name',
            'file_size',
            'file_path',
            'updated_at',
        )->latest()->paginate(10);
    }

    /**
     * Display a listing of the resource for select.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFilesForSelect()
    {
        return File::select(
            'id',
            'uuid',
            'file_name'
        )->get();
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
        $activeUserUsername = auth()->user()->username;
        $existingFiles = null;
        $filesUploaded = [];
        
        for($i = 0; $i < $request->get('filesCount'); $i++) {
            $file = $request->file('file' . $i);
            if (count(File::where('file_name', $file->getClientOriginalName())->get())) {
                $existingFiles === null ? 
                    $existingFiles = $file->getClientOriginalName() : 
                    $existingFiles = $existingFiles . ', ' . $file->getClientOriginalName();
            } else {
                array_push($filesUploaded, $i);
                Storage::disk('public')->put(
                    $file->getClientOriginalName(), 
                    StorageFile::get($file)
                );
                File::create([
                    'uploaded_by_user_id' => $activeUserId,
                    'uploaded_by_user_username' => $activeUserUsername,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize() === 0 ? '0 KB' : round($file->getSize() / 1024, 3) . ' KB',
                    'file_path' => './config-uploads/',
                ]);
            }
        }
        
        return response()->json([
            'error' => $existingFiles !== null ? true : false,
            'message' => $existingFiles !== null ? 'These files already exist' : 'All files uploaded successfully',
            'not_uploaded_files' => $existingFiles,
            'files_uploaded' => $filesUploaded,
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  String $uuid 
     * @return \Illuminate\Http\Response
     */
    public function updateFile(Request $request, $uuid)
    {
        $file = File::findByUuid($uuid);

        if (array_key_exists('error', $file)) {
            return $file;
        }

        $reuploadedFile = $request->file('file');

        unlink($file['file_path'] . $file['file_name']);

        Storage::disk('public')->put(
            $reuploadedFile->getClientOriginalName(), 
            StorageFile::get($reuploadedFile)
        );

        $file->version = $file['version'] + 1;
        $file->file_size = $reuploadedFile->getSize() === 0 ? '0 KB' : round($reuploadedFile->getSize() / 1024, 3) . ' KB';
        $file->save();

        return response([
            'error' => false,
            'message' => 'File successfully updated',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  String file uuid $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        $file = File::select('file_path', 'file_name')->where('uuid', $uuid)->get()[0];

        unlink($file['file_path'] . $file['file_name']);

        File::where('uuid', $uuid)->delete();

        return response()->json([
            'error' => false,
            'message' => 'File deleted successfully',
        ]); 
    }

    /**
     * Search for file in data base
     * 
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request){
        if($search = $request->get('query')){
            $files = File::select(
                'uuid',
                'uploaded_by_user_username',
                'version',
                'file_name',
                'file_size',
                'file_path',
                'updated_at',
            )->where(function($query) use ($search){
                $query
                    ->where('uploaded_by_user_username', 'LIKE', "%$search%")
                    ->orWhere('file_name', 'LIKE', "%$search%");
            })->latest()->paginate(10);
            return $files;
        }
        
        return $files = File::select(
            'uuid',
            'uploaded_by_user_username',
            'version',
            'file_name',
            'file_size',
            'file_path',
            'updated_at',
        )->latest()->paginate(10);
    }
}
