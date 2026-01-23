<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\todolist;
use App\Models\User;

class TodolistController extends Controller
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

    /**
     * Afficher la liste des tâches
     */
    public function index()
    {
        $tasks = todolist::with(['creator', 'assignee', 'completer'])
                        ->orderBy('created_at', 'desc')
                        ->get();
        
        $users = User::where('state', 1)
                    ->orderBy('name')
                    ->get();
        
        return view('todolist.index', compact('tasks', 'users'));
    }

    /**
     * Créer une nouvelle tâche
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date|after:today'
        ]);

        todolist::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'status' => 'pending'
        ]);

        return redirect()->route('todolist.index')->with('success', 'Tâche créée avec succès !');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $task = todolist::findOrFail($id);
        $users = User::where('state', 1)
                    ->orderBy('name')
                    ->get();
        
        return view('todolist.edit', compact('task', 'users'));
    }

    /**
     * Mettre à jour une tâche
     */
    public function update(Request $request, $id)
    {
        $task = todolist::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'status' => $request->status
        ]);

        // Si la tâche est marquée comme complétée, enregistrer qui l'a complétée
        if ($request->status === 'completed' && $task->status !== 'completed') {
            $task->markAsCompleted(Auth::id());
        }

        return redirect()->route('todolist.index')->with('success', 'Tâche mise à jour avec succès !');
    }

    /**
     * Supprimer une tâche
     */
    public function destroy($id)
    {
        $task = todolist::findOrFail($id);
        
        // Seul le créateur ou un admin peut supprimer une tâche
        if ($task->created_by !== Auth::id() && !Auth::user()->can('admin')) {
            return redirect()->back()->with('error', 'Vous n\'avez pas les droits pour supprimer cette tâche.');
        }
        
        $task->delete();
        
        return redirect()->route('todolist.index')->with('success', 'Tâche supprimée avec succès !');
    }

    /**
     * Marquer une tâche comme complétée
     */
    public function complete($id)
    {
        $task = todolist::findOrFail($id);
        $task->markAsCompleted(Auth::id());
        
        return redirect()->back()->with('success', 'Tâche marquée comme complétée !');
    }

    /**
     * Marquer une tâche comme en cours
     */
    public function inProgress($id)
    {
        $task = todolist::findOrFail($id);
        $task->markAsInProgress();
        
        return redirect()->back()->with('success', 'Tâche marquée comme en cours !');
    }

    /**
     * Marquer une tâche comme en attente
     */
    public function pending($id)
    {
        $task = todolist::findOrFail($id);
        $task->markAsPending();
        
        return redirect()->back()->with('success', 'Tâche marquée comme en attente !');
    }
}