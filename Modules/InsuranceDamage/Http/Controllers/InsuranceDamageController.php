<?php

namespace Modules\InsuranceDamage\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\FileManager\Http\Controllers\FileManagerController;
use Modules\InsuranceDamage\Entities\Damage;
use Modules\InsuranceDamage\Entities\DamageSupplementary;
use ZipArchive;

class InsuranceDamageController extends Controller
{

    public function index(Request $request)
    {
        $fields = $request->validate([
            'op' => 'string|max:255',
        ]);
        if (isset($fields['op'])){
            switch ($fields['op']) {
                case 'delete';
                    $damage = Damage::onlyTrashed();
                    break;
                case 'archive';
                    $damage = Damage::where('archive', '!=', null);
                    break;
                default :
                    $damage = new Damage();
            }
        }else{
            $damage = new Damage();
        }

        $entities = $damage->with(['refer' => function($q) {
            $q->with(['transferor', 'receiver']);
        },'insuranceType', 'injured', 'city', 'files', 'supplementary', 'operator'])
            ->where(function ($q) use($request) {


                if ($request->has('injured') && $request->get('injured') != -1) {
                    $q->where('injured', $request->get('injured'));
                }

                if ($request->get('receiver') && $request->get('receiver') != -1) {
                    $q->whereHas('refer', function ($refer) use($request) {
                        $refer->where('receiver', $request->get('receiver'));
                    });
                }

                if ($request->get('mobile') && $request->get('mobile') != -1) {
                    $q->whereHas('injured', function ($inj) use($request) {
                        $inj->where('mobile', $request->get('mobile'));
                    });
                }


                if ($request->has('refer_id') && $request->get('refer_id') != -1) {
                    $q->where('refer_id', $request->get('refer_id'));
                }

                if ($request->has('id') && $request->get('id') != -1) {
                    $q->where('id', $request->get('id'));
                }


                if ($request->has('status') && $request->get('status') != -1) {
                    $q->where('status', $request->get('status'));
                }

                if ($request->has('insurance_type') && $request->get('insurance_type') != -1) {
                    $q->where('insurance_type', $request->get('insurance_type'));
                }

                if ($request->has('city_id') && $request->get('city_id') != -1) {
                    $q->where('city_id', $request->get('city_id'));
                }


                if ($request->get('from_date') && $request->get('to_date') && $request->get('from_date') != "" && $request->get('to_date') != "") {
                    $q->where('created_at', '>=', $request->get('from_date'));
                    $q->where('created_at', '<=', $request->get('to_date'));
                } elseif ($request->get('from_date')) {
                    $q->where('created_at', '>=', $request->get('from_date'));
                } elseif ($request->get('to_date')) {
                    $q->where('created_at', '<=', $request->get('to_date'));
                }

                if ( ! in_array(Auth::user()->role_id , ['programmer', 'super_admin', 'guest'])) {
                    $q->where('refer_id', Auth::id());
                    $q->where('status', '<>', 2);
                }

            })
//            ->where('deleted', 0)
            ->orderBy('id', 'desc')
            ->paginate($request->has('limit') ? $request->get('limit') : 15);

        return response($entities);
    }


    public function refer($id, Request $request)
    {

        $fields = $request->validate([
            'desc' => 'string',
            'receiver' => 'numeric|required'
        ]);

        $damage_file = Damage::find($id);
        if (!$damage_file) return response(['status' => false, 'msg' => 'پرونده یافت نشد!'], 404);

        $damage_file->update([
            'status' => $request->get('status') ?? 1
        ]);

        if (!in_array($request->get('status'), [2])) {

            $model = DB::table('damage_refer')->insertGetId([
                'transferor' => Auth::id(),
                'damage_id' => $id,
                'desc' => $fields['desc'] ?? '--',
                'receiver' => $fields['receiver'],
                'created_at' => Carbon::now(),
//                'status'        => 1, # must be set default in database , Not here!!!
            ]);
        }

        return response(['status' => true, 'msg' => 'ارجاع داده شد.']);

    }

    public function referLog($id)
    {
        try {

            $response = DB::select('call sp_refer_log(?)', [$id]);

            return response()->json(['status' => true, 'result' => $response]);
        } catch (\Exception $exception) {
            if ($exception instanceof \Illuminate\Database\QueryException) {
                return response()->json(['status' => false, 'msg' => $exception->getPrevious()->errorInfo[2]]);
            } else {
                return response()->json(['status' => false, 'msg' => $exception->getMessage()]);
            }
        }
    }


    public function determining($id, Request $request)
    {

        $result = DB::table('insurance_damage_determining')
            ->leftJoin('user', 'user.id', '=', 'insurance_damage_determining.created_by')
            ->where('damage_id', $id)->get();

        return response(['status' => true, 'result' => $result]);
    }


