<?php

declare(strict_types=1);

namespace NiceSimpleVp\CustomPostType;

interface Taxonomy
{
    public function getTaxonomyName(): string;
}