<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Storage;
use File as StorageFile;
use App\Models\SelectedFilesForScraping as SFFS;
use DB;
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
        $data = File::select(
            'files.uuid',
            'uploaded_by_user_username',
            'version',
            'file_name',
            'file_size',
            'file_path',
            'error_msg',
            's.code as status_code',
            'returned_for_fixing_message',
            'approvement_date',
            'files.updated_at',
        )->leftJoin(
            'status as s', 's.id', 'approvement_status_id'
        );

        if (!auth()->user()->is_admin) {
            $data->where('file_name', 'NOT LIKE', '%example%');
        }

        return $data->latest('files.created_at')->paginate(10);
    }

    /**
     * Display a listing of the resource for select.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFilesForSelect()
    {
        $files = File::select(
            DB::raw(
                '
                    files.id,
                    files.uuid,
                    IF (
                        s.code != "approvement_approved",
                        CONCAT(file_name, " (Unapproved)"),
                        file_name
                    ) AS file_name
                '
            )
        )->leftJoin(
            'status as s', 's.id', 'approvement_status_id'
        );

        if(auth()->user()->is_admin) {
            $files->where(function($query){
                $query->whereRaw(
                    '
                        error_msg IS NULL AND
                        file_name NOT LIKE "%example%"
                    '
                );
            }); 
        } else {
            $files->where(function($query){
                $query->whereRaw(
                    '
                        error_msg IS NULL AND
                        (
                            s.code = "approvement_approved" OR
                            approvement_status_id IS NULL
                        ) AND
                        file_name NOT LIKE "%example%"
                    '
                );
            }); 
        }

        return $files->get();
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

        $sent_for_approval_status_id = collect(
            DB::select('SELECT get_status_id_by_code("approvement_sent_for_approval") AS statusId')
        )->first()->statusId;
        
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
                    'approvement_status_id' => auth()->user()->is_admin == true ? null : $sent_for_approval_status_id,
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
        $file_id = $file['id'];

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
        $file->error_msg = null;
        $file->save();

        $stopped_for_a_reason_status_id = collect(
            DB::select('SELECT get_status_id_by_code("scraping_stopped_for_a_reason") AS statusId')
        )->first()->statusId;

        $not_started_status_id = collect(
            DB::select('SELECT get_status_id_by_code("scraping_not_started") AS statusId')
        )->first()->statusId;

        SFFS::where('selected_files_id', 'LIKE', "%$file_id%")
            ->where('status_id', $stopped_for_a_reason_status_id)
            ->update([
                'status_id' => $not_started_status_id
            ]);

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
    public function search(Request $request)
    {
        $files = File::select(
            'files.uuid',
            'uploaded_by_user_username',
            'version',
            'file_name',
            'file_size',
            'file_path',
            'error_msg',
            's.code as status_code',
            'returned_for_fixing_message',
            'approvement_date',
            'files.updated_at',
        )->leftJoin(
            'status as s', 's.id', 'approvement_status_id'
        );

        if($search = $request->get('query')){
            $files->where(function($query) use ($search){
                $query
                    ->where('uploaded_by_user_username', 'LIKE', "%$search%")
                    ->orWhere('file_name', 'LIKE', "%$search%");
            }); 
        }
        
        if (!auth()->user()->is_admin) {
            $files->where('file_name', 'NOT LIKE', '%example%');
        }

        return $files->latest('files.created_at')->paginate(10);
    }

    /**
     * Update file with error message from scraper
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateFileWithErrorMessageFromScraper(Request $request)
    {
        $error_msg = $request->get('error_message');
        $uuid = $request->get('uuid');

        $selected_files_data = SFFS::findByUuidAndActiveUserId($uuid, auth()->user()->id);

        File::whereRaw('FIND_IN_SET(id, ?)', $selected_files_data['selected_files_id'])
            ->update([
                'error_msg' => $error_msg
            ]);
        

        return response([
            'error' => false,
            'message' => 'Error message sent',
        ], 200);
    }

    /**
     * Reject file
     * Only admins can reject files
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function rejectFile(Request $request)
    {
        if (auth()->user()->is_admin == true) {
            $uuid = $request->get('uuid');
            $reject_msg = $request->get('returned_for_fixing_message');
            $returned_for_fixing_status_id = collect(
                DB::select('SELECT get_status_id_by_code("approvement_returned_for_fixing") AS statusId')
            )->first()->statusId;

            $file = File::findByUuid($uuid);

            $file->returned_for_fixing_message = $reject_msg;
            $file->approvement_status_id = $returned_for_fixing_status_id;
            $file->save();

            return response([
                'error' => false,
                'message' => 'File successfully rejected',
            ], 200);
        } else {
            return response([
                'error' => true,
                'message' => 'You do not have rights to perform this action',
            ], 401);
        }
    }

    /**
     * Approve file
     * Only admins can approve files
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function approveFile(Request $request)
    {
        if (auth()->user()->is_admin == true) {
            $uuid = $request->get('uuid');
            $approved_status_id = collect(
                DB::select('SELECT get_status_id_by_code("approvement_approved") AS statusId')
            )->first()->statusId;

            $file = File::findByUuid($uuid);

            $file->approvement_status_id = $approved_status_id;
            $file->approvement_date = Carbon::now();
            $file->save();

            return response([
                'error' => false,
                'message' => 'File successfully approved',
            ], 200);
        } else {
            return response([
                'error' => true,
                'message' => 'You do not have rights to perform this action',
            ], 401);
        }
    }

    /**
     * Resend file for approval
     * Function is created and should be used only for non-admin users action
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resendForApproval(Request $request, $uuid)
    {
        $file = File::findByUuid($uuid);
        $file_id = $file['id'];

        if (array_key_exists('error', $file)) {
            return $file;
        }

        $reuploadedFile = $request->file('file');

        unlink($file['file_path'] . $file['file_name']);

        Storage::disk('public')->put(
            $reuploadedFile->getClientOriginalName(), 
            StorageFile::get($reuploadedFile)
        );

        $sent_for_approval_status_id = collect(
            DB::select('SELECT get_status_id_by_code("approvement_sent_for_approval") AS statusId')
        )->first()->statusId;

        $file->version = $file['version'] + 1;
        $file->approvement_status_id = $sent_for_approval_status_id;
        $file->returned_for_fixing_message = null;
        $file->save();

        return response([
            'error' => false,
            'message' => 'File successfully resent for approval',
        ]);
    }
}
