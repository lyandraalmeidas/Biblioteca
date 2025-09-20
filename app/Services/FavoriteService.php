<?php

namespace App\Services;

use App\Repositories\FavoriteRepository;

class FavoriteService
{
    private FavoriteRepository $repo;

    public function __construct(?FavoriteRepository $repo = null)
    {
        $this->repo = $repo ?: new FavoriteRepository();
    }

    /**
     * @return int[]
     */
    public function all(int $userId): array
    {
        return $this->repo->allByUser($userId);
    }

    /**
     * Toggle book id in favorites; returns action 'added' or 'removed' and the list.
     * @return array{action:string,favorites:int[]}
     */
    public function toggle(int $userId, int $bookId): array
    {
        $action = $this->repo->toggle($userId, $bookId);
        $favorites = $this->repo->allByUser($userId);
        return ['action' => $action, 'favorites' => $favorites];
    }
}
