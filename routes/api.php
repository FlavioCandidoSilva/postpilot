<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AIContentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PromptTemplateController;
use App\Http\Controllers\QueueController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas de autenticação
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Usuário atual
    Route::get('/user', [AuthController::class, 'user']);
    
    // Posts
    Route::get('/posts', [PostController::class, 'apiIndex']);
    Route::get('/posts/{post}', [PostController::class, 'apiShow']);
    Route::post('/posts', [PostController::class, 'apiStore']);
    Route::put('/posts/{post}', [PostController::class, 'apiUpdate']);
    Route::delete('/posts/{post}', [PostController::class, 'apiDestroy']);
    Route::get('/posts/calendar/events', [PostController::class, 'apiCalendarEvents']);
    
    // Categorias
    Route::get('/categories', [CategoryController::class, 'apiIndex']);
    Route::post('/categories', [CategoryController::class, 'apiStore']);
    Route::put('/categories/{category}', [CategoryController::class, 'apiUpdate']);
    Route::delete('/categories/{category}', [CategoryController::class, 'apiDestroy']);
    
    // Tags
    Route::get('/tags', [TagController::class, 'apiIndex']);
    Route::post('/tags', [TagController::class, 'apiStore']);
    Route::put('/tags/{tag}', [TagController::class, 'apiUpdate']);
    Route::delete('/tags/{tag}', [TagController::class, 'apiDestroy']);
    
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'apiStats']);
    Route::get('/dashboard/upcoming-posts', [DashboardController::class, 'apiUpcomingPosts']);
    Route::get('/dashboard/engagement', [DashboardController::class, 'apiEngagementData']);
    
    // IA - Requer plano Pro para algumas funcionalidades
    Route::post('/ai/generate', [PostController::class, 'generateWithAI'])->middleware('subscription:pro');
    Route::post('/ai/suggest-timing', [PostController::class, 'suggestTiming'])->middleware('subscription:pro');
    
    // Templates de Prompts
    Route::get('/prompts', [PromptTemplateController::class, 'apiIndex']);
    Route::get('/prompts/{promptTemplate}', [PromptTemplateController::class, 'apiShow']);
    Route::post('/prompts', [PromptTemplateController::class, 'apiStore']);
    Route::put('/prompts/{promptTemplate}', [PromptTemplateController::class, 'apiUpdate']);
    Route::delete('/prompts/{promptTemplate}', [PromptTemplateController::class, 'apiDestroy']);
    Route::post('/prompts/{promptTemplate}/generate', [PromptTemplateController::class, 'apiGenerate']);
    
    // Fila de Postagens
    Route::get('/queue', [QueueController::class, 'apiIndex']);
    Route::get('/queue/stats', [QueueController::class, 'apiStats']);
    Route::post('/queue/reorder', [QueueController::class, 'apiReorder']);
    Route::post('/queue/{id}/retry', [QueueController::class, 'apiRetry']);
    Route::post('/queue/{id}/cancel', [QueueController::class, 'apiCancel']);
    Route::post('/queue/{id}/process-now', [QueueController::class, 'apiProcessNow']);
    
    // Assinatura
    Route::get('/subscription/info', [SubscriptionController::class, 'apiInfo']);
    Route::post('/subscription/upgrade', [SubscriptionController::class, 'apiUpgrade']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'apiCancel']);
});
