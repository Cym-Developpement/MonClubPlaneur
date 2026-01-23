<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\wiki;

class WikiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function readPage(Request $request)
    {
        if ($request->page == 'Accueil') {
            $id = wiki::where('pageName', $request->page)->first()->id;
        } else {
            $id = intval($request->page);
        }

        $page =  wiki::withTrashed()->find($id);

        if (!is_null($page) && !$page->active && !isset($request->revision)) {
            return redirect($page->last_url);
        }

        return (isset($request->print)) ? view('wiki.print', ['page' => $page]) : view('wiki.page', ['page' => $page]) ;
    }

    public function newPage(Request $request)
    {
        $new = wiki::newPage($request->current, $request->name);
        return redirect($new->url);
    }

    public function deletePage(Request $request)
    {
        $page = wiki::find($request->page);
        $parent = $page->parent_page;
        if ($page->is_deletable) {
            $page->delete();
        } else {
            return redirect($page->url);
        }
        return redirect($parent->url);
    }

    public function updatePage(Request $request)
    {
        $current = wiki::find($request->page);
        if (!$current->active) {
            return redirect($current->last_url);
        }
        
        return redirect($current->updateContent($request->content));
    }

    public function restore(Request $request)
    {
        return redirect(wiki::withTrashed()->find($request->page)->restoreRevision());
    }

    public function password(Request $request)
    {
        return wiki::decrypt($request->password, $request->encrypted);
    }
}
