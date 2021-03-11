<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WeijuController;
use App\Http\Livewire\Weixin;
use App\Http\Livewire\WebChat;
use App\Http\Livewire\WechatAutoReply;
use App\Http\Livewire\WechatContent;
use App\Http\Livewire\WechatBotContact;

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

Route::post('/webhook/weiju', [WeijuController::class, 'listen'])->name('webhook.weiju');

Route::post('/webhook/test', [WeijuController::class, 'test'])->name('webhook.test');

Route::group([
    'middleware' => ['auth:sanctum', 'verified'],
    'prefix'=>'channels/wechat', 
    'as'=>'channel.wechat.',
    ], function () {
        Route::get('/', Weixin::class)->name('weixin');
        Route::get('/webchat', WebChat::class)->name('webchat');
        Route::get('/content', WechatContent::class)->name('content');
        Route::get('/autoreply', WechatAutoReply::class)->name('autoreply');
        Route::get('/contact', WechatBotContact::class)->name('contact');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');
