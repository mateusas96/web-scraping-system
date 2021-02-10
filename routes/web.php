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
    return view('auth.login');
})->middleware('guest');

Auth::routes();

Route::group(['middleware' => 'auth', 'middleware' => 'validateBackHistory'], function(){
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('/users/management', 'HomeController@index')->name('users.management');

    Route::get('/upload-configs', 'HomeController@index')->name('config.upload');

    Route::get('/scrape-data', 'HomeController@index')->name('scrape.data');

    Route::get('/scrape-data/view-scraper/{scarperName}', 'HomeController@index')->name('scrape.data');
});

Route::get('/testscraper', function() {

    $crawler = Goutte::request('GET', 'https://www.closed.com/en/women/jeans/');

    $crawler->filterXpath('//*[@id]/li/form/div')->each(function ($node) {
        // echo $node->html();
        // dump($node->filterXpath('//h2/span[last()]/text()')->text());
        // dump($node->filterXpath('//a/@href')->text());
        dump($node->filterXpath('//div/span[1]/meta[1]/@content')->text());
        // dump($node->filterXpath('//span[last()]/del/text()')->text());
        die;
    });

});
