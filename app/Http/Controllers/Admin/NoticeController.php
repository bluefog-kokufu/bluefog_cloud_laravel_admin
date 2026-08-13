<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notice\StoreNoticeRequest;
use App\Http\Requests\Notice\UpdateNoticeRequest;
use App\Models\Notice;
use App\Services\NoticeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function __construct(private readonly NoticeService $noticeService) {}

    public function index(): View
    {
        $notices = $this->noticeService->paginate();

        return view('notices.index', compact('notices'));
    }

    public function create(): View
    {
        return view('notices.form', ['notice' => null]);
    }

    public function store(StoreNoticeRequest $request): RedirectResponse
    {
        $this->noticeService->create($request->validated());

        return redirect()->route('admin.notices.index')->with('status', 'お知らせを追加しました。');
    }

    public function show(Notice $notice): View
    {
        return view('notices.show', compact('notice'));
    }

    public function edit(Notice $notice): View
    {
        return view('notices.form', compact('notice'));
    }

    public function update(UpdateNoticeRequest $request, Notice $notice): RedirectResponse
    {
        $this->noticeService->update($notice, $request->validated());

        return redirect()->route('admin.notices.index')->with('status', 'お知らせを更新しました。');
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $this->noticeService->delete($notice);

        return redirect()->route('admin.notices.index')->with('status', 'お知らせを削除しました。');
    }
}
