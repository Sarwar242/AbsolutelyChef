@extends('layouts.theme')

@section('head')
<!-- link to the local custom styles for SqPaymentForm -->
{{-- <link rel="stylesheet" type="text/css" href="mysqpaymentform.css"> --}}
<!-- link to the SqPaymentForm library -->
<script type="text/javascript" src="https://js.squareup.com/v2/paymentform">
</script>
<style>
 
    * {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

#sq-ccbox {
  float:left;
  margin:5px;
  padding:10px;
  vertical-align: top;
  font-weight: bold;
}

#nonce-form {
  padding: 25px;
  text-align: right;
}

.sq-input {
  display: inline-block;
  border: 1px solid #E0E2E3;
  border-radius: 4px;
}

.sq-input--focus {
  border: 1px solid #4A90E2;
  box-shadow: 0 0 2px 0 rgba(0,0,0,0.10), 0 2px 2px 0 rgba(0,0,0,0.10);
}

.sq-input--error {
  border: 1px solid red;
}

.label {
  font-size: 18px;
  font-weight: bold;
  line-height: 24px;
  padding-right: 16px;
}

#sq-creditcard {
  display: block;
  width: 100%;
  height: 56px;
  padding: 15px;
  margin-top: 10px;
  background: #0EB00E;
  box-shadow: 0 0 2px 0 rgba(0,0,0,0.10), 0 2px 2px 0 rgba(0,0,0,0.10);
  border-radius: 4px;
  cursor:pointer;
  color: #FFFFFF;
  font-size: 16px;
  line-height: 24px;
  text-align: center;
  outline: none;
}
</style>
@endsection
@section('content')

    <div class="advertise-section pb-5 pt-5">
        <div class="container">

            <div class="row">
                <div class="col-md-12">

                    <div class="advertise-section-heading mt-4">

                        <h1>Buy Premium Balance</h1>
                        <h5 class="text-muted"></h5>
                    </div>

                </div>
            </div>
            <hr>
            <div class="row">
                <div class="container">
                    <div class="row">
                        <div class="col-md-8 bg-white card p-sm-4 shadow">   
                                    <table class="table text-center cel-nostyle">        
                                        <tr class="border-bottom">
                                            <th>General : </th>
                                            <td>                  
                                                <input type="number" name="" onchange="update();" id="general" value="0" min="0" max="10">
                                            </td>
                                        </tr>             
                                        <tr class="border-bottom">
                                            <th>Management : </th>
                                            <td>             
                                                <input type="number" name="" onchange="update();" id="management" value="0" min="0" max="10">
                                            </td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <th >Total price : </th>
                                            <td id="ttl"> {!! get_amount(0) !!}</td>
                                        </tr>
                                        <tr>
                                            <th >
                                                Total Payment :    
                                            </th>
                                            <td id="pym">
                                                 {!! get_amount(0) !!}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <div id="form-container">
                                                    <input type="hidden" id="card-nonce">
                                                    <input type="hidden" id="total_pay">
                                                    <div id="sq-card-number"></div>
                                                    <div class="third" id="sq-expiration-date"></div>
                                                    <div class="third" id="sq-cvv"></div>
                                                    <div class="third" id="sq-postal-code"></div>
                                                    <button id="sq-creditcard" class="button-credit-card" onclick="onGetCardNonce(event);" >Pay</button>
                                                    {{-- onclick="" --}}
                                                  </div> 
                                            </td>
                                        </tr>
                                    </table>
                        </div>
                        <div class="col-md-4"> 
                            <h3>Premium Job Posting Balance</h3>
                            <h4>Flat Rate</h4>
                            <ul class="list-group">
                                <li class="list-group-item"><span>General</span><span class="float-right"> {!! get_amount(30) !!}</span></li>
                                <li class="list-group-item"><span>Management</span><span class="float-right"> {!! get_amount(50) !!}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('page-js')
<script>

