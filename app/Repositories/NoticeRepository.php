<?php

namespace App\Repositories;

use App\Models\Notice;
use Illuminate\Pagination\LengthAwarePaginator;

class NoticeRepository implements NoticeRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Notice::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): ?Notice
    {
        return Notice::query()->find($id);
    }

    public function create(array $data): Notice
    {
        return Notice::create($data);
    }

    public function update(Notice $notice, array $data): Notice
    {
        $notice->update($data);

        return $notice;
    }

    public function delete(Notice $notice): void
    {
        $notice->delete();
    }
}
