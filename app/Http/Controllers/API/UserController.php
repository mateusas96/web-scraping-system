<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::select(
            'first_name',
            'last_name',
            'email',
            'username',
            'is_admin',
            'is_disabled',
            'can_upload_files',
            'created_at',
            'updated_at',
        )->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $email)
    {
        $this->validate($request,[
            'is_admin'      => 'required|int',
            'is_disabled'   => 'required|int',
        ]);

        $user = User::where('email', $email)->firstOrFail();

        $user->update($request->all());

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Search for user in data base
     * 
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request){
        $users = User::select(
            'first_name',
            'last_name',
            'username',
            'email',
            'is_admin',
            'is_disabled',
            'can_upload_files',
            'created_at',
            'updated_at',
        );

        if($search = $request->get('query')){
            $users->where(function($query) use ($search){
                $query
                    ->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('username', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('created_at', 'LIKE', "%$search%");
            });
        }
        
        return $users->paginate(10);
    }

    // returns all current (logged in) user's information
    public function getCurrentUser() {
        return User::where('email', auth()->user()->email)->first();
    }
}
