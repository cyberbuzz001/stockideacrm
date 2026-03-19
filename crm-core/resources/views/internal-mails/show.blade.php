<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $mail->subject }}
            </h2>
            <a href="{{ route('internal-mails.index') }}"
                class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-200">
                Back to Inbox
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <!-- Mail Header -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-6">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-600 text-lg">
                            {{ strtoupper(substr($mail->sender->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900">{{ $mail->sender->name }}</p>
                            <p class="text-sm text-slate-500">{{ $mail->sender->role }} • {{ $mail->sender->email }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        {{ $mail->category === 'Compliance Alert' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $mail->category === 'Target Updates' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $mail->category === 'Market Holidays' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $mail->category === 'General' ? 'bg-slate-100 text-slate-700' : '' }}">
                        {{ $mail->category }}
                    </span>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <p class="text-xs text-slate-400 uppercase tracking-widest font-black mb-1">Sent</p>
                    <p class="text-sm text-slate-600">{{ $mail->created_at->format('l, F d, Y \a\t h:i A') }}</p>
                </div>

                @if($mail->attachment_path)
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <p class="text-xs text-slate-400 uppercase tracking-widest font-black mb-2">Attachment</p>
                        <a href="{{ asset('storage/' . $mail->attachment_path) }}" download
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-bold hover:bg-indigo-100">
                            Attachment: {{ basename($mail->attachment_path) }}
                        </a>
                    </div>
                @endif
            </div>

            <!-- Mail Body -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-6">
                <div class="prose max-w-none">
                    {!! nl2br(e($mail->body)) !!}
                </div>
            </div>

            <!-- Read Receipts -->
            @if(auth()->user()->role === 'Admin' || $mail->sender_id === auth()->id())
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <h3 class="text-lg font-black text-slate-900 mb-4">Read Receipts</h3>

                    @if($mail->readReceipts->count() > 0)
                        <div class="space-y-3">
                            @foreach($mail->readReceipts as $receipt)
                                <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center font-bold text-green-600 text-sm">
                                            {{ strtoupper(substr($receipt->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-sm text-slate-900">{{ $receipt->user->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $receipt->user->role }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-green-600 font-bold">Read</p>
                                        <p class="text-xs text-slate-400">{{ $receipt->read_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <p class="text-sm text-slate-500">
                                <span class="font-bold text-slate-700">{{ $mail->readReceipts->count() }}</span>
                                of
                                <span class="font-bold text-slate-700">
                                    {{ in_array('all', $mail->recipient_ids) ? \App\Models\User::count() - 1 : count($mail->recipient_ids) }}
                                </span>
                                recipients have read this mail
                            </p>
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">No one has read this mail yet.</p>
                    @endif
                </div>
            @endif

            <!-- Delete Button (for sender or admin) -->
            @if(auth()->user()->role === 'Admin' || $mail->sender_id === auth()->id())
                <div class="mt-6">
                    <form action="{{ route('internal-mails.destroy', $mail) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this mail?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-100 text-red-700 rounded-xl text-sm font-bold hover:bg-red-200">
                            Delete Mail
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
