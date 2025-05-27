<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register',[AuthController::class,'register']);
    Route::post('login',[AuthController::class,'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout',[AuthController::class,'logout']);
    });
});
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('profile')->group(function () {
        Route::post('update/{profile}',[ProfileController::class,'update']);
        Route::get('show/{profile}',[ProfileController::class,'show']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
   Route::prefix('posts')->group(function () {
       Route::post('create',[PostController::class,'store']);
       Route::get('list',[PostController::class,'index']);
       Route::get('show/{post}',[PostController::class,'show']);
       Route::post('update/{post}',[PostController::class,'update']);
       Route::delete('delete/{post}',[PostController::class,'destroy']);
   });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('comments')->group(function () {
        Route::post('create/{post}',[CommentController::class,'addComment']);
        Route::get('list/{post}',[CommentController::class,'getComments']);
        Route::patch('update/{comment}',[CommentController::class,'updateComment']);
        Route::delete('delete/{comment}',[CommentController::class,'deleteComment']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('likes')->group(function () {
        Route::post('create/like/post/{post}',[LikeController::class,'toggleLikeToPost']);
        Route::post('create/like/comment/{comment}',[LikeController::class,'toggleLikeToComment']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/follow/{user}', [FollowController::class, 'Follow']);
    Route::get('/followers/{user}', [FollowController::class, 'MyFollowers']);
    Route::get('/me/following', [FollowController::class, 'MyFollowing']);
    Route::get('/feed/for-you', [FeedController::class, 'PersonalizedFeed']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations', [ConversationController::class, 'index']);

    Route::post('/conversations/{conversation}/messages/', [MessageController::class, 'send']);
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'fetch']);
    Route::post('/conversations/{conversation}/read', [MessageController::class, 'markAsRead']);
});