function update() {
    var x=document.getElementById("general").value;
    var y=document.getElementById("management").value;
    var z = (parseInt(x)*30)+(parseInt(y)*50);
    // console.log(z);
    // alert(z);
    document.getElementById("total_pay").value=z;
    document.getElementById("pym").innerHTML="&pound;"+z;
    document.getElementById("ttl").innerHTML="&pound;"+z;
    var test = document.getElementById("total_pay").value;
    // alert(test);
}

</script>

<script type="text/javascript">

const idempotency_key = uuidv4();
function uuidv4() {
   return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
     var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
     return v.toString(16);
   });
}
function testpay() {
  var general=document.getElementById("general").value;
  var management=document.getElementById("management").value;
  var total_pay=document.getElementById("total_pay").value;
  var url = "{!! url('premium/pay') !!}";
  var nonce = 'test';
  // var idempotency_key = 'test';
  var token = 'test';
  window.location.replace( url+'/' + nonce + '/'+idempotency_key+ '/' + token + '/' +general+ '/' +management+ '/'+ total_pay );
}



var paymentForm = new SqPaymentForm({
  // Initialize the payment form elements
  applicationId: "{!! env('SQUARE_APPLICATION_ID') !!}",
  inputClass: 'sq-input',
  autoBuild: false,
  locationId: "WDX1WFYN7TBWD",
  // Initialize the credit card inputTargets
  cardNumber: {
    elementId: 'sq-card-number',
    placeholder: '• • • •  • • • •  • • • •  • • • •'
  },
  cvv: {
    elementId: 'sq-cvv',
    placeholder: 'CVV'
  },
  expirationDate: {
    elementId: 'sq-expiration-date',
    placeholder: 'MM/YY'
  },
  postalCode: {
    elementId: 'sq-postal-code',
    placeholder: 'Post Code'
  },
  // Customize the CSS for SqPaymentForm iframe elements
  inputStyles: [{
    fontSize: '18px',
    fontFamily: 'Helvetica Neue',
    padding: '15px',
    color: '#373F4A',
    lineHeight: '24px',
    placeholderColor: '#BDBFBF'
  }],

  // SqPaymentForm callback functions
  callbacks: {

           /*
           * callback function: cardNonceResponseReceived
           * Triggered when: SqPaymentForm completes a card nonce request
           */
           cardNonceResponseReceived: function (errors, nonce, cardData) {
           if (errors) {
               // Log errors from nonce generation to the browser developer console.
               console.error('Encountered errors:');

               var template = '';
               errors.forEach((error, index) => {
                    template += (index+1) + '. ' + error.message+'\n'
               });

               alert(template)
               return;
           }

            const verificationDetails = { 
                intent: 'CHARGE', 
                    amount: document.getElementById("total_pay").value, 
                    currencyCode: 'GBP', 
                    billingContact: {
                        givenName: 'Jane',
                        familyName: 'Doe'
                    }
                };    
                 //Initiate SCA flow
                paymentForm.verifyBuyer(
                    nonce, 
                    verificationDetails, 
                    function(err, verificationResult) {
                        if (err == null) {
                          var general=document.getElementById("general").value;
                          var management=document.getElementById("management").value;
                          var total_pay=document.getElementById("total_pay").value;
                                const URL = "{!! url('premium/pay') !!}";
                                window.location.replace( URL+'/' + nonce + '/'+idempotency_key+ '/' + verificationResult.token + '/' +general+ '/' +management+ '/'+ total_pay );
                        }
                }); 
            //   alert(`The generated nonce is:\n${nonce}`);
              $('#card-nonce').val(nonce);
        
           }
    },

});
paymentForm.build();

  function onGetCardNonce(event) {
        // Don't submit the form until SqPaymentForm returns with a nonce
        event.preventDefault();
        // Request a nonce from the SqPaymentForm object
        paymentForm.requestCardNonce();
    }

 </script>
@endsection