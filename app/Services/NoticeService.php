<?php

namespace App\Services;

use App\Models\Notice;
use App\Repositories\NoticeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class NoticeService
{
    public function __construct(private readonly NoticeRepositoryInterface $notices) {}

    public function paginate(): LengthAwarePaginator
    {
        return $this->notices->paginate();
    }

    public function find(int $id): ?Notice
    {
        return $this->notices->find($id);
    }

    public function create(array $data): Notice
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $this->notices->create($data);
    }

    public function update(Notice $notice, array $data): Notice
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $this->notices->update($notice, $data);
    }

    public function delete(Notice $notice): void
    {
        $this->notices->delete($notice);
    }
}
