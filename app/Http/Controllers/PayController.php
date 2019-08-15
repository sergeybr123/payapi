<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayController extends Controller
{
    public function robokassaPay(Request $request)
    {
        $payment = Payment::findOrFail($request->payment);

        $invoice = Invoice::where(['payment_id'=>$request->payment, 'email'=>$request->email, 'amount'=>$request->amount, 'status'=>'active'])->first();

        if(!$invoice) {
            $mrh_login = $payment->data['merchant_id'];
            $mrh_pass1 = $payment->data['merchant_pass'];

            $invoice = new Invoice();
            $invoice->payment_id = $request->payment;
            $invoice->email = $request->email;
            $invoice->amount = $request->amount;
            $invoice->status = 'active';
            $invoice->save();

            // номер заказа
            $inv_id = $invoice->id;

            // сумма заказа
            $out_summ = $invoice->amount;

            // формирование подписи
            $crc  = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");

            $invoice->crc = $crc;
            $invoice->save();

            return response()->json(['invoice' => $invoice], 201);
        } else {
            return response()->json(['invoice' => $invoice], 200);
        }
    }

    public function renew(Request $request)
    {
        $invoice = Invoice::findOrFail($request->id);
        $invoice->status = 'complete';
        $invoice->save();

        $new_invoice = new Invoice();
        $new_invoice->payment_id = $invoice->payment;
        $new_invoice->email = $invoice->email;
        $new_invoice->amount = $invoice->amount;
        $new_invoice->status = 'active';
        $new_invoice->save();

        return response()->json(['invoice' => $new_invoice], 201);
    }

    public function robokassaResult(Request $request)
    {
        $out_summ = $request->OutSum;
        $inv_id = $request->InvId;
        $crc = strtoupper($request->SignatureValue);

        $invoice = Invoice::faidOrFail($inv_id);

        $payment = Payment::findOrFail($invoice->payment_id);
        $mrh_pass2 = $payment->data['merchant_pass_2'];

        $my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass2"));

        if ($my_crc == $crc)
        {
            $invoice->paid = true;
            $invoice->paid_at = Carbon::now();
            $invoice->status = 'paid';
            $invoice->save();

//            return response()->json(['error' => 0, 'status' => 'paid']);
        } else {
//            return response()->json(['error' => 1, 'status' => 'no_paid']);
        }
    }

    public function getStatus($id)
    {
        $invoice = Invoice::findOrFail($id);
        if($invoice->status == 'paid') {
            return response()->json(['status' => 'paid']);
        } else {
            return response()->json(['status' => 'no_paid']);
        }
    }
}
