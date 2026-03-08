<?php

namespace App\Http\Controllers;

use App\Mail\BulkMailing;
use App\Models\MailingLog;
use App\Models\parametre;
use App\Models\transaction;
use App\Models\User;
use App\Models\usersAttributes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailingController extends Controller
{
    public function index()
    {
        $history = MailingLog::with('sentBy')->latest()->limit(50)->get();
        return view('admin.mailing', compact('history'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:200',
            'body'    => 'required|string',
            'filter'  => 'required|string',
        ]);

        $users = $this->resolveUsers($request->input('filter'));

        $excludeTechnique = $request->boolean('exclude_technique');
        if ($excludeTechnique) {
            $allAttributes = $this->loadAttributes();
            $users = $users->filter(function ($user) use ($allAttributes) {
                return !isset($allAttributes[$user->id]) || !in_array('user:technique', $allAttributes[$user->id]);
            })->values();
        }

        $nomCourt   = parametre::getValue('club-nom_court', 'MonClub');
        $nomComplet = parametre::getValue('club-nom_complet', '');
        $logo       = parametre::getValue('club-logo', '');
        $emailClub  = parametre::getValue('club-email', '');

        foreach ($users as $user) {
            Mail::to($user->email)->send(new BulkMailing(
                recipientName: $user->name,
                mailSubject:   $request->input('subject'),
                body:          $request->input('body'),
                nomCourt:      $nomCourt,
                nomComplet:    $nomComplet,
                logo:          $logo,
                emailClub:     $emailClub,
            ));
        }

        MailingLog::create([
            'sent_by'         => auth()->id(),
            'subject'         => $request->input('subject'),
            'body'            => $request->input('body'),
            'filter'          => $request->input('filter'),
            'recipient_count' => $users->count(),
        ]);

        return redirect('/admin/mailing')->with('success', 'Email envoyé à ' . $users->count() . ' destinataire(s).');
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'subject'    => 'required|string|max:200',
            'body'       => 'required|string',
            'test_email' => 'required|email',
        ]);

        $nomCourt   = parametre::getValue('club-nom_court', 'MonClub');
        $nomComplet = parametre::getValue('club-nom_complet', '');
        $logo       = parametre::getValue('club-logo', '');
        $emailClub  = parametre::getValue('club-email', '');

        Mail::to($request->input('test_email'))->send(new BulkMailing(
            recipientName: 'Test',
            mailSubject:   $request->input('subject'),
            body:          $request->input('body'),
            nomCourt:      $nomCourt,
            nomComplet:    $nomComplet,
            logo:          $logo,
            emailClub:     $emailClub,
        ));

        MailingLog::create([
            'sent_by'         => auth()->id(),
            'subject'         => $request->input('subject'),
            'body'            => $request->input('body'),
            'filter'          => 'test',
            'recipient_count' => 1,
            'test_email'      => $request->input('test_email'),
        ]);

        return redirect('/admin/mailing')->with('success', 'Email de test envoyé à ' . $request->input('test_email') . '.');
    }

    private function resolveUsers(string $filter)
    {
        if (str_starts_with($filter, 'year:')) {
            $year    = (int) substr($filter, 5);
            $userIds = transaction::where('name', 'Cotisation ' . $year)->pluck('idUser')->unique();
            return User::whereIn('id', $userIds)->orderBy('name')->get();
        }

        if ($filter === 'all') {
            return User::where('id', '>', 0)->orderBy('name')->get();
        }

        return User::where('state', 1)->orderBy('name')->get();
    }

    private function loadAttributes(): array
    {
        $allAttributes = [];
        foreach (usersAttributes::all() as $attr) {
            $allAttributes[$attr->userId][] = $attr->attributeName;
        }
        return $allAttributes;
    }
}
