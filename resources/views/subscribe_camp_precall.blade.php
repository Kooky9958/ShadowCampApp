@php
    // PROMO39TMP
    $amount = (time() > strtotime('2023-10-19 20:00:00 Pacific/Auckland') && time() < strtotime('2023-10-23 12:00:00 Pacific/Auckland')) ? 40.00 : 140.00 ;
@endphp
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                <h3 class="font-semibold text-xl pb-4">Subscribe to Precall</h3>

                <x-validation-errors class="mb-4" />

                <table class="mb-10">
                    <tr>
                        <td>Name:</td><td>{{ $account->name }}</td>
                    </tr>
                    <tr>
                        <td>Email:</td> <td>{{ $account->email }}</td>
                    <tr>
                    </tr>
                        <td>Price:</td> <td>${{ $amount }}</td>
                    </tr>
                </table>

                @if ($spaces_available)
                    <form id="payment-form">
                        @csrf

                        <div id="payment_select" class="my-10">
                            <fieldset id="payment_option">
                                <legend>Please select your preferred payment method:</legend>
                                <div>
                                    {{-- show referral codes  --}}
                                    {{-- @if(time() <= strtotime('2024-03-09 23:59:59 Pacific/Auckland')) --}}
                                    <div class="p-5">
                                        <input type="radio" id="payopt_discount"
                                        name="payopt" value="discount">
                                        <label for="discount_code">Get a free subscription with a referral code you got from a friend:</label>
                                        <x-input id="discount_code" class="block mt-1 w-full" type="text" name="discount_code" />
                                    </div>
                                    {{-- @endif --}}
                                    <div class="p-5">
                                        <input type="radio" id="payopt_poli"
                                        name="payopt" value="poli">
                                        <label for="payopt_poli">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/assets/poli_logo.jpg" alt="POLi Internet Banking" class="inline">&nbsp;&nbsp;&nbsp;&nbsp;Pay via internet banking with POLi</label>
                                    </div>
                                    <div class="p-5">
                                        <input type="radio" id="payopt_card"
                                        name="payopt" value="stripe_card">
                                        <label for="payopt_card">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/assets/cc_wa.png" alt="Visa/Mastercard" class="inline">&nbsp;&nbsp;&nbsp;&nbsp;Pay with Credit/Debit card</label>
                                    </div>
                                    <div class="p-5">
                                        <input type="radio" id="payopt_afterpay"
                                        name="payopt" value="stripe_afterpay">
                                        <label for="payopt_afterpay">&nbsp;&nbsp;&nbsp;&nbsp;<img src="/assets/afterpay_logo.jpg" alt="Afterpay" class="inline">&nbsp;&nbsp;&nbsp;&nbsp;Pay with Afterpay</label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <div id="stripe-elements" class="hidden">
                            <div id="stripe-payment-element" class="my-10">
                                <!--Stripe.js injects the Payment Element-->
                            </div>

                            <div id="stripe-payment-message" class="hidden mb-10 text-xl text-sc-brown border-b-2 border-sc-brown"></div>
                        </div>

                        <div>
                            <x-checkbox id="health_attest" name="health_attest" required />
                            By checking this box, The Member attests that they are fully able to participate in an exercise routine of their choice without undue risk.
                        </div>

                        <div>
                            <x-checkbox id="tsandcs" name="tsandcs" required />
                            By checking this box, I state that I have read and accept the terms and conditions as defined in the <a href="https://www.shadow.camp/membership-agreement/" target="_blank" class="underline text-sc-orange-5 hover:no-underline">Membership Agreement</a>.
                        </div>

                        <div id="error_area" class="hidden text-red-600"></div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-button class="ml-4" id="submit-button">
                                <span class="hidden" id="spinner">Please wait ...</span>
                                <span id="button-text">{{ __('Subscribe') }}</span>
                            </x-button>
                        </div>

                    </form>
                @else
                    <p>
                        There are currently no spaces available on the precall camp
                    </p>
                @endif

                
            </div> 
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        //// Init
        const appearance = {
            theme: 'stripe',

            variables: {
                colorPrimary: '#e5d5c6'
            }
        };

        const stripe = Stripe("{{ env('STRIPE_KEY') }}");

        //++ Declare functions

        function payment_select() {
            var selected_option = document.querySelector('input[name="payopt"]:checked').value;

            if(selected_option == 'poli') {
                poli_initialize();
            } else if(selected_option == 'discount') {
                // Do nothing
            } 
            else if(selected_option == 'stripe_card') {
                stripe_initialize('card')
            } else if(selected_option == 'stripe_afterpay') {
                stripe_initialize('afterpay_clearpay')
            } else {
                stripe_initialize('auto');
            }
        }

        function poli_initialize() {
            const div_stripe_elements = document.querySelector("#stripe-elements");
            div_stripe_elements.classList.add("hidden");
        }

        async function stripe_initialize(payment_method_type) {
            const div_stripe_elements = document.querySelector("#stripe-elements");
            div_stripe_elements.classList.remove("hidden");

            const { clientSecret } = await fetch("/stripe/create_pay_intent", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ "product": "camp_precall", "payment_method_type": payment_method_type }),
            }).then((r) => r.json());

            elements = stripe.elements({ clientSecret, appearance });

            const paymentElement = elements.create("payment");
            paymentElement.mount("#stripe-payment-element");
        }

        
        async function handleSubmit(e) {
            e.preventDefault();
            setLoading(true);

            var selected_option = document.querySelector('input[name="payopt"]:checked');

            if(selected_option == null) {
                var error_area = document.querySelector('#error_area');
                error_area.classList.remove("hidden");
                error_area.innerHTML = "Please select a payment method.";
                setLoading(false);
                return;
            }

            selected_option = selected_option.value;

            if(selected_option == 'poli') {
                fetch("/poli/initiate_transaction", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: JSON.stringify({ "product": "camp_precall" }),
                }).then((r) => r.json()).then((data) => {
                    console.log(data);
                    if(data.Success == true)
                        window.location.href = data.NavigateURL;
                });
            } 
            else if(selected_option == 'discount') {
                var discount_code = document.querySelector('#discount_code').value;
                if(discount_code.length < 10) {
                    var error_area = document.querySelector('#error_area');
                    error_area.classList.remove("hidden");
                    error_area.innerHTML = "Discount code input is invalid. Please check and try again.";
                }
                else {
                    const xhr0 = new XMLHttpRequest();

                    const form0 = document.getElementById("payment-form");
                    const form0_fd = new FormData(form0);

                    xhr0.open("POST", "{{ env('APP_URL') }}subscribe/camp_precall", false);
                    xhr0.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                    xhr0.onreadystatechange = () => {
                        if (xhr0.readyState === 4) {
                            if(xhr0.status == 200) {
                                window.location.href = '{{ env('APP_URL') }}purchase/confirmation';
                            } else {
                                send_form2gqe90_success = false;
                                var response = JSON.parse(xhr0.response);
                                var error_area = document.querySelector('#error_area');
                                error_area.classList.remove("hidden");
                                error_area.innerHTML = response.message;

                                setLoading(false);
                            }
                        }
                    }

                    xhr0.send(form0_fd);
                }
            }
            else 
            {
                const { error } = await stripe.confirmPayment({
                    elements,
                    confirmParams: {
                    return_url: "{{ env('APP_URL') }}stripe/confirmation",
                    },
                });

                if (error.type === "card_error" || error.type === "validation_error") {
                    showMessage(error.message);
                } else {
                    showMessage("An unexpected error occurred.");
                }
            }

            setLoading(false);
        }

        function showMessage(messageText) {
            const messageContainer = document.querySelector("#stripe-payment-message");

            messageContainer.classList.remove("hidden");
            messageContainer.textContent = messageText;

            setTimeout(function () {
                messageContainer.classList.add("hidden");
                messageText.textContent = "";
            }, 8000);
        }

        function setLoading(isLoading) {
            if (isLoading) {
                // Disable the button and show a spinner
                document.querySelector("#submit-button").disabled = true;
                document.querySelector("#spinner").classList.remove("hidden");
                document.querySelector("#button-text").classList.add("hidden");
            } else {
                document.querySelector("#submit-button").disabled = false;
                document.querySelector("#spinner").classList.add("hidden");
                document.querySelector("#button-text").classList.remove("hidden");
            }
        }

        //-- Declare functions

        document
            .querySelector("#payment-form")
            .addEventListener("submit", handleSubmit);

        document
            .querySelector("#payment_option")
            .addEventListener("change", payment_select);

    </script>
</x-app-layout>