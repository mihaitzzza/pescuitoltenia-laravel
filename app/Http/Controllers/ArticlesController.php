<?php

namespace App\Http\Controllers;

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
        return response()->json([
            'articles' => Article::all()->load('author')
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

        // Store cover image.
        $file = $request->file('cover');;
        $path = $file->store('uploads/articles/covers', ['disk' => 'public']);

        // Store content in text file.
        $uniqueContentName = uniqid();
        $contentPath = 'uploads/articles/content/'.$uniqueContentName.'.txt';
        Storage::disk('public')
            ->put($contentPath, $request->get('content'));

        // Create article
        $article = new Article();
        $article->title = $request->get('title');
        $article->cover = $path;
        $article->content = $contentPath;
        $article->author_id = Auth::user()->id;
        $article->save();

        /*$ptn = '/<img src=\"([^\"]+)\">/';
        preg_match_all($ptn, $request->get('content'), $matches);

        foreach ($matches[1] as $base64Image)
        {
            Log::info($base64Image);
        }*/

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
            $path = $coverImage->store('uploads/articles/covers', ['disk' => 'public']);
        }

        // Store content in text file.
        $uniqueContentName = uniqid();
        $contentPath = 'uploads/articles/content/'.$uniqueContentName.'.txt';
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
}
