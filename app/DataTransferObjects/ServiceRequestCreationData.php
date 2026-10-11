<?php

namespace App\DataTransferObjects;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

final readonly class ServiceRequestCreationData
{
    /**
     * @param array<int, string> $services
     * @param array<int, UploadedFile> $attachments
     */
    public function __construct(
        public ?int $userId,
        public ?int $createdBy,
        public bool $adminCreated,
        public string $requesterName,
        public ?string $requesterDesignation,
        public ?string $unitHeadName,
        public ?string $requesterOffice,
        public ?string $office,
        public ?string $phone,
        public array $services,
        public ?string $description,
        public ?string $propertyNo,
        public ?string $category,
        public string $status,
        public ?string $dateOfRequest,
        public ?string $timeOfRequest,
        public array $attachments,
        public ?string $officeLocation = null,
        public ?string $networkOther = null,
        public ?string $ictOther = null,
        public ?string $misOther = null,
    ) {}

    /**
     * @param array<string, mixed> $validated
     * @param array<int, UploadedFile> $attachments
     */
    public static function forUser(User $user, array $validated, array $attachments): self
    {
        $services = array_values($validated['services'] ?? []);

        return new self(
            userId: $user->id,
            createdBy: null,
            adminCreated: false,
            requesterName: $validated['requester_name'] ?? $user->full_name,
            requesterDesignation: $validated['designation'] ?? $user->designation,
            unitHeadName: $validated['unit_head_name'] ?? null,
            requesterOffice: $validated['requester_office'] ?? $user->office,
            office: $validated['office'] ?? $user->office,
            phone: $validated['phone_local'] ?? $user->phone,
            services: $services,
            description: $validated['description'] ?? null,
            propertyNo: $validated['property_no'] ?? null,
            category: $validated['category'] ?? self::inferCategory($services),
            status: 'pending',
            dateOfRequest: $validated['date_request'] ?? null,
            timeOfRequest: $validated['time_request'] ?? null,
            attachments: $attachments,
            officeLocation: $validated['network_specify_location'] ?? null,
            networkOther: $validated['network_other'] ?? null,
            ictOther: $validated['ict_other'] ?? null,
            misOther: $validated['mis_other'] ?? null,
        );
    }

    /**
     * @param array<string, mixed> $validated
     * @param array<int, UploadedFile> $attachments
     */
    public static function forAdmin(int $actorId, array $validated, array $attachments): self
    {
        $services = array_values($validated['services']);

        return new self(
            userId: null,
            createdBy: $actorId,
            adminCreated: true,
            requesterName: $validated['requester_name'],
            requesterDesignation: $validated['requester_designation'] ?? null,
            unitHeadName: $validated['unit_head_name'] ?? null,
            requesterOffice: $validated['requester_office'] ?? null,
            office: $validated['office'] ?? null,
            phone: $validated['phone'] ?? null,
            services: $services,
            description: $validated['description'] ?? null,
            propertyNo: $validated['property_no'] ?? null,
            category: self::inferCategory($services),
            status: 'submitted',
            dateOfRequest: $validated['date_of_request'] ?? null,
            timeOfRequest: $validated['time_of_request'] ?? null,
            attachments: $attachments,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toModelAttributes(?string $attachmentPath): array
    {
        return [
            'user_id' => $this->userId,
            'created_by' => $this->createdBy,
            'is_admin_created' => $this->adminCreated,
            'requester_name' => $this->requesterName,
            'requester_designation' => $this->requesterDesignation,
            'unit_head_name' => $this->unitHeadName,
            'requester_office' => $this->requesterOffice,
            'office' => $this->office,
            'phone' => $this->phone,
            'services' => $this->services,
            'office_location' => $this->officeLocation,
            'network_others' => $this->networkOther,
            'ict_others' => $this->ictOther,
            'mis_others' => $this->misOther,
            'description' => $this->description,
            'property_no' => $this->propertyNo,
            'ict_property_no' => $this->adminCreated ? $this->propertyNo : null,
            'category' => $this->category,
            'attachment' => $attachmentPath,
            'status' => $this->status,
            'date_of_request' => $this->dateOfRequest ? Carbon::parse($this->dateOfRequest) : now(),
            'time_of_request' => $this->timeOfRequest
                ? Carbon::parse(($this->dateOfRequest ?? now()->toDateString()) . ' ' . $this->timeOfRequest)
                : now(),
        ];
    }

    private static function inferCategory(array $services): string
    {
        $serviceSet = collect($services);

        if ($serviceSet->contains(fn(string $service) => str_starts_with($service, 'new_')
            || str_contains($service, 'network')
            || str_contains($service, 'telephone')
            || str_contains($service, 'cabling')
            || str_contains($service, 'location'))) {
            return 'Network';
        }

        if ($serviceSet->contains(fn(string $service) => str_contains($service, 'troubleshoot')
            || str_contains($service, 'hardware')
            || str_contains($service, 'software_installation')
            || str_contains($service, 'others_ict'))) {
            return 'ICT';
        }

        if ($serviceSet->contains(fn(string $service) => str_contains($service, 'account')
            || str_contains($service, 'password')
            || str_contains($service, 'system_access')
            || str_contains($service, 'others_mis'))) {
            return 'MIS';
        }

        return 'General';
    }
}
