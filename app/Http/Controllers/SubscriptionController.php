<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
   public function index()
    {
        $this->authorizeRole(['superadmin', 'manager']);
        return Subscription::with('package')->get();
    }

    public function show($id)
    {
        $this->authorizeRole(['superadmin', 'manager']);
        return Subscription::with('package')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['superadmin']);
        $subscription = Subscription::create($request->all());
        return response()->json($subscription, 201);
    }

    public function checkActive($schoolId)
    {
        $subscription = Subscription::where('school_id', $schoolId)
            ->with('package')
            ->latest('end_date')
            ->first();

        if (!$subscription || !$subscription->isActive()) {
            return response()->json(['status' => 'inactive'], 403);
        }

        return response()->json([
            'status' => 'active',
            'package' => $subscription->package->name,
            'expires_at' => $subscription->end_date,
        ]);
    }

}
