<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use App\Models\User;

class AuditController extends Controller
{
    /**
     * Display a listing of the audits.
     */
    public function index()
    {
        $audits = Audit::with('user')->latest()->paginate(20);
        
        return response()->json([
            'audits' => $audits
        ]);
    }

    /**
     * Display a listing of the audits for a specific model.
     */
    public function modelAudits(Request $request, $model, $id)
    {
        $modelClass = "App\\Models\\" . ucfirst($model);
        
        if (!class_exists($modelClass)) {
            return response()->json([
                'error' => 'Model not found'
            ], 404);
        }
        
        $modelInstance = $modelClass::find($id);
        
        if (!$modelInstance) {
            return response()->json([
                'error' => 'Record not found'
            ], 404);
        }
        
        $audits = $modelInstance->audits()->with('user')->latest()->get();
        
        return response()->json([
            'audits' => $audits
        ]);
    }

    /**
     * Display a listing of the audits for the authenticated user.
     */
    public function userAudits(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }
        
        $audits = Audit::where('user_id', $user->id)->latest()->paginate(20);
        
        return response()->json([
            'audits' => $audits
        ]);
    }
} 