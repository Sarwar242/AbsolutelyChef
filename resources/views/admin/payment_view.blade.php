@extends('layouts.dashboard')


@section('content')
    <div class="row">
        <div class="col-md-12">


            <table class="table table-striped table-hover">

                <tr>
                    <th>@lang('app.payer_name')</th>
                    <td>{{$payment->user->name}}</td>
                </tr>
                <tr>
                    <th>@lang('app.payer_email')</th>
                    <td>{{$payment->user->email}}</td>
                </tr>

                <tr>
                    <th>@lang('app.amount')</th>
                    <td>{!! get_amount($payment->amount) !!}</td>
                </tr>

                <tr>
                    <th>@lang('app.method')</th>
                    @if(!is_null($payment->transactionDetail()))
                    <td>{{$payment->transactionDetail()->payment_service_type }}</td>
                    @else 
                    <td>Square(Debit/Credit Card)</td>
                    @endif
             
                </tr>

                <tr>
                    <th>@lang('app.currency')</th>
                    @if(!is_null($payment->transactionDetail()))
                    <td>{{$payment->transactionDetail()->currency}}</td>
                    @else 
                    <td>GBP</td>
                    @endif
                </tr>

                @if($payment->payment_method == 'stripe')

                    <tr>
                        <th>@lang('app.card_last4')</th>
                        <td>{{$payment->card_last4}}</td>
                    </tr>

                    <tr>
                        <th>@lang('app.card_id')</th>
                        <td>{{$payment->card_id}}</td>
                    </tr>

                    <tr>
                        <th>@lang('app.card_brand')</th>
                        <td>{{$payment->card_brand}}</td>
                    </tr>

                    <tr>
                        <th>@lang('app.card_expire')</th>
                        <td>{{$payment->card_exp_month}},{{$payment->card_exp_year}}</td>
                    </tr>

                @endif

                <tr>
                    <th>@lang('app.gateway_transaction_id')</th>
                    <td>{{$payment->transaction_id }}</td>
                </tr>

                @if($payment->payment_method == 'bank_transfer')
                    <tr>
                        <th colspan="2"><h4>@lang('app.bank_transfer_information')</h4></th>
                    </tr>
                    <tr>
                        <th>@lang('app.bank_swift_code')</th>
                        <td>{{$payment->bank_swift_code}}</td>
                    </tr>

                    <tr>
                        <th>@lang('app.account_number')</th>
                        <td>{{$payment->account_number}}</td>
                    </tr>

                    <tr>
                        <th>@lang('app.branch_name')</th>
                        <td>{{$payment->branch_name}}</td>
                    </tr>

                    <tr>
                        <th>@lang('app.branch_address')</th>
                        <td>{{$payment->branch_address}}</td>
                    </tr>

                    <tr>
                        <th>@lang('app.account_name')</th>
                        <td>{{$payment->account_name}}</td>
                    </tr>

                    <tr>
                        <th>@lang('app.iban')</th>
                        <td>{{$payment->iban}}</td>
                    </tr>
                @endif

                <tr>
                    <th>@lang('app.time')</th>
                    <td>{{$payment->created_at->format('F d, Y h:i a')}}</td>
                </tr>
            </table>


            @if($payment->reward)
                <h3>@lang('app.selected_reward')</h3>
                <table class="table table-striped table-bordered">
                    <tr>
                        <th>@lang('app.amount')</th>
                        <td>{!! get_amount($payment->reward->amount) !!}</td>
                    </tr>
                    <tr>
                        <th>@lang('app.description')</th>
                        <td>{{$payment->reward->description}}</td>
                    </tr>
                    <tr>
                        <th>@lang('app.estimated_delivery')</th>
                        <td>{{$payment->reward->estimated_delivery}}</td>
                    </tr>
                </table>
            @endif

    <div style="display: flexbox">
        @if(!is_null($payment->transactionDetail()))
        @if($payment->transactionDetail()->status != 'PAID')
            <a href="{{route('status_change', [$payment->id, 'success'] )}}" class="btn btn-success"><i class="fa fa-check-circle-o"></i> @lang('app.mark_as_success') </a>
        @endif
        @if($payment->transactionDetail()->status == 'PAID')
        <a href="{{route('invoice', $payment->id )}}" class="btn btn-success"><i class="la la-save"></i> @lang('app.invoice') </a>
        @endif
        @else 
        <a href="{{route('invoice', $payment->id )}}" class="btn btn-success"><i class="la la-save"></i> @lang('app.invoice') </a>
        @endif

    </div>
           

        </div>
    </div>



@endsection