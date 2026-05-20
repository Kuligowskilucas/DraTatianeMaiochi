<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalRecord\StoreAttachmentRequest;
use App\Http\Resources\MedicalRecordEntryAttachmentResource;
use App\Models\MedicalRecordEntry;
use App\Models\MedicalRecordEntryAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AttachmentController extends Controller
{
    use AuthorizesRequests;
    /**
     * GET /api/medical-record-entries/{entry}/attachments
     */
    public function index(MedicalRecordEntry $entry)
    {
        $this->authorize('viewAnyForEntry', [MedicalRecordEntryAttachment::class, $entry]);

        $attachments = $entry->attachments()
            ->with('uploadedBy')
            ->get();

        return MedicalRecordEntryAttachmentResource::collection($attachments);
    }

    /**
     * POST /api/medical-record-entries/{entry}/attachments
     * multipart/form-data, campo "file".
     */
    public function store(StoreAttachmentRequest $request, MedicalRecordEntry $entry)
    {
        $this->authorize('createForEntry', [MedicalRecordEntryAttachment::class, $entry]);

        $file = $request->file('file');
        $mime = $file->getMimeType();

        $ext = match ($mime) {
            'application/pdf' => 'pdf',
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
        };

        $uuid = (string) Str::uuid();

        $path = $file->storeAs(
            "medical-record-attachments/{$entry->id}",
            "{$uuid}.{$ext}",
            'private'
        );

        $attachment = $entry->attachments()->create([
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime'          => $mime,
            'size'          => $file->getSize(),
            'uploaded_by'   => $request->user()->id,
        ]);

        $attachment->load('uploadedBy');

        return (new MedicalRecordEntryAttachmentResource($attachment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * DELETE /api/medical-record-entry-attachments/{attachment}
     * Soft delete (arquivo no disco permanece — limpeza física é outro processo).
     */
    public function destroy(MedicalRecordEntryAttachment $attachment)
    {
        $this->authorize('delete', $attachment);
        $attachment->delete();
        return response()->json(null, 204);
    }

    /**
     * GET /api/medical-record-entry-attachments/{attachment}/download
     * Stream com auth — nunca exposto via URL pública.
     */
    public function download(MedicalRecordEntryAttachment $attachment)
    {
        $this->authorize('view', $attachment);
    
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('private');
    
        if (! $disk->exists($attachment->file_path)) {
            abort(404, 'Arquivo não encontrado no servidor.');
        }
    
        return $disk->download(
            $attachment->file_path,
            $attachment->original_name,
            ['Content-Type' => $attachment->mime]
        );
    }
}