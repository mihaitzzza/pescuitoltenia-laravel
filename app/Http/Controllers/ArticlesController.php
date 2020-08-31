<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Article;
use App\User;

class ArticlesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = User::findOrFail(Auth::user()->id);
        $articles = Article::with('author')->orderBy('created_at', 'desc');

        if (!$user->isAdmin())
        {
            $articles->where('author_id', $user->id);
        }

        return response()->json([
            'articles' => $articles->get()
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate fields.
        $request->validate([
            'title' => 'required|max:255',
            'cover' => 'required|image',
            'content' => 'required|string'
        ]);
        $shouldPublish = filter_var($request->get('publishArticle'), FILTER_VALIDATE_BOOLEAN);

        // Store cover image.
        $file = $request->file('cover');;
        $path = $file->store('articles/covers', ['disk' => 'poPublic']);

        // Store content in text file.
        $uniqueContentName = uniqid();
        $contentPath = 'articles/content/'.$uniqueContentName.'.txt';
        Storage::disk('public')
            ->put($contentPath, $request->get('content'));

        // Create article
        $article = new Article();
        $article->title = $request->get('title');
        $article->cover = $path;
        $article->content = $contentPath;
        $article->author_id = Auth::user()->id;
        $article->published_at = $shouldPublish ? Carbon::now() : null;
        $article->save();

        return response()->json([
            'article' => $article->load('author')
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $article = Article::findOrFail($id);
        $content = Storage::disk('public')->get($article->content);
        $article->content = $content;

        return response()->json([
            'article' => $article
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate fields.
        Log::info($request);
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required|string'
        ]);

        // Store cover image.
        $coverImage = $request->file('cover');
        $path = null;
        if ($coverImage)
        {
            $path = $coverImage->store('articles/covers', ['disk' => 'poPublic']);
        }

        // Store content in text file.
        $uniqueContentName = uniqid();
        $contentPath = 'articles/content/'.$uniqueContentName.'.txt';
        Storage::disk('public')
            ->put($contentPath, $request->get('content'));

        // Update article
        $article = Article::findOrFail($id);
        $article->title = $request->get('title');
        if ($path)
        {
            $article->cover = $path;
        }
        $article->content = $contentPath;
        $article->save();

        return response()->json([
            'article' => $article
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Article::findOrFail($id)->delete();

        return response()->json([
            'ok' => true
        ], 200);
    }

    public function getAll()
    {
        $articles = Article::whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->get();

        foreach($articles as $article)
        {
            $content = Storage::disk('public')->get($article->content);
            $article->content = strip_tags($content);
        }

        return response()->json([
            'articles' => $articles
        ], 200);
    }

    public function publish($id)
    {
        $article = Article::findOrFail($id);

        if ($article->published_at)
        {
            return response()->json([
                'message' => 'This article was already published.'
            ], 404);
        }

        $published_at = Carbon::now();
        $article->published_at = $published_at;
        $article->save();

        return response()->json([
            'ok' => true,
            'published_at' => $published_at
        ], 200);
    }

    public function getOne($id)
    {
        $article = Article::findOrFail($id);

        if (!$article->published_at)
        {
            return response()->json([
                'message' => 'This article is not published.'
            ], 403);
        }

        $content = Storage::disk('public')->get($article->content);
        $article->content = $content;
        $article->description = strip_tags($content);

        return response()->json([
            'article' => $article->load('author')
        ], 200);
    }
}
