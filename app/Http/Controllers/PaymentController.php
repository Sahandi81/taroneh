<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PurchaseHistory;
use App\Traits\SmsHelper;
use Evryn\LaravelToman\Facades\Toman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Evryn\LaravelToman\CallbackRequest;

class PaymentController extends Controller
{
	use SmsHelper;

    public function index(): array
	{
        return [
			'success' => true,
			'details' => array_reverse(Payment::all()->toArray())
		];
    }


    public function gateway(Request $request)
    {

        $fields = $request->validate([
            'order_id' => 'required'
        ]);

        $order = PurchaseHistory::find($fields['order_id']);

        if (!in_array($order->status, ['unpaid', 'failed']) ){
            return Response::json(['success' => false, 'msg' => 'ORDER_IS_NOT_PAYABLE']);
        }

        $request = Toman::amount($order->final_price)
//        $request = Toman::amount(1000)
            ->description('token : ' . $order->id . ', from : ' . env('APP_URL'))
            ->callback(route('payment.callback'))
            ->request();

        if ($request->successful()) {
            $transactionId = $request->transactionId();

            Payment::create([
               'transaction'            => $transactionId,
               'purchase_history_id'    => $order->id,
               'user_id'                => Auth::id(),
               'status'                 => 0,
               'amount'                 => $order->final_price,
            ]);

            return [
                'success'   => true,
                'url'       => $request->paymentUrl(),
            ];
        }

        if ($request->failed()) {
            return Response::json(['success' => false, 'msg' => 'SOMETHING_WRONG_WITH_GATEWAY']);
        }

        return ['success' => true, 'msg' => 'UNKNOWN_ERROR'];

    }

    public function callback(CallbackRequest $request)
	{

        $payment = $request->amount(1000)->verify();

        if ($payment->successful()) {
            $referenceId = $payment->referenceId();
            $payment = Payment::with('users')->where('transaction', $request->transactionId())->first();
            $payment->update(['status' => 1]);
			$purchase = PurchaseHistory::find($payment->purchase_history_id);
			$purchase->update(['status' => 'paid']);
			SmsHelper::sendSms($payment->users->phone, [$payment->users->f_name, env('WEB_URL') . 'profile/orders?id=' . $purchase->id]);
			return redirect(env('WEB_URL') . 'checkout/success')->with(['success' => true, 'details' => $payment, 'reference_id' => $referenceId]);
        }

        if ($payment->alreadyVerified()) {
            return ['success' => false, 'msg' => 'ORDER_ALREADY_VERIFIED'];
        }

        if ($payment->failed()) {
            $payment = Payment::with('users')->where('transaction', $request->transactionId())->first();
            $payment->update(['status' => 2]);
            PurchaseHistory::find($payment->purchase_history_id)->update(['status' => 'failed']);
			return redirect(env('WEB_URL') . 'checkout/failed')->with(['success' => false, 'details' => $payment]);
        }

        return ['success' => true, 'msg' => 'UNKNOWN_ERROR'];

    }
}
