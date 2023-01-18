<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;

class CompanyController extends Controller
{
    public function fetch(Request $request) 
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $companyQuery = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        });

        // Get single data
        if($id)
        {
            $company = $companyQuery->find($id);

            if($company)
            {
                return ResponseFormatter::success($company, 'Company found');
            }

            return ResponseFormatter::error('Company not found', 404);
        }

        // Get multiple data
        $companies = $companyQuery;

        if ($name){
            $companies->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies found'
        );
    }

    public function create(CreateCompanyRequest $request)
    {

        try {
            // Upload logo
            if($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }
    
            // Create Company
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path,
            ]);

            if(!$company)
            {
                throw new Exception('Company not create');
            }

            // Attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            // Load users at company
            $company->load('users');
    
            return ResponseFormatter::success($company, 'Company created');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
        
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            $company = Company::find($id);

            // Check if company not exists
            if(!$company) {
                throw new Exception('Company not found');
            }

            // Upload logo
            if($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            // Update Company
            $company->update([
                'name' => $request->name,
                'logo' => $path,
            ]);

            return ResponseFormatter::success($company, 'Company updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
