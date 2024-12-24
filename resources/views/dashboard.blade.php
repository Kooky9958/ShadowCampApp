<x-app-layout>

    @if ($account && ($account->hasActiveSubTo('camp_delta_migrate') || $account->hasActiveSubTo('camp_delta9')))
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200 flex">
                <div style="background-image: url('assets/live_cover_img.png'); height: 150px; width: 150px; background-size: cover; background-repeat:   no-repeat; background-position: center center; display: flex; align-items: center; justify-content: center;">
                    <div style="height: 100px; width: 100px;">
                        <a href="/watch/live/7v19165hgb">
                            <img src="/assets/play_symbol.png" alt="" style="opacity: 0.35;">
                        </a>
                    </div>
                </div>
                <div class="mx-4 w-1/2">
                    <h3 class="font-bold text-lg text-gray-800 pb-1 b">LIVE STREAM</h3>
                    <p class="text-sc-orange-1">
                        Workout with Moni LIVE every weekday morning at 615am New Zealand Standard Time.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200 flex">
                <div style="background-image: url('{{ '/storage/'.$video_most_recent->coverimage_path }}'); height: 150px; width: 150px; background-size: cover; background-repeat:   no-repeat; background-position: center center; display: flex; align-items: center; justify-content: center;">
                    <div style="height: 100px; width: 100px;">
                        <a href="/watch/{{ $video_most_recent->url_id }}">
                            <img src="/assets/play_symbol.png" alt="" style="opacity: 0.35;">
                        </a>
                    </div>
                </div>
                <div class="mx-4 w-1/2">
                    <h3 class="font-bold text-lg text-gray-800 pb-1">LATEST VIDEO</h3>
                    <p class="flex flex-col justify-between h-1/2">
                        <span class="text-sc-orange-1">{{ $video_most_recent->name }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                <h3 class="font-bold text-lg text-gray-800 pb-1">Featured Playlists</h3>
                <ul>
                    <li class="py-2">
                        <a href="/watch/playlist/f00f518abf" class="underline text-sc-orange-5">Shadow Lives</a>
                    </li>
                    <li class="py-2">
                        <a href="/watch/playlist/77a1b7c3dd" class="underline text-sc-orange-5">Joy</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                <h3 class="font-bold text-lg text-gray-800 pb-1">Orientation</h3>
                <ul>
                    <li class="py-2">
                        <a href="storage/tmp-rce/IF%20YOU%20ARE%20NEW%20HERE%20PAVE.pdf" class="underline text-sc-orange-5">New here? See the introductory guide</a>
                    </li>
                    <li class="py-2">
                        <a href="storage/tmp-rce/pave_routine.pdf" class="underline text-sc-orange-5">Routine guide</a>
                    </li>
                    <li class="py-2">
                        <a href="/watch/playlist/ec5955d2b8" class="underline text-sc-orange-5">How-to videos (The Basics videos)</a>
                    </li>
                    <li class="py-2">
                        <a href="/pc/resource_list/9517966d67" class="underline text-sc-orange-5">Nutrition resources</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    @endif

    @if ($account && $account->hasActiveSubTo('camp_precall'))
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
            <h3 class="font-semibold text-lg pb-4">Your Precall Dawn Journey BEGINS!</h3>
                <div class="p-5">
                    1. To start, please watch the live videos where Moni gives instruction and guidance on how to proceed through the camp.<br/>
                    <div style="padding: 20px; margin-top: 10px;">
                        <a href="/watch/playlist/71f94853bd">
                            <x-button>
                                {{ __('WATCH LIVE VIDEOS') }}
                            </x-button>
                        </a>
                    </div>
                </div>
                <div class="p-5">
                    2. You can find an explanation of this week's Timetable and Classes here:
                    <div style="padding: 20px; margin-top: 10px;">
                        <a href="/pc/resource_lists">
                            <x-button>
                                {{ __('TIMETABLE & CLASS BREAKDOWN') }}
                            </x-button>
                        </a>
                    </div>
                </div>
                <div class="p-5">
                    3. New workout videos will appear each day at 06:00 AM.<br/>
                    <div style="padding: 20px; margin-top: 10px;">
                        <a href="/watch/playlist/0600b606ed">
                            <x-button>
                                {{ __('WATCH WORKOUT VIDEOS') }}
                            </x-button>
                        </a>
                    </div>
                </div>
                <div class="p-5">
                    4. Video, Photo and PDF resources are available to support you in your journey.<br/>
                    <div style="padding: 20px; margin-top: 10px;">
                        <a href="/pc/resource_list/be021c72be">
                            <x-button>
                                {{ __('SEE RESOURCES') }}
                            </x-button>
                        </a>
                    </div>
                </div>
                <div class="p-5">
                    5. Disruptions and setbacks happen, here are some videos to help you pivot if you encounter a difficulty.<br/>
                    <div style="padding: 20px; margin-top: 10px;">
                        <a href="/watch/playlist/4a030b3ff4">
                            <x-button>
                                {{ __('WATCH PIVOT VIDEOS') }}
                            </x-button>
                        </a>
                    </div>
                </div>
                <div class="p-10">
                    Note: New videos and content will appear daily as you progress.
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="py-4">
        @php
            if ($account) {
                $nextSubscriptionProducts = $account->getNextSubscriptionProducts();
                $active_subs = $account->getActiveSubs();
                $mr_sub_start_date = end($active_subs)['start_date'] ?? "";
            } else {
                $nextSubscriptionProducts = [];
                $active_subs = [];
                $mr_sub_start_date = "";
            }
        @endphp
        @if (in_array('camp_delta_resubscribe', $nextSubscriptionProducts))
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                <h3 class="font-semibold text-lg pb-4">Subscription</h3>
                <p>
                    @if ($account->hasActiveSubTo('camp_delta9'))
                        You have <span class="text-green-600">successfuly subscribed</span> to Delta PAVE.<br/>
                        <br/>
                        Your next payment is due by {{ date('d M Y', strtotime('2024-02-26 +9 weeks')) }}.
                    @else
                        Delta Crew, it is now time to <span class="text-red-600">resubscribe and pay</span> for your next camp PAVE 2.0 (February - March 2024).<br/>
                        <br/>
                        <a>
                        <x-button onclick="window.location.href='/subscribe/camp_delta';">
                            RESUBSCRIBE NOW
                        </x-button>   
                    @endif
                </p>       
            </div> 
        </div>
        @elseif(in_array('camp_delta_migrate', $nextSubscriptionProducts) || ($account && $account->hasActiveSubTo('camp_delta_migrate')))
        @php
            $asubs = $account->getActiveSubs(20);
            $intro = (array_key_exists('camp_precall', $asubs)) ? true : false ;
            $dm_renew = (array_key_exists('camp_delta_migrate', $asubs) && $asubs['camp_delta_migrate']['start_date']+(7*7*24*60*60) <= time()) ? true : false ;

        @endphp
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                    <h3 class="font-semibold text-lg pb-4">Subscription</h3>
                    @if ($intro)
                    <p>Congratulations 🥳 you have completed Precall Dawn. You now have the opportuniy to move to our live community. </p>
                    @elseif ($dm_renew)
                    <p>🚨 Your delta subscription will expire soon. It is time to pay for the next 8 weeks. </p>
                    @endif
                    <p>
                        @if ($account->hasActiveSubTo('camp_delta_migrate') && !$dm_renew)
                            You have <span class="text-green-600">successfuly subscribed</span> to Delta Camp. <br>
                            <br>
                            Your next payment is due by {{ date('d M Y', strtotime(date('Y-m-d', $mr_sub_start_date).' +9 weeks')) }}.
                        @elseif ($dm_renew)
                            <span class="text-red-600">Resubscribe and pay</span> for your Delta Camp.<br/>
                            <br/>
                            <a>
                            <x-button onclick="window.location.href='/subscribe/camp_delta_migrate';">
                                RESUBSCRIBE NOW
                            </x-button>  
                        @else
                            <span class="text-red-600">Subscribe and pay</span> for your Delta Camp.<br/>
                            <br/>
                            <a>
                            <x-button onclick="window.location.href='/subscribe/camp_delta_migrate';">
                                SUBSCRIBE NOW
                            </x-button>   
                        @endif
                    </p>       
                </div> 
            </div>
        @elseif (in_array('camp_precall', $nextSubscriptionProducts) || ($account && $account->hasActiveSubTo('camp_precall')))
        @php
            // PROMO39TMP
            $amount = (time() > strtotime('2023-10-19 20:00:00 Pacific/Auckland') && time() < strtotime('2023-10-23 12:00:00 Pacific/Auckland')) ? 40.00 : 140.00 ;
        @endphp
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow sm:rounded-lg py-6 px-6 border border-gray-200">
                    <h3 class="font-semibold text-lg pb-4">Subscription</h3>
                    <div class="p-10">
                        Precall Dawn is an 8 Week PT Specific Pre Recorded camp that takes you through everything you need to know to join us in our live community. Week day workouts, education lives, and nutrition plans to get you started along with all the information to keep you going!<br/>
                        <br/>
                        THIS IS NOT YOUR USUAL ONLINE WORKOUT PROGRAM. This is an invitation into Monis life, her whānau, her thoughts and her passions. Along with that comes a little wild, a little raw, a lot of real, genuine emotion and an unwavering will to show up daily regardless of what life is throwing her!<br/>
                        <br/>
                        <br/>
                        <span class="text-lg font-medium">${{ $amount }}</span><br/>
                    </div>
                    <p>
                        @if ($account->hasActiveSubTo('camp_precall'))
                            You have <span class="text-green-600">successfuly subscribed</span> to Precall.
                        @else
                            You have <span class="text-red-600">not subscribed</span> to Precall.<br/>
                            <br/>
                            <a>
                            <x-button onclick="window.location.href='/subscribe/camp_precall';">
                                SUBSCRIBE NOW
                            </x-button>   
                        @endif
                    </p>       
                </div> 
            </div>
        @endif
    </div>
</x-app-layout>