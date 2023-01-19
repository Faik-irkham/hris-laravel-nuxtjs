<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class RoleController extends Controller
{
    public function fetch(Request $request) 
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $roleQuery = Role::query();

        // Get single data
        if($id)
        {
            $role = $roleQuery->find($id);

            if($role)
            {
                return ResponseFormatter::success($role, 'Role found');
            }

            return ResponseFormatter::error('Role not found', 404);
        }

        // Get multiple data
        $teams = $roleQuery->where('company_id', $request->company_id);

        if ($name){
            $teams->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Role found'
        );
    }

    public function create(CreateRoleRequest $request)
    {

        try {
    
            // Create team
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            if(!$role)
            {
                throw new Exception('Role not create');
            }
    
            return ResponseFormatter::success($role, 'Role created');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
        
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            $role = Role::find($id);

            // Check if company not exists
            if(!$role) {
                throw new Exception('Role not found');
            }

            // Update Company
            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($role, 'Role updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Get team
            $role = Role::find($id);

            // TODO : Check if team is owned by user

            // Check if team exists
            if(!$role) {
                throw new Exception('Role not found');
            }

            // Delete team
            $role->delete();

            return ResponseFormatter::success('Role deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
