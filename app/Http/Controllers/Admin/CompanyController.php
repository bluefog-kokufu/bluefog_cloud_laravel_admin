<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $companyService) {}

    public function index(): View
    {
        $companies = $this->companyService->paginate();

        return view('companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('companies.form', ['company' => null]);
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $this->companyService->create($request->validated());

        return redirect()->route('admin.companies.index')->with('status', '契約企業を追加しました。');
    }

    public function show(Company $company): View
    {
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        return view('companies.form', compact('company'));
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $this->companyService->update($company, $request->validated());

        return redirect()->route('admin.companies.index')->with('status', '契約企業を更新しました。');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->companyService->delete($company);

        return redirect()->route('admin.companies.index')->with('status', '契約企業を削除しました。');
    }

    public function provision(Company $company): RedirectResponse
    {
        try {
            $this->companyService->startProvisioning($company);
        } catch (RuntimeException $e) {
            return redirect()->route('admin.companies.show', $company)->with('status', $e->getMessage());
        }

        return redirect()->route('admin.companies.show', $company)->with('status', 'プロビジョニングを開始しました。完了までしばらくお待ちください。');
    }
}
