<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\UserDocumentRequest;
use App\Http\Resources\UserDocumentResource;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'type' => ['nullable', 'integer', 'exists:document_types,id'],
            'within' => ['nullable', 'integer', 'in:7,30,60,90'],
        ]);

        $documents = UserDocument::query()
            ->whereHas('user')
            ->with(['user', 'type'])
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('document_type_id', $type))
            ->when($filters['within'] ?? null, fn ($q, $days) => $q->expiringWithin((int) $days))
            ->orderBy('valid_until')
            ->get();

        return Inertia::render('manager/documents/index', [
            'documents' => UserDocumentResource::collection($documents),
            'types' => DocumentType::withCount('documents')->orderBy('name')->get(),
            'filters' => (object) $filters,
        ]);
    }

    public function store(UserDocumentRequest $request, User $user): RedirectResponse
    {
        $document = UserDocument::firstOrNew([
            'user_id' => $user->id,
            'document_type_id' => $request->validated('document_type_id'),
        ]);

        $document->fill($request->safe()->only(['valid_until', 'note']));
        $document->reminded_at = null;
        $document->save();

        return back()->with('success', 'Dokument zapisany.');
    }

    public function destroy(UserDocument $document): RedirectResponse
    {
        $document->delete();

        return back()->with('success', 'Dokument usunięty.');
    }

    public function storeType(Request $request): RedirectResponse
    {
        DocumentType::create($request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:document_types,name'],
            'remind_days_before' => ['required', 'integer', 'min:1', 'max:365'],
        ]));

        return back()->with('success', 'Dodano typ dokumentu.');
    }
}
