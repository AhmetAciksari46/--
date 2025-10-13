<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use Illuminate\Http\Request;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Hash;

class PackageController extends Controller
{
    use ApiResponser;
    //TODO : packages ve package id liyi ayrı bir route ekle ana sayfadan görünmesi ve üyelerin görebilmesi için
    public function getpackages()
    {

        $packages = Package::all();
        return $this->successResponse($packages);
    }


    public function publicIndex()
    {
        // Sadece aktif ve görünür olan paketleri getir
        $packages = Package::where('is_active', true)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();

        return $this->successResponse($packages, 'Paket listesi başarıyla getirildi.');
    }

    public function publicShow($id)
    {
        // Sadece aktif ve görünür paketi gösterebilir
        $package = Package::where('is_active', true)
            ->where('is_visible', true)
            ->findOrFail($id);

        return response()->json([
            'message' => 'Paket detayları getirildi.',
            'data' => $package
        ]);
        return $this->successResponse($package, 'Paket detayları getirildi.');
    }



    public function getpackagebyid($id)
    {
        if (!auth()->user()->can('package.view')) {
            return $this->errorResponse('unauthorized', 403);
        }
        $this->authorizeRole(['admin']);
        return Package::with('subscriptions')->findOrFail($id);

        $package = Package::findOrFail($id);
        return $this->successResponse($package);
    }

    public function create(CreatePackageRequest $request)
    {
        $package = Package::create($request->validated());
        return $this->successResponse($package->fresh(), 'Paket başarıyla oluşturuldu.');
    }
    public function update(UpdatePackageRequest $request, $id)
    {
        $package = Package::findOrFail($id);

        $package->update($request->validated());
        $message = "Paket başarıyla güncellendi.";
        return $this->successResponse($package->fresh(), $message);
    }
    public function delete($id)
    {
        if (!auth()->user()->can('package.delete')) {
            return $this->errorResponse('unauthorized', 403);
        }
        $package = Package::findOrFail($id);
        $package->delete();

        return $this->successResponse('Paket başarıyla silindi.');
    }
}
