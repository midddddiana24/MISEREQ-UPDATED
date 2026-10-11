<?php

namespace App\Actions;

use App\DataTransferObjects\ServiceRequestCreationData;
use App\Models\ServiceRequest;
use App\Services\FileUploadService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\UploadedFile;

final class CreateServiceRequest
{
    public function __construct(
        private readonly FileUploadService $fileUploads,
        private readonly DatabaseManager $database,
    ) {}

    public function execute(ServiceRequestCreationData $data): ServiceRequest
    {
        return $this->database->transaction(function () use ($data): ServiceRequest {
            $attachments = array_map(
                fn(UploadedFile $file): array => $this->storeAttachment($file),
                $data->attachments
            );

            $request = ServiceRequest::createWithUniqueControlNumber(
                $data->toModelAttributes($attachments[0]['path'] ?? null)
            );

            foreach ($attachments as $attachment) {
                $request->attachments()->create($attachment + [
                    'uploaded_by' => $data->createdBy ?? $data->userId,
                ]);
            }

            return $request;
        });
    }

    /**
     * @return array{path: string, original_name: string, mime_type: ?string, size: int|null}
     */
    private function storeAttachment(UploadedFile $file): array
    {
        $stored = $this->fileUploads->upload($file, 'request-attachments');

        return [
            'path' => $stored['path'],
            'original_name' => $stored['original_filename'],
            'mime_type' => $file->getMimeType(),
            'size' => $stored['size'] ?? $file->getSize(),
        ];
    }
}
