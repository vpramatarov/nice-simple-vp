<?php

declare(strict_types=1);

namespace NiceSimpleVp\Repository;

interface Item
{
    public function get(int $id);
}