<?php

namespace App\Http\Controllers;

use App\Models\MessageAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{

    public function __invoke(MessageAttachment $attachment): StreamedResponse
    {
        $this->authorize('download', $attachment);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }
}
