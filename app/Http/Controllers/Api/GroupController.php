<?php

namespace App\Http\Controllers\Api;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupController extends Controller
{

    public function index()
    {
        $group = Group::all();

        return response()->json([
            'success' => true,
            'group' => $group
        ]);
    }

    public function show($id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'group' => $group
        ]);
    }

    public function store(Request $request)
    {
        $group = new Group();
        $group->name = $request->name;
        $group->save();

        return response()->json([
            'success' => true,
            'group' => $group
        ]);
    }

    public function update(Request $request, $id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 400);
        }

        $updated = $group->fill($request->all())->save();

        if ($updated) {
            return response()->json([
                'success' => true,
                'group' => $group
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Group can not be updated'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 400);
        }

        if ($group->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Group deleted successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Group can not be deleted'
            ], 500);
        }
    }

}
