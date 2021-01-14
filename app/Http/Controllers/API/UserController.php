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
            'created_at',
            'updated_at',
            'email_verified_at',
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
        $user = User::where('email', $email)->firstOrFail();

        $this->validate($request,[
            'is_admin'      => 'required|int',
            'is_disabled'   => 'required|int',
        ]);

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
        if($search = $request->get('query')){
            $users = User::select(
                'first_name',
                'last_name',
                'username',
                'email',
                'is_admin',
                'is_disabled',
                'created_at',
                'updated_at',
                'email_verified_at',
            )->where(function($query) use ($search){
                $query
                    ->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('username', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('created_at', 'LIKE', "%$search%")
                    ->orWhere('email_verified_at', 'LIKE', "%$search%");
            })->paginate(10);
            return $users;
        }
        
        return $users = User::select(
            'first_name',
            'last_name',
            'username',
            'email',
            'is_admin',
            'is_disabled',
            'created_at',
            'updated_at',
            'email_verified_at',
        )->paginate(10);
    }
}
