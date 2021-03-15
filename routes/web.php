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

Auth::routes(['verify' => true]);

Route::group(['middleware' => ['auth', 'validateBackHistory', 'verified']], function(){
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('/upload-configs', 'HomeController@index')->name('config.upload');

    Route::get('/scrape-data', 'HomeController@index')->name('scrape.data');

    Route::get('/scrape-data/view-scraper/{scarperName}', 'HomeController@index')->name('scrape.data');

    Route::get('/403', 'HomeController@index')->name('scrape.data');
});

Route::group(['middleware' => ['auth', 'validateBackHistory', 'checkIfAdmin', 'verified']], function(){
    Route::get('/users/management', 'HomeController@index')->name('users.management');
});

Route::get('/testscraper', function() {

    $crawler = Goutte::request('GET', 'https://gb.benetton.com/sweater-in-shetland-wool-dark-green-103MK1N24_570.html');

    // dump($crawler->html());

    $crawler->filter("#size-1-pdp .btn-outline-secondary:not([disabled])")->each(function ($node) {

        dump($node->text());
    //     // echo $node->html();
        // dump($node->filterXpath('//div/div/div/div/div/div[4]/div[2]/div[1]/div/text()')->text());
        // dump($node->filterXpath('//div[4]/div[3]/div/ul/li/button/text()')->attr('href'));
    //     dump($node->filter('.sales > .value')->text());
    //     dump($node->filter('.strike-through span')->attr('content'));
    //     // die;
    });

});
