<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    //afficher la liste des candidatures
    //NB: application(anglais) === candidatures
    public function index(Request $request){

        $query = $request->user()->applications();

        if($request->filled('status')){
            $query->where('status', $request->status);
        }

        if ($request->filled('platform')){
            $query->where('platform', $request->platform);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $query->orderBy('applied_at', 'desc');
        return response()->json($query->paginate(10));
    }
    
    //ajouter une candidature
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company'      => 'required|string|max:255',
            'position'     => 'required|string|max:255',
            'platform'     => 'required|string|max:100',
            'applied_at'   => 'required|date',
            'status'       => 'in:envoyee,relance,entretien,refus,acceptee',
            'follow_up_at' => 'nullable|date|after:applied_at',
            'url'          => 'nullable|url|max:500',
            'notes'        => 'nullable|string|max:2000',
            'location'     => 'nullable|string|max:255',
            'salary'       => 'nullable|integer|min:0',
        ]);

        // Si pas de date de relance fournie, on met J+7 automatiquement
        if (empty($validated['follow_up_at'])) {
            $validated['follow_up_at'] = now()->addDays(7)->toDateString();
        }

        $application = $request->user()->applications()->create($validated);

        return response()->json($application, 201);
    }

    //afficher une candidature spécifique
    public function show(Request $request, Application $application)
    {
        if($application->user_id !== $request->user()->id){
            return response()->json([
                'message'=> 'Accès interdit'
            ],403);
        }

        return response()->json($application);
    }

    //modifier une candidature
    public function update(Request $request, Application $application)
    {
        if($application->user_id !== $request->user()->id){
            return response()->json([
                'message'=> 'Accès interdit'
            ],403);
        }

        $validated = $request->validate([
            'company'      => 'sometimes|string|max:255',
            'position'     => 'sometimes|string|max:255',
            'platform'     => 'sometimes|string|max:100',
            'applied_at'   => 'sometimes|date',
            'status'       => 'sometimes|in:envoyee,relance,entretien,refus,acceptee',
            'follow_up_at' => 'nullable|date',
            'url'          => 'nullable|url|max:500',
            'notes'        => 'nullable|string|max:2000',
            'location'     => 'nullable|string|max:255',
            'salary'       => 'nullable|integer|min:0',
        ]);

        $application->update($validated);

        return response()->json($application);
    }

    //supprimer la candidature
    public function destroy(Request $request, Application $application)
    {
        if($application->user_id !== $request->user()->id){
            return response()->json([
                'message'=> 'Accès interdit'
            ],403);
        }

        $application->delete();

        return response()->json(null,204);
    }

    //mettre à jour le statut
    public function updateStatus(Request $request, Application $application){
        if($application->user_id !== $request->user()->id){
            return response()->json([
                'message'=> 'Accès interdit'
            ],403);
        }

        $request->validate(['status'=> 'required|in:envoyee,relance,entretien,refus,acceptee']);

        $application->update(['status'=> $request->status]);

        return response()->json($application);
    }

    //afficher les stats
    public function stats(Request $request)
    {
        $apps  = $request->user()->applications();
        $total = $apps->clone()->count();

        $byStatus = $apps->clone()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $responses = ($byStatus['relance'] ?? 0)
                   + ($byStatus['entretien'] ?? 0)
                   + ($byStatus['refus'] ?? 0)
                   + ($byStatus['acceptee'] ?? 0);

        $weekly = $apps->clone()
            ->selectRaw('YEARWEEK(applied_at) as yearweek, COUNT(*) as count')
            ->where('applied_at', '>=', now()->subWeeks(8))
            ->groupBy('yearweek')
            ->orderBy('yearweek')
            ->get();

        return response()->json([
            'total'           => $total,
            'by_status'       => $byStatus,
            'response_rate'   => $total > 0 ? round(($responses / $total) * 100) : 0,
            'needs_follow_up' => $apps->clone()->needsFollowUp()->count(),
            'weekly'          => $weekly,
        ]);
    }

    //recupérer les relances
    public function followUps(Request $request)
    {
        $apps = $request->user()
            ->applications()
            ->needsFollowUp()
            ->orderBy('follow_up_at')
            ->get();

        return response()->json($apps);
    }
}