    public function determiningCreate($id, Request $request)
    {

        $id = DB::table('insurance_damage_determining')->insertGetId([
            'damage_id' => $id,
            'created_by' => Auth::id(),
            'transport' => $request->get('transport') ? $request->get('transport') : 0,
            'commission' => $request->get('commission') ? $request->get('commission') : 0,
            'deductions' => $request->get('deductions') ? $request->get('deductions') : 0,
            'scrap_pieces' => $request->get('scrap_pieces') ? $request->get('scrap_pieces') : 0,
            'accessories' => $request->get('accessories') ? $request->get('accessories') : 0,
            'total' => $request->get('total') ? $request->get('total') : 0,
            'car_price' => $request->get('car_price') ? $request->get('car_price') : 0,
            'date_visit' => $request->get('date_visit'),
            'time_visit' => $request->get('time_visit'),
            'details' => json_encode($request->get('details')),
            'created_at' => Carbon::now()
        ]);

        if ($id) {
            return response(['status' => true, 'msg' => 'تعیین خسارت با موفقیت انجام شد.']);
        }

        return response(['status' => false, 'msg' => 'خطایی رخ داده است.']);
    }

    public function determiningConfirm($id, Request $request)
    {
        $result = DB::table('insurance_damage_determining')->where('id', $id)->update([
            'approved_by' => Auth::id(),
            'status' => $request->get('status'),
            'created_at' => Carbon::now()
        ]);

        if ($result) {
            return response(['status' => true, 'msg' => 'تعیین خسارت تایید شد.']);
        }

        return response(['status' => false, 'msg' => 'خطایی رخ داده است.']);
    }

    public function supplementary($id, Request $request)
    {
        $request->validate([
            'injured'       => 'required|array',
            'damage_id'     => 'required|numeric',
        ]);
        foreach ($request->get('injured') as $item){
            $model = null;
            $item['damage_id'] = $id;

            if (isset($item['id'])){
                $model = DamageSupplementary::find($item['id']);
            }

            if ($model) {
                $model->update($item);
            }else {
                DamageSupplementary::create($item);
            }
        }

        return response(['status' => true, 'msg' => 'تعیین خسارت تایید شد.']);
    }

    public function show($id)
    {
        try {
            $model = Damage::with(['refer' => function($q) {
                $q->with(['transferor', 'receiver']);
            },'insuranceType', 'injured', 'city', 'files', 'supplementary', 'operator', 'files'])->where('damage_id', $id);

            DB::table('damage_refer')->where('receiver', Auth::id())
                ->where('seen', 0)
                ->where('damage_id', $id)
                ->update([
                    'seen' => 1,
                    'last_seen' => Carbon::now()
                ]);

            return response($model);

        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }
    }

    public function download($id)
    {
        $zip = new ZipArchive();
        $zip_file = 'insurance_damage_' . $id . '_' . time() . '.zip'; // Name of our archive to download
        if (!file_exists(public_path('files'))) mkdir(public_path('files'));
        if (!file_exists(public_path('files/' . $id))) mkdir(public_path('files/' . $id));
        $zip_file = 'files/' . $id .DIRECTORY_SEPARATOR. $zip_file;
        if ($zip->open(public_path( $zip_file), ZipArchive::CREATE) === TRUE) {
            $files = DB::table('file')
                ->where('fileable_type', "Modules\\InsuranceDamage\\Entities\\Damage")
                ->where('fileable_id', $id)
                ->get();
            foreach ($files as $file) {
                try {
                    $zip->addFile(public_path('/storage/' . $file->directory . '/' . $file->fileable_id . '/' . $file->file), $file->caption . '/' . $file->file);
                } catch (\Exception $exception) {
                    continue;
                }
            }
            $zip->close();
            return response(['status' => true, 'result' => $zip_file]);
        }

        return response(['status' => false]);

    }


