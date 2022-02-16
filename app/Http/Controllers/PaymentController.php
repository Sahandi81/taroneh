<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Shetabit\Multipay\Invoice;

class PaymentController extends Controller
{
    public function index()
    {

    }

    public function gateway(Request $request)
    {
        $invoice = new Invoice;
        $invoice->amount(1000);
        $invoice->detail(['detailName' => 'your detail goes here']);
        $invoice->detail('detailName', 'your detail goes here');
        $invoice->detail(['name1' => 'detail1', 'name2' => 'detail2']);
        $invoice->detail('detailName1', 'your detail1 goes here')
            ->detail('detailName2', 'your detail2 goes here');

    }
}
