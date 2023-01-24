<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Models\Responsibility;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateResponsibilityRequest;

class ResponsibilityController extends Controller
{
    public function fetch(Request $request) 
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $responsibilityQuery = Responsibility::query();

        // Get single data
        if($id)
        {
            $responsibility = $responsibilityQuery->find($id);

            if($responsibility)
            {
                return ResponseFormatter::success($responsibility, 'Responsibility found');
            }

            return ResponseFormatter::error('Responsibility not found', 404);
        }

        // Get multiple data
        $responsibilities = $responsibilityQuery->where('role_id', $request->role_id);

        if ($name){
            $responsibilities->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $responsibilities->paginate($limit),
            'responsibility found'
        );
    }

    public function create(CreateResponsibilityRequest $request)
    {

        try {
    
            // Create team
            $responsibility = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);

            if(!$responsibility)
            {
                throw new Exception('Responsibility not create');
            }
    
            return ResponseFormatter::success($responsibility, 'Responsibility created');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
        
    }

    public function destroy($id)
    {
        try {
            // Get team
            $responsibility = Responsibility::find($id);

            // TODO : Check if team is owned by user

            // Check if team exists
            if(!$responsibility) {
                throw new Exception('Responsibility not found');
            }

            // Delete team
            $responsibility->delete();

            return ResponseFormatter::success('Responsibility deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
