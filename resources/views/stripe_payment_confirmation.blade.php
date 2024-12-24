<x-app-layout>
    <x-slot name="header">
        @if (isset($_GET['redirect_status']) && $_GET['redirect_status'] == 'succeeded')
            <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
                Payment successful ...
            </h2>
        @else
            <h2 class="font-semibold text-xl text-center text-gray-800 leading-tight">
                Payment processing ...
            </h2>
        @endif
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">

                <div id="stripe-payment-message"></div>

            </div> 
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        //// Init
        const stripe = Stripe("{{ env('STRIPE_KEY') }}");

        //++ Declare functions

        async function checkStatus() {
            const clientSecret = new URLSearchParams(window.location.search).get(
                "payment_intent_client_secret"
            );

            if (!clientSecret) {
                return;
            }

            const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret);

            switch (paymentIntent.status) {
                case "succeeded":
                showMessage("Your payment has succeeded, thank you.");
                break;
                case "processing":
                showMessage("Your payment is processing.");
                break;
                case "requires_payment_method":
                showMessage("Your payment was not successful, please try again.");
                break;
                default:
                showMessage("Something went wrong.");
                break;
            }
        }

        function showMessage(messageText) {
            const messageContainer = document.querySelector("#stripe-payment-message");

            messageContainer.classList.remove("hidden");
            messageContainer.textContent = messageText;
        }

        //-- Declare functions

        checkStatus();
    </script>
</x-app-layout>