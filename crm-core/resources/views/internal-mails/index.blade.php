<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Internal Mails') }}
            </h2>
            @if(in_array(auth()->user()->role, ['Admin', 'Manager']))
            <a href="{{ route('internal-mails.create') }}"
                class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700">
                ✉️ Compose Mail
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
                    <p class="font-bold">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Category Filters -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('internal-mails.index') }}"
                        class="px-4 py-2 rounded-lg text-sm font-bold border-2 {{ !$category ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300' }}">
                        All Mails
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('internal-mails.index', ['category' => $cat]) }}"
                            class="px-4 py-2 rounded-lg text-sm font-bold border-2 {{ $category === $cat ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300' }}">
                            {{ $cat }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Inbox Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-slate-400 text-[10px] uppercase font-black tracking-widest bg-slate-50/50">
                                <th class="px-6 py-4 w-12"></th>
                                <th class="px-6 py-4">From</th>
                                <th class="px-6 py-4">Subject</th>
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($mails as $mail)
                                <tr
                                    class="hover:bg-slate-50 transition-colors {{ $mail->isReadByCurrentUser ? '' : 'bg-blue-50/30' }}">
                                    <td class="px-6 py-4">
                                        @if(!$mail->isReadByCurrentUser)
                                            <span class="w-2 h-2 bg-indigo-600 rounded-full block"></span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-900">{{ $mail->sender->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $mail->sender->role }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('internal-mails.show', $mail) }}"
                                            class="font-medium text-slate-900 hover:text-indigo-600">
                                            {{ $mail->subject }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold
                                                {{ $mail->category === 'Compliance Alert' ? 'bg-red-100 text-red-700' : '' }}
                                                {{ $mail->category === 'Target Updates' ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $mail->category === 'Market Holidays' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ $mail->category === 'General' ? 'bg-slate-100 text-slate-700' : '' }}">
                                            {{ $mail->category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ $mail->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('internal-mails.show', $mail) }}"
                                            class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold hover:bg-indigo-200">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                        No mails found.
                                        {{ $category ? "Try a different category." : "Your inbox is empty!" }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($mails->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $mails->links() }}
                    </div>
                @endif
            </div>

            <!-- Unread Count Badge -->
            @if($unreadCount > 0)
                <div class="mt-4 text-center">
                    <p class="text-sm text-slate-500">
                        You have <span class="font-bold text-indigo-600">{{ $unreadCount }}</span> unread mail(s)
                    </p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>