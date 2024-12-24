<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                <h3 class="font-semibold text-xl pb-4">Subscribe to Delta</h3>

                <x-validation-errors class="mb-4" />

                <table class="mb-10">
                    <tr>
                        <td>Name:</td><td>{{ $account->name }}</td>
                    </tr>
                    <tr>
                        <td>Email:</td> <td>{{ $account->email }}</td>
                    <tr>
                    </tr>
                        <td>IG Username:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td> <td>{{ $account->ig_user }}</td>
                    </tr>
                    </tr>
                        <td>Price:</td> <td>$160</td>
                    </tr>
                </table>

                <form id="signup-form" enctype="multipart/form-data">
                    @csrf

                    <div id="payment_select" class="my-10">
                        <fieldset id="payment_option">
                            <legend>Please select your preferred payment method:</legend>
                            <div>
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

                    @php
                        $have_id = false;
                        $session = \App\Models\Account::getSessionAccount();
                        $id_jdcode = json_decode($session['account']->identity_verification, true);
                        if($id_jdcode != null)
                            $have_id = true;

                    @endphp
                    @if (!$have_id)
                        
                        <div class="my-10">
                            <x-label for="identity_file" value="{{ __('Identity file') }}" />
                            <x-input id="identity_file" class="block mt-1 w-full" type="file" name="identity_file" required />
                            <div id="identity_file_error" class="hidden text-red-600"></div>
                            <br/>
                            Please provide a photo of your driver's license or passport. This will be used to verify your identity. The file will be transmitted to and stored securely on our server only. No third party will receive your identity information. As soon as a member of our staff has verified your identity, the file will be deleted. We require this information to assist us in ensuring our community is a safe and respectful space for our members and staff. We also touch on sensitive issues and need to ensure that each member is over the age of 18.
                        </div>

                    @endif

                    <div>
                        <x-checkbox id="health_attest" name="health_attest" required />
                        By checking this box, The Member attests that they are fully able to participate in an exercise routine of their choice without undue risk.
                    </div>

                    <div>
                        <x-checkbox id="tsandcs" name="tsandcs" required />
                        By checking this box, I state that I have read and accept the terms and conditions as defined in the <a href="https://www.shadow.camp/membership-agreement/" target="_blank" class="underline text-sc-orange-5 hover:no-underline">Membership Agreement</a>.
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="ml-4" id="submit-button">
                            <span class="hidden" id="spinner">Please wait ...</span>
                            <span id="button-text">{{ __('Subscribe') }}</span>
                        </x-button>
                    </div>

                </form>
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
            } else if(selected_option == 'stripe_card') {
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
                body: JSON.stringify({ "product": "camp_delta_migrate", "payment_method_type": payment_method_type }),
            }).then((r) => r.json());

            elements = stripe.elements({ clientSecret, appearance });

            const paymentElement = elements.create("payment");
            paymentElement.mount("#stripe-payment-element");
        }

        function send_form2gqe90() {
            const xhr0 = new XMLHttpRequest();

            const form0 = document.getElementById("signup-form");
            const form0_fd = new FormData(form0);

            xhr0.open("POST", "{{ env('APP_URL') }}subscribe/camp_delta_migrate", false);
            xhr0.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr0.onreadystatechange = () => {
                if (xhr0.readyState === 4) {
                    if(xhr0.status == 200) {
                        send_form2gqe90_success = true;
                    } else {
                        send_form2gqe90_success = false;
                        var response = JSON.parse(xhr0.response);
                        var error_area = document.querySelector('#identity_file_error');
                        error_area.classList.remove("hidden");
                        error_area.innerHTML = response.message;

                        setLoading(false);
                    }
                }
            }

            xhr0.send(form0_fd);
        }

        async function handleSubmit(e) {
            e.preventDefault();
            setLoading(true);

            send_form2gqe90_success = false;
            send_form2gqe90();

            if(send_form2gqe90_success == true) {
                var selected_option = document.querySelector('input[name="payopt"]:checked').value;

                if(selected_option == 'poli') {
                    fetch("/poli/initiate_transaction", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ "product": "camp_delta_migrate" }),
                    }).then((r) => r.json()).then((data) => {
                        console.log(data);
                        if(data.Success == true)
                            window.location.href = data.NavigateURL;
                    });
                } else {
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
            .querySelector("#signup-form")
            .addEventListener("submit", handleSubmit);

        document
            .querySelector("#payment_option")
            .addEventListener("change", payment_select);

    </script>
</x-app-layout>