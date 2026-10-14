@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Contact Messages</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Review and manage incoming inquiries from the website contact form.</p>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        <th class="py-3.5 px-4 w-16">#</th>
                        <th class="py-3.5 px-4">Sender Name</th>
                        <th class="py-3.5 px-4">Email & Phone</th>
                        <th class="py-3.5 px-4">Company</th>
                        <th class="py-3.5 px-4">Message Preview</th>
                        <th class="py-3.5 px-4 w-28">Status</th>
                        <th class="py-3.5 px-4 w-36">Submitted At</th>
                        <th class="py-3.5 px-4 w-32 text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-300">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-4 font-medium text-slate-500 dark:text-slate-400">
                                {{ $loop->iteration }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-900 dark:text-white">{{ $contact->name }}</td>
                            <td class="py-4 px-4">
                                <a href="mailto:{{ $contact->email }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">{{ $contact->email }}</a>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $contact->phone ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 text-slate-600 dark:text-slate-400">{{ $contact->company_name ?? '-' }}</td>
                            <td class="py-4 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">{{ Str::limit($contact->message, 70) }}</td>
                            <td class="py-4 px-4">
                                @switch($contact->status)
                                    @case('new')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400 border border-rose-200/60 dark:border-rose-900/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        New
                                    </span>
                                        @break
                                    @case('contacted')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-900/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Contacted
                                    </span>
                                        @break
                                    @case('resolved')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Resolved
                                    </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        {{ ucfirst($contact->status) }}
                                    </span>
                                @endswitch
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-500 dark:text-slate-400">
                                {{ $contact->created_at ? $contact->created_at->format('M d, Y H:i') : '-' }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" onclick="openModal('viewContactModal{{ $contact->id }}')" class="p-2 text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:bg-sky-50 dark:hover:bg-sky-950/50 rounded-lg transition-colors cursor-pointer" title="View Message">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                    <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this message?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-600 dark:text-rose-400 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors cursor-pointer" title="Delete Message">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- View & Status Modal -->
                        <div id="viewContactModal{{ $contact->id }}" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 dark:bg-slate-950/70 backdrop-blur-xs">
                            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 dark:border-slate-800 transform transition-all">
                                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Contact Message from {{ $contact->name }}</h3>
                                    <button type="button" onclick="closeModal('viewContactModal{{ $contact->id }}')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition-colors cursor-pointer">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </div>

                                <div class="p-6 space-y-5">
                                    <!-- Details Grid -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                                        <div class="space-y-1.5">
                                            <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">Email</p>
                                            <p class="font-medium text-slate-900 dark:text-white">
                                                <a href="mailto:{{ $contact->email }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $contact->email }}</a>
                                            </p>
                                        </div>
                                        <div class="space-y-1.5">
                                            <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">Phone</p>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $contact->phone ?? 'N/A' }}</p>
                                        </div>
                                        <div class="space-y-1.5">
                                            <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">Company</p>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $contact->company_name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="space-y-1.5">
                                            <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">Submitted Date</p>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $contact->created_at ? $contact->created_at->format('M d, Y H:i A') : 'N/A' }}</p>
                                        </div>
                                    </div>

                                    <!-- Full Message Box -->
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Full Message</label>
                                        <div class="p-4 bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-sm whitespace-pre-wrap leading-relaxed">
                                            {{ $contact->message }}
                                        </div>
                                    </div>

                                    <!-- Status Update Form -->
                                    <form method="POST" action="{{ route('admin.contacts.update-status', $contact->id) }}" class="pt-2 border-t border-slate-100 dark:border-slate-800">
                                        @csrf
                                        @method('PATCH')
                                        <div class="flex flex-col sm:flex-row items-end sm:items-center gap-4">
                                            <div class="w-full sm:flex-1">
                                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Update Status</label>
                                                <select name="status" class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all cursor-pointer">
                                                    <option value="new" {{ $contact->status == 'new' ? 'selected' : '' }}>New</option>
                                                    <option value="contacted" {{ $contact->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                                    <option value="resolved" {{ $contact->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                                </select>
                                            </div>
                                            <div class="w-full sm:w-auto">
                                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-all gap-2 cursor-pointer">
                                                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                                                    <span>Save Status</span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="flex items-center justify-end px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                                    <button type="button" onclick="closeModal('viewContactModal{{ $contact->id }}')" class="px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-colors cursor-pointer">Close</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-slate-400 dark:text-slate-500 py-12 text-sm">No contact messages received yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Lightweight Modal Handlers Script -->
    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        }
    </script>
@endsection
