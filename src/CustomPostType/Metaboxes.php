<?php

declare(strict_types=1);

namespace NiceSimpleVp\CustomPostType;

interface Metaboxes
{
    public function addMetaBoxes(): void;

    public function saveMetaboxesData(int $postId ): void;
}