<?php

namespace App\Http\Controllers;

use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PackageTypeEnums;
use App\Order;
use App\Package;
use Illuminate\Http\Request;
use Nikolag\Square\Facades\Square;
use SquareConnect\ApiException;
use App\Notifications\InvoiceMail;
use Illuminate\Support\Facades\Log;
class CartController extends Controller
{
    
    public function index()
    {

        $cartItem = \Cart::getContent()->first();
        if(!$cartItem){
            return redirect()->route('advertise');
        }
        return view('cart',compact('cartItem'));
    }

    public function addToCart($id)
    {
        $package = Package::find($id);
        \Cart::clear();
        \Cart::add([
            'id'        => $package->id,
            'name'      => $package->name,
            'price'     => $package->price * $package->job_number,
            'quantity'  => 1,
            'attributes'    => [
                'type'      => $package->type,
                'single_price' => $package->price,
                'job_number' => $package->job_number
            ]
            
        ]);
        return redirect()->route('cart.index'); 
    }

    public function delete($id)
    {
        
        \Cart::remove($id);

        return redirect()->route('cart.index'); 

    }

    public function update(Request $request,$id)
    {   
        $qty = $request->input('quantity');
        $item = \Cart::getContent($id)->first();
        $per_price = calculatePrice($qty,$item->attributes->type);
        $type = $item->attributes->type;
        \Cart::update($id, array(
            'price' => $qty * $per_price , 
            'attributes'    => [
                'type' =>$type,
                'single_price'  => $per_price,
                'job_number'  => $qty 
            ]
        ));

        return redirect()->route('cart.index');
    }


    public function payment(Request $request,$nonce,$idempotency_key,$token)
    {   
        $user = auth()->user();

        $cartItem = \Cart::getContent()->first();

        $location_id = 'HCNMSVVNQ6J2D'; //$location_id is id of a location from Square

        // optional, default=USD
        $currency = 'GBP'; //available currencies => https://docs.connect.squareup.com/api/connect/v2/?q=currency#type-currency

        // optional
        $note = 'Advertise payment'; //note about this charge

        //optional
        $reference_id = $cartItem->id; //some kind of reference id to an object or resource

        $options = [
            'amount' => \Cart::getTotal() * 100 ,
            'source_id' => $nonce,
            'verification_token'    =>  $token,
            'location_id' => $location_id,
            'currency' => $currency,
            "autocomplete"=> true,
            'note' => $note,
            'reference_id' => $reference_id,
            "idempotency_key" => $idempotency_key,
        ];
        try{
            $square = Square::charge($options); // Simple charge
        }catch(\Exception  $e){
            // dd($e);
            // return response($e) ;
            return  redirect()->back()->with('error' ,$e->getMessage() );
        }
        // dd($square);
        $payment_status =  OrderPaymentStatusEnum::FAILED;
        if($square->status == "APPROVED" || $square->status == "PAID" || $square->status == "COMPLETED" )
            {
                $payment_status =  OrderPaymentStatusEnum::SUCCESS;
            }else{
                $payment_status =  OrderPaymentStatusEnum::FAILED;
            }

        if($payment_status==OrderPaymentStatusEnum::SUCCESS)
        {
            $promotion = str_random(7);
            $order = $user->orders()->create([
                'transaction_id'    => $square->payment_service_id,
                'amount'            => \Cart::getTotal(),
                'job_qty'           => $cartItem->attributes->job_number,
                'package_type'      => $cartItem->attributes->type,
                'status'            => OrderStatusEnum::PROCCESS,
                'payment_status'    => $payment_status,
                'promotion_code'    => $promotion
            ]);
            
            $payment = $order->payment()->create([
                'user_id'   => $user->id,
                'transaction_id' =>  $square->payment_service_id,
                'amount'    => \Cart::getTotal(),
                'status'    => 'success',
                'currency'    => $currency,
                'payment_method'    => 'Square',
                'jobs'    =>  $cartItem->attributes->job_number,
                'package_name'    => PackageTypeEnums::getKey($cartItem->attributes->type),
                'package_id'    => $cartItem->id,
            ]);
            \Cart::clear();
            try{
                $user->notify(new InvoiceMail($payment));
            }catch(\Exception $e){
                Log::info('Mail couldn\'t be sent. '.$e->getMessage());
            }
          
            return view('paymentsuccess', compact(['user','order']));
        }
        else{
            // dd($square);
            return redirect()->back()->with('message','Something went wrong .');
        }
    }

}
//SW1A 2AA