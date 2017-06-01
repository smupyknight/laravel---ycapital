<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Company;

class CompanyController extends Controller
{
    /**
     * Show company list
     * @return view 
     */
    public function getIndex()
    {
        $companies = Company::active()->paginate(20);
        return view('pages.admin.company-list')
                ->with('companies',$companies)
                ->with('title','Company Management');
    }

    /**
     * Show add company form
     * @return view 
     */
    public function getAdd()
    {
        return view('pages.admin.company-form')
                ->with('form_title','Create Company')
                ->with('title','Create Company');
    }

    /**
     * Handles saving of new company
     * @param  Request $request 
     * @return redirect           
     */
    public function postAdd(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'abn' => 'required',
            'acn' => 'required',
            'billing_address' => 'required',
            'billing_email' => 'required|email|unique:companies,billing_email',
        ]);
        $company = new Company;
        $company->name = $request->name;
        $company->abn = $request->abn;
        $company->acn = $request->acn;
        $company->billing_address = $request->billing_address;
        $company->billing_email = $request->billing_email;
        $company->save();
        return redirect('admin/companies');
    }


    /**
     * Shows edit company form
     * @param  int $company_id 
     * @return view             
     */
    public function getEdit($company_id)
    {
        $company = Company::findOrFail($company_id);
        return view('pages.admin.company-form')
                ->with('company',$company)
                ->with('form_title','Edit Company')
                ->with('title','Edit Company');
    }

    /**
     * Handles updating of company
     * @param  Request $request    
     * @param  int  $company_id 
     * @return redirect              
     */
    public function postEdit(Request $request,$company_id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'abn' => 'required',
            'acn' => 'required',
            'billing_address' => 'required',
            'billing_email' => 'required|email|unique:companies,billing_email,'.$company_id,
        ]);
        $company = Company::findOrFail($company_id);
        $company->name = $request->name;
        $company->abn = $request->abn;
        $company->acn = $request->acn;
        $company->billing_address = $request->billing_address;
        $company->billing_email = $request->billing_email;
        $company->save();
        return redirect('admin/companies');
    }

    /**
     * Soft deletes a company
     * @param  int $company_id 
     * @return redirect             
     */
    public function getDelete($company_id)
    {
        $company = Company::findOrFail($company_id);
        $company->is_active = 0;
        $company->save();
        return redirect('admin/companies');
    }
}
