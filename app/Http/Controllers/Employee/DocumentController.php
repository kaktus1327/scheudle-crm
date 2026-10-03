<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserDocumentResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $documents = $request->user()->documents()->with('type')->orderBy('valid_until')->get();

        return Inertia::render('employee/documents/index', [
            'documents' => UserDocumentResource::collection($documents),
        ]);
    }
}
