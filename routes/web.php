<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Auth::routes(['verify' => true]);

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/api/instagram/{username}', function ($username) {


    $instagram = new \InstagramScraper\Instagram();
    $medias = $instagram->getMedias(ltrim($username, '@'));


    $res = [];
    foreach ($medias as $item){
        // dd($item);
        array_push($res, [
            "img"=> $item['imageThumbnailUrl'],
            "video"=> $item["videoStandardResolutionUrl"],
            "caption" => $item['caption'],
            "link"=> $item['link'],
        ]);
    }
            
    return json_encode(collect($res)->take(5));
});