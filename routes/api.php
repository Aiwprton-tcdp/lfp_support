<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\TicketsController;
use App\Http\Controllers\CouponsController;
use App\Http\Controllers\WebSocketController;
use Inertia\Inertia;
use Illuminate\Foundation\Application;

Route::post('/install', [InstallController::class, 'Install'])->name('install');
Route::any('/', [TicketsController::class, 'Start']);

Route::post('/tickets', [TicketsController::class, 'Get'])->name('tickets');
Route::post('/ticket/messages', [TicketsController::class, 'GetMessages']);
Route::post('/ticket/message/add', [TicketsController::class, 'AddMessage']);
Route::post('/ticket/file/add', [TicketsController::class, 'AddFile']);
Route::post('/ticket/message_templates', [TicketsController::class, 'GetMessageTemplates']);

Route::post('/responsives/get', [TicketsController::class, 'GetResponsives']);
Route::post('/ticket/transfer', [TicketsController::class, 'Transfer'])->name('tickets.transfer');
Route::post('/add_responsives/get', [TicketsController::class, 'GetResponsivesForAdd']);
Route::post('/ticket/add_responsive', [TicketsController::class, 'AddResponsive']);
Route::post('/ticket/remove_responsive', [TicketsController::class, 'RemoveResponsive']);
Route::post('/ticket/close', [TicketsController::class, 'Close'])->name('ticket.close');

Route::get('/reasons/get', [TicketsController::class, 'GetReasons']);
Route::post('/hints/get', [TicketsController::class, 'GetHints']);
Route::post('/ticket/new', [TicketsController::class, 'New'])->name('ticket.new');
Route::post('/reasons/add', [TicketsController::class, 'AddReason']);
Route::post('/hints/edit', [TicketsController::class, 'EditHints']);

Route::post('/coupons', [CouponsController::class, 'Get'])->name('coupons');
Route::post('/coupon/new', [CouponsController::class, 'New'])->name('coupon.new');
Route::post('/coupon/close', [CouponsController::class, 'Close'])->name('coupon.close');
Route::post('/coupon/repair', [CouponsController::class, 'Repair'])->name('coupon.repair');

Route::post('/centrifuge/subscribe', [WebSocketController::class, 'Subscribe']);
Route::post('/websocket/refresh', [WebSocketController::class, 'Refresh']);