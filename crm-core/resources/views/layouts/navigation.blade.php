<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        @php $logo = \App\Models\SystemSetting::get('company_logo'); @endphp
                        @if($logo)
                            <img src="{{ Storage::url($logo) }}" alt="Logo" class="h-8 max-w-[120px] object-contain">
                        @else
                            <div class="text-xl font-black text-indigo-600 tracking-tighter">{{ \App\Models\SystemSetting::get('company_name', config('app.name')) }}</div>
                        @endif
                    </a>
                </div>

                <!-- Global Search -->
                <div class="hidden sm:flex items-center ms-6">
                    <form action="{{ route('leads.index') }}" method="GET" class="relative">
                        <input type="text" name="search" placeholder="Search leads..."
                            class="bg-gray-100 border-none rounded-full text-xs px-10 py-2 focus:ring-indigo-500 w-64"
                            value="{{ request('search') }}">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </form>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Live Status Indicator -->
                    <div class="flex items-center gap-2 ml-4 border-l pl-4 h-8 my-auto">
                        <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Status:</span>
                        <span id="user-status-badge"
                            class="px-2 py-1 rounded text-xs font-bold uppercase bg-gray-100 text-gray-600">
                            Connecting...
                        </span>
                    </div>

                    <x-nav-link :href="route('leads.index')" :active="request()->routeIs('leads.*')">
                        {{ __('Leads') }}
                    </x-nav-link>
                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                        {{ __('Clients') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('agent.learning.index')" :active="request()->routeIs('agent.learning.*')" class="relative">
                        {{ __('My Academy') }}
                        @if(\App\Models\TrainingLog::where('user_id', Auth::id())->where('completion_status', 'pending')->exists() || 
                            \App\Models\TrainingModule::where(function($q) {
                                $q->where('target_team', 'All')->orWhere('target_team', Auth::user()->role);
                            })->whereDoesntHave('logs', function($q) {
                                $q->where('user_id', Auth::id());
                            })->exists())
                            <span class="absolute top-3 right-0 -mr-2 w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        @endif
                    </x-nav-link>

                    <!-- Compliance Dropdown — All Employees -->
                    <div class="hidden sm:flex sm:items-center sm:ms-4">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('kyc-rpm.*', 'message-templates.*') ? 'border-indigo-400 text-gray-900 border-b-2' : '' }}">
                                    <div>{{ __('Compliance') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('kyc-rpm.index')">{{ __('KYC / RPM Dashboard') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('message-templates.index')">{{ __('Message Templates') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Business Dropdown -->
                    @if(in_array(auth()->user()->role, ['Admin', 'Manager', 'SBA']))
                        <div class="hidden sm:flex sm:items-center sm:ms-4">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('payments.sales-orders', 'advisory-calls.*', 'reports.*') ? 'border-indigo-400 text-gray-900 border-b-2' : '' }}">
                                        <div>{{ __('Business') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link
                                        :href="route('payments.sales-orders')">{{ __('Sales Orders') }}</x-dropdown-link>
                                    <x-dropdown-link
                                        :href="route('advisory-calls.index')">{{ __('Market Calls') }}</x-dropdown-link>
                                    <x-dropdown-link :href="route('reports.index')">{{ __('Reports') }}</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif

                    <!-- Team Dropdown -->
                    @if(in_array(auth()->user()->role, ['Admin', 'Manager']))
                        <div class="hidden sm:flex sm:items-center sm:ms-4">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('employees.*', 'attendance.*', 'targets.*', 'admin.training.*') ? 'border-indigo-400 text-gray-900 border-b-2' : '' }}">
                                        <div>{{ __('Team') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link
                                        :href="route('employees.index')">{{ __('Employees') }}</x-dropdown-link>
                                    <x-dropdown-link
                                        :href="route('attendance.index')">{{ __('Attendance') }}</x-dropdown-link>
                                    <x-dropdown-link :href="route('targets.index')">{{ __('Targets') }}</x-dropdown-link>
                                    @if(auth()->user()->role === 'Admin')
                                    <x-dropdown-link :href="route('admin.training.index')">{{ __('Training Manager') }}</x-dropdown-link>
                                    @endif
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif

                    <!-- Insights Dropdown -->
                    @if(in_array(auth()->user()->role, ['Admin', 'Manager']))
                        <div class="hidden sm:flex sm:items-center sm:ms-4">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('ai.*', 'analytics.*') ? 'border-indigo-400 text-gray-900 border-b-2' : '' }}">
                                        <div>{{ __('Insights') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('ai.index')">{{ __('AI Analysis') }}</x-dropdown-link>
                                    <x-dropdown-link
                                        :href="route('analytics.index')">{{ __('Smart Analysis') }}</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif

                    <!-- System Dropdown -->
                    @if(auth()->user()->role === 'Admin')
                        <div class="hidden sm:flex sm:items-center sm:ms-4">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('activities.index', 'admin.logs') ? 'border-indigo-400 text-gray-900 border-b-2' : '' }}">
                                        <div>{{ __('System') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link
                                        :href="route('activities.index')">{{ __('Activity Logs') }}</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.logs')">{{ __('System Logs') }}</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.data-access')">{{ __('Data Access Report') }}</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.consents')">{{ __('Consent Dashboard') }}</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif

                    <x-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')" class="relative">
                        {{ __('Chat') }}
                        <span id="nav-chat-badge"
                            class="hidden ml-2 px-1.5 py-0.5 bg-red-500 text-white rounded-full text-[10px] font-bold">0</span>
                    </x-nav-link>
                    <x-nav-link :href="route('internal-mails.index')" :active="request()->routeIs('internal-mails.*')"
                        class="relative">
                        {{ __('Mails') }}
                        <span id="nav-mail-badge"
                            class="hidden ml-2 px-1.5 py-0.5 bg-red-500 text-white rounded-full text-[10px] font-bold">0</span>
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leads.index')" :active="request()->routeIs('leads.*')">
                {{ __('Leads') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                {{ __('Clients') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('agent.learning.index')" :active="request()->routeIs('agent.learning.*')">
                🎓 {{ __('My Academy') }}
            </x-responsive-nav-link>

            <!-- Business Section -->
            @if(in_array(auth()->user()->role, ['Admin', 'Manager', 'SBA']))
                <div class="px-4 py-2 border-t border-gray-100 bg-gray-50/50">
                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('Business') }}</span>
                </div>
                <x-responsive-nav-link :href="route('payments.sales-orders')"
                    :active="request()->routeIs('payments.sales-orders')">
                    {{ __('Sales Orders') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('advisory-calls.index')"
                    :active="request()->routeIs('advisory-calls.*')">
                    {{ __('Market Calls') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                    {{ __('Reports') }}
                </x-responsive-nav-link>
            @endif

            <!-- Team Section -->
            @if(in_array(auth()->user()->role, ['Admin', 'Manager']))
                <div class="px-4 py-2 border-t border-gray-100 bg-gray-50/50">
                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('Team') }}</span>
                </div>
                <x-responsive-nav-link :href="route('employees.index')" :active="request()->routeIs('employees.*')">
                    {{ __('Employees') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.*')">
                    {{ __('Attendance') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('targets.index')" :active="request()->routeIs('targets.*')">
                    {{ __('Targets') }}
                </x-responsive-nav-link>
                @if(auth()->user()->role === 'Admin')
                <x-responsive-nav-link :href="route('admin.training.index')" :active="request()->routeIs('admin.training.*')">
                    {{ __('Training Manager') }}
                </x-responsive-nav-link>
                @endif
            @endif

            <!-- Insights Section -->
            @if(in_array(auth()->user()->role, ['Admin', 'Manager']))
                <div class="px-4 py-2 border-t border-gray-100 bg-gray-50/50">
                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('Insights') }}</span>
                </div>
                <x-responsive-nav-link :href="route('ai.index')" :active="request()->routeIs('ai.*')">
                    {{ __('AI Analysis') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('analytics.index')" :active="request()->routeIs('analytics.*')">
                    {{ __('Smart Analysis') }}
                </x-responsive-nav-link>
            @endif

            <!-- System Section -->
            @if(auth()->user()->role === 'Admin')
                <div class="px-4 py-2 border-t border-gray-100 bg-gray-50/50">
                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('System') }}</span>
                </div>
                <x-responsive-nav-link :href="route('activities.index')" :active="request()->routeIs('activities.*')">
                    {{ __('Activity Logs') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.logs')" :active="request()->routeIs('admin.logs')">
                    {{ __('System Logs') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.data-access')" :active="request()->routeIs('admin.data-access')">
                    {{ __('Data Access Report') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.consents')" :active="request()->routeIs('admin.consents')">
                    {{ __('Consent Dashboard') }}
                </x-responsive-nav-link>
            @endif

            <div class="border-t border-gray-100"></div>

            <x-responsive-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')">
                💬 {{ __('Chat') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('internal-mails.index')"
                :active="request()->routeIs('internal-mails.*')">
                📧 {{ __('Mails') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
