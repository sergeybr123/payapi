<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class PayController extends Controller
{
    public function robokassaPay(Request $request)
    {
        $payment = Payment::findOrFail($request->payment);

        $mrh_login = $payment->data['merchantLogin'];
        $mrh_pass1 = $payment->data['password_1'];

        $invoice = Invoice::where(['payment_id'=>$request->payment, 'email'=>$request->email, 'amount'=>$request->amount])
            ->whereIn('status', ['active', 'paid'])
            ->first();

        if(!$invoice && $invoice->crc == $invoice->makeSignature($mrh_login, $mrh_pass1)) {
            

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
            $crc  = $invoice->makeSignature($mrh_login, $mrh_pass1);

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
        if($invoice) {
            $invoice->status = 'complete';
            $invoice->save();

        $payment = Payment::findOrFail($invoice->payment_id);
        $mrh_login = $payment->data['merchantLogin'];
        $mrh_pass1 = $payment->data['password_1'];

        $new_invoice = new Invoice();
        $new_invoice->payment_id = $invoice->payment_id;
        $new_invoice->email = $invoice->email;
        $new_invoice->amount = $invoice->amount;
        $new_invoice->status = 'active';
        
        $new_invoice->save();
        $new_invoice->crc = $new_invoice->makeSignature($mrh_login, $mrh_pass1);
        $new_invoice->save();

            return response()->json(['invoice' => $new_invoice], 201);
        } else {
            return response()->json(['error' => 1]);
        }
    }

    public function robokassaResult(Request $request)
    {
        $out_summ = $request->OutSum;
        $inv_id = $request->InvId;
        $crc = strtoupper($request->SignatureValue);

        $invoice = Invoice::faidOrFail($inv_id);

        $payment = Payment::findOrFail($invoice->payment_id);
        $mrh_pass2 = $payment->data['password_2'];

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
        if($invoice) {
            $payment = Payment::findOrFail($invoice->payment_id);
            $phrase = "{$payment->data['merchantLogin']}:$invoice->id:{$payment->data['password_2']}";
            
            $crc = md5($phrase);
            $url = "https://auth.robokassa.ru/Merchant/WebService/Service.asmx/OpState?MerchantLogin={$payment->data['merchantLogin']}&InvoiceID={$invoice->id}&Signature={$crc}";
            $client = new Client();
            $response = $client->request('GET', $url);
            if($response->getStatusCode() == 200) {
                $xml = simplexml_load_string((string)$response->getBody());
                
                if($xml->State->Code == 100) {
                    $invoice->status = 'paid';
                    $invoice->paid_at = $xml->State->StateDate;
                    $invoice->save();
                    return response()->json(['error' => 0, 'status' => 'paid']);
                } else {
                    return response()->json(['error' => 1, 'status' => 'no_paid']);
                } 
            } else {
                return response()->json(['error' => 1, 'status' => 'no_paid']);
            }
            
        } else {
            return response()->json(['error' => 1], 404);
        }

//        $xmldata = simplexml_load_file("../pay.xml");

        //https://auth.robokassa.ru/Merchant/WebService/Service.asmx/OpState?

        //https://auth.robokassa.ru/Merchant/WebService/Service.asmx/OpState?MerchantLogin=demo&InvoiceID=1932809606&Signature=9e2bf657364d25acf5905b4ac4f50e39

        //Signature - MerchantLogin:InvoiceID:Пароль#2

//        return response()->json(['xml' => $xml]);

//
//        if($invoice->status == 'paid') {
//            return response()->json(['status' => 'paid']);
//        } else {
//            return response()->json(['status' => 'no_paid']);
//        }
    }
}
