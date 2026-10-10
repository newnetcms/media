<?php

namespace Newnet\Media\Repositories;

use Newnet\Core\Repositories\BaseRepositoryInterface;

interface MediaRepositoryInterface extends BaseRepositoryInterface
{
    public function filter(array $filters, int $itemOnPage);

    public function filterStats(array $filters): array;

    public function sidebarStats(): array;
}
