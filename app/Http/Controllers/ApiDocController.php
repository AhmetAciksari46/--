<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Laravel 12 LMS API",
 *      description="bridge digital LMS projesi için REST API dokümantasyonu",
 *      @OA\Contact(
 *          email="ahmet@aciksari.net"
 *      ),
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT"
 * )
 */
class ApiDocController extends Controller
{
    //
}
