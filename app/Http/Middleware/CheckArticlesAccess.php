<?php

namespace App\Http\Middleware;

use Log;
use App\Article;
use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckArticlesAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $articleId = $request->route('article');

        if (!$articleId)
        {
            if ($request->user()->isAdminOrStaff())
            {
                return $next($request);
            }

            return response()->json([
                'message' => "You don't have access to this resource."
            ], 403);
        }

        $article = Article::findOrFail($articleId);
        $user = User::find(Auth::user()->id)->first();
        $authorId = $article->author()->first()->id;

        if ($user->isAdmin() || $authorId == $user->id) {
            return $next($request);
        }

        return response()->json([
            'message' => "You don't have access to this resource."
        ], 403);
    }
}
