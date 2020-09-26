<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use App\group_follow;
use App\qna;
use App\qna_follow;
use App\user_follow;
use Storage;
use Illuminate\Support\Facades\Hash;

class profileController extends Controller
{


    public function updateProfile(Request $req){
        $res = [];

        if(Auth::id()){
           
            // Cek Auth Siapa
            $data = User::find($req->id);

            if($data->id == Auth::id()){
                
                if(Auth::user()->username != $req->username){
                    $cekUsername = User::where("username",$req->username)->first();
                    if(!$cekUsername){
                        $data->username =$req->username;
                    }else{
                        $res = [
                            "info" => "Username Telah digunakan"
                        ];
                        return $res;

                    }
                }

                if($req->avatar){
                    Storage::delete($data->id);
            
                    $image_64 = $req->avatar; //your base64 encoded data
            
                    $replace = substr($image_64, 0, strpos($image_64, ',')+1); 
            
                    // find substring fro replace here eg: data:image/png;base64,
            
                    $image = str_replace($replace, '', $image_64); 
            
                    $image = str_replace(' ', '+', $image); 
            
                    $imageName = $data->id.'.png';
                    
            
                    Storage::disk('public')->put($imageName, base64_decode($image));

                    $data->avatar = env("APP_URL")."/storage/".$imageName;
                }
                if($req->password){
                    $data->password = Hash::make($req->password);
                }

                $data->name = $req->name;
                $data->gender = $req->gender;
                $data->study_program = $req->study_program;
                $data->university = $req->university;
                $data->angkatan = $req->angkatan;
                $data->instagram = $req->instagram;
                $data->whatsapp = $req->whatsapp;

                $data->save();

                $res = [
                    "status" => "Success",
                    "data" => $data
                ];
                
                return $res;
              
             
            }else{
                array_push($res, [
                    "status" => "Failed"
                ]);
                return $res;
            }
        }
    }
    public function profile($uname){
         $user = User::where("username",ltrim($uname, '@'))->first();

        $follow = user_follow::
            where("user_id",Auth::id())
            ->where("followed_id",$user->id)
            ->first();

        if($follow){
            $user->followed = true;
        }
        
        return $user;
    }

    public function profileById($id){
        return User::find($id);
    }
    
    
    public function quest(Request $req, $id){
        // return $id;
        if(Auth::id()){
                
                    $skip = 0;
                    $take = 5;

                    if($req->page > 1){
                        $skip = $take * $req->page-1;
                    }

               

                    if($req->filter){
                        if($req->filter == 'Quest Only'){
                            $filterType = " quest_id is null";
                        }else if($req->filter == 'Quest & Balasan'){
                            $filterType = 'text != ""';
                        }else if($req->filter == 'Media'){
                            $filterType = ' (img != "" OR video != "" OR embed != "")';
                        }else{
                            $filterType = 'id';
                        }
                    }else{
                        $filterType = 'id ';
                    }

                    
                    $metda = qna::orderBy("id","DESC")
                        ->whereRaw($filterType)
                        ->where("user_id",$id)
                        ->with("group")
                        ->with("user")
                        ->with("quest")
                        ->skip($skip)->take($take)
                        ->get();


                    if($metda){
                    $metda->map(function($q) {

                        if($q->quest){

                            $q->membalas_user = User::find($q->quest->user_id)->username;
                        }

                        
                        $follow = qna_follow::
                            where("user_id",Auth::id())
                            ->where("quest_id",$q->id)
                            ->first();

                        if($follow){
                            $q->followed = true;
                        }

                        return $q;
                        
                    })->toArray();
                }
                    $respons = [
                        "data" => $metda
                    ];

                    return response()->json($respons);
        }
    }
}