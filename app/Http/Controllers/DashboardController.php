<?php

namespace App\Http\Controllers;

use App\Article;
use App\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getData()
    {
        $users = User::orderBy('created_at', 'desc');
        $articles = Article::orderBy('created_at', 'desc');
        $usersCount = $users->count();
        $articlesCount = $articles->count();

        $userLastCreatedAt = null;
        $latestUser = $users->first();
        if ($latestUser)
        {
            $userLastCreatedAt = $users->first()->created_at;
        }

        $articleLastCreatedAt = null;
        $latestArticle = $articles->first();
        if ($latestArticle)
        {
            $articleLastCreatedAt = $articles->first()->created_at;
        }

        return response()->json([
            'usersCount' => $usersCount,
            'lastCreatedUserDate' => $userLastCreatedAt,
            'articlesCount' => $articlesCount,
            'lastCreatedArticleDate' => $articleLastCreatedAt,
        ], 200);
    }
}
