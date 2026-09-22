<?php

namespace App\DTO;

class WorkspaceLayoutDTO extends BaseDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $preset,
        public readonly array $layoutMatrix,
        public readonly bool $isAutoSaved = true
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            userId: $userId,
            preset: $data['preset'] ?? 'institution_organization',
            layoutMatrix: $data['layout'] ?? [],
            isAutoSaved: $data['auto_save'] ?? true
        );
    }
}
