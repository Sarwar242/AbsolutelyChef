@extends('layouts.theme')

@section('content')

    <div class="advertise-section pb-5 pt-5">
        <div class="container">

            <div class="row">
                <div class="col-md-12">

                    <div class="advertise-section-heading mt-4">

                        <h1>Thank you for your Payment.</h1>
                        <h5 class="text-muted"></h5>
                    </div>

                </div>
            </div>
            <hr>
            <div class="row">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 bg-white card p-sm-4 shadow">   
                            <h3>An email confirmation and payment receipt has been sent to:  {{$user->email}}</h3>
                            <h4>Invoice No:   ABC-010-{{$payment->id}}</h4>
                            <h4>Total Paid: {!! get_amount($payment->amount) !!}</h4>
                            <br><br>
                            <div style="display: flexbox">
                                <a href="{{ route('posted_jobs') }}" class="btn btn-outline-primary">Jobs</a>&nbsp;
                                &nbsp;<a href="{{ route('dashboard') }}" class="btn btn-outline-primary">Back to Dashboard</a>
                            </div>
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
