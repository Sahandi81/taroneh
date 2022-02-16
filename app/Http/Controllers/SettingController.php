<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): array
    {
        return ['status' => true, 'details' => Setting::all()];
    }

    public function update(Request $request)
    {
        $fields = $request->validate([
            'id'        => 'required|exists:settings,_id',
            'status'    => 'required|max:1|min:0',
            'item'      => 'string',
        ]);

        try {
            Setting::find($fields['id'])->update($fields);
            return ['status' => true, 'details' => Setting::find($fields['id'])];
        } catch (\PDOException $e) {
            return ['status' => false, 'msg' => 'CALL_PROGRAMMER', 'error' => $e->getMessage()];
        }
    }
}
