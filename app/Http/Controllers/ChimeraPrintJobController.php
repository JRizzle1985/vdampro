<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChimeraPrintJob;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ChimeraPrintJobController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('superuser');

        $query = ChimeraPrintJob::with(['user', 'assets'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('delivery_method')) {
            $query->where('delivery_method', $request->input('delivery_method'));
        }

        $jobs = $query->paginate(25);

        return view('chimera-print-jobs.index', compact('jobs'));
    }

    public function show(ChimeraPrintJob $chimeraPrintJob): View
    {
        $this->authorize('superuser');

        $chimeraPrintJob->load(['user', 'assets.company']);

        return view('chimera-print-jobs.show', compact('chimeraPrintJob'));
    }
}