    public function update(Request $request, int $id)
    {
        $request->validate([
            'files'             => 'array|min:0',
            'files.*.caption'   => 'string',
            'files.*.name'      => 'string',
            'files.directory'   => 'string',
        ]);
        try {
            $model = Damage::find($id);
            if ($request->get('type') == 1) {
                $rules = [
                    'lat'                   => 'required',
                    'lng'                   => 'required',
                    'date_occurrence'       => 'required',
                    'time_occurrence'       => 'required',
                    'address_occurrence'    => 'required',
                    'city_id'               => 'required',
                    'insurance_type'        => 'required',
                    'type'                  => 'required',
                ];
                if ($request->get('insurance_type') == 1) {
                    $rules['guilty_mobile'] = 'required';
                    $rules['injured_insurance_unique_code'] = 'required';
                    $rules['guilty_insurance_unique_code'] = 'required';
                }
            } else {
                $rules = [
                    'lat'                   => 'required',
                    'lng'                   => 'required',
                    'date_occurrence'       => 'required',
                    'time_occurrence'       => 'required',
                    'address_occurrence'    => 'required',
                    'city_id'               => 'required',
                    'insurance_type'        => 'required',
                    'type'                  => 'required',
                ];
            }
            $validator = \Validator::make($request->all(), $rules);

            if ( $validator->fails() ) {
                return Response()->json(['status' => false, 'msg' => $validator->errors()->first()]);
            }

            try {
                $model->update([
                    'lat'                   => $request->get('lat'),
                    'lng'                   => $request->get('lng'),
                    'date_occurrence'       => $request->get('date_occurrence'),
                    'time_occurrence'       => $request->get('time_occurrence'),
                    'address_occurrence'    => $request->get('address_occurrence'),
                    'city_id'               => $request->get('city_id'),
                    'insurance_type'        => $request->get('insurance_type'),
                    'type'                  => $request->get('type'),
                    'description'           => $request->get('des'),
                    'guilty_mobile'         => $request->has('guilty_mobile') ? $request->get('guilty_mobile') : null,
                    'injured_insurance_unique_code' => $request->has('injured_insurance_unique_code') ?  $request->get('injured_insurance_unique_code') : null,
                    'guilty_insurance_unique_code'  => $request->has('guilty_insurance_unique_code') ?  $request->get('guilty_insurance_unique_code') : null,
                    'convertible_number'    => $request->has('convertible_number') ?  $request->get('convertible_number') : null,
                    'has_convertible'       => $request->has('has_convertible') ?  $request->get('has_convertible') : 0,
                ]);


                if ($request->has('files')) {
                    $dir = $request->has('files.directory') ? $request->get('files.directory') : null;
                    $result = FileManagerController::moveFileFromAttachment($model, $request->get('files'), $dir, [], false);
                    if ($result['status'] == false) return $result;
                }

                if ($model) {
                    return response(['status' => true, 'msg' => 'موفقیت آمیز']);
                }

                return response(['status' => false, 'msg' => 'خطا']);
            } catch (Exception $exception) {
                return response(['status' => false, 'msg' => $exception->getMessage() , 'line' => $exception->getLine()]);
            }

        } catch (Exception $exception) {
            return response()->json(['status' => false, 'msg' => $exception->getMessage()]);
        }
    }

    public function indexDeleted(Request $request)
    {
        $damages = Damage::onlyTrashed()->paginate($request->has('limit') ? $request->get('limit') : 15);
        return $damages;
    }

    public function organizing(): array
    {
        # Damage() is for `insurance_damage` table !
        $items = Damage::all();
        $count = 0;
        foreach ($items as $item) {
            if ($item->deleted){
                $item->update([
                   'deleted_at' => $item->updated_at,
                ]);
                $count++;
            }
        }

        return ['status' => true, 'count' => $count];
    }


    public function makeArchive(Request $request)
    {
        $fields = $request->validate([
            'id'        => 'required|numeric',
            'archive'   => 'required|bool'
        ]);

        $damage = Damage::find($fields['id']);
        if ( ! $damage)
            return response(['status' => false, 'msg' => 'پرونده مورد نظر موجود نمیباشد.'], 404);

        $date = date('Y-m-d H:i:s');
        $fields['archive'] = $fields['archive'] ? $date : null;
        if ($damage->update(['archive' => $fields['archive']])){
            return response(['status' => true, 'msg' => 'موفقیت آمیز']);
        }
        return response(['status' => false, 'msg' => 'خطایی رخ داد.'], 422);
    }

    public function restore(Request $request)
    {
        $fields = $request->validate([
            'id'  => 'required|numeric',
        ]);
        $damage = Damage::withTrashed()->find($fields['id']);
        if ( ! $damage)
            return response(['status' => false, 'msg' => 'پرونده مورد نظر یافت نشد.']);

        if ($damage->restore()){
            return response(['status' => true, 'msg' => 'موفقیت آمیز.']);
        }
        return response(['status' => false, 'msg' => 'خطایی رخ داده است.']);
    }

    public function destroy($id)
    {

        $model = Damage::find($id);
        if ( ! $model)
            return response(['status' => false, 'msg' => 'پرونده موجود نیست!'], 404);

        if ($model->delete())
            return response(['status' => true, 'msg' => 'موفقیت آمیز']);

        return response(['status' => false, 'msg' => 'خطایی رخ داده است.']);

    }
}


