<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagerProfile;
use App\Models\SchoolStudentProfile;
use App\Models\TeacherProfile;
use App\Traits\ApiResponser;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Profile\ManagerUpdateProfileSettingRequest;

class ManagerProfileController extends Controller
{

    use ApiResponser;

    public function getprofilesettings(Request $request)
    {
        $user = Auth::user();
        $profile = ManagerProfile::where('user_id', $user->id)
            ->with('user')
            ->firstOrFail();

        return $this->successResponse($profile);
        //return $this->successResponse($profile, __('api.profile_fetched'));

    }
    public function updateprofilesettings(ManagerUpdateProfileSettingRequest $request)
    {
        $user = Auth::user();
        $profile = ManagerProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['payment_reminder' => false]
        );
        $profile->fill($request->validated());
        $profile->save();
        return $this->successResponse($profile->fresh(), __('api.profile_fetched'));
    }




    //getbyid gibi ->manager kullanacak
    public function show($user_id)
    {
        $this->authorizeRole(['admin', 'manager', 'teacher', 'student']);
        $profile = SchoolStudentProfile::where('user_id', $user_id)
            ->with('user') // Profile ait user bilgilerini de yükle
            ->firstOrFail();
        return response()->json($profile);
    }
}
