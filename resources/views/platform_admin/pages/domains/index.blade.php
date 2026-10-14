@extends('platform_admin.layout.master')

@section('contents')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Tenant Domains & Caddy SSL Routing</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage custom domain bindings, subdomains, and automatic SSL provisioning.</p>
            </div>
            <button type="button" onclick="openModal('createDomainModal')" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-sm transition-all gap-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Attach Domain</span>
            </button>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        <th class="py-3.5 px-4">Domain Name</th>
                        <th class="py-3.5 px-4">Tenant / Store</th>
                        <th class="py-3.5 px-4">Domain Type</th>
                        <th class="py-3.5 px-4">Primary</th>
                        <th class="py-3.5 px-4">DNS Verified</th>
                        <th class="py-3.5 px-4">Caddy SSL Status</th>
                        <th class="py-3.5 px-4 w-32 text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-300">
                    @forelse($domains as $dom)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-4 font-semibold">
                                <a href="http://{{ $dom->domain }}" target="_blank" class="inline-flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 hover:underline">
                                    <span>{{ $dom->domain }}</span>
                                    <i class="fa-solid fa-external-link text-[10px] opacity-70"></i>
                                </a>
                            </td>
                            <td class="py-4 px-4 text-slate-900 dark:text-white font-medium">{{ $dom->tenant->name ?? 'N/A' }}</td>
                            <td class="py-4 px-4">
                                @if($dom->is_custom)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-400 border border-purple-200/60 dark:border-purple-900/50">
                                    Custom Domain
                                </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    Subdomain
                                </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($dom->is_primary)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/50">Yes</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">No</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($dom->dns_verified)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/50">
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                    Verified
                                </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-900/50">
                                    <i class="fa-solid fa-clock text-[10px]"></i>
                                    Pending DNS
                                </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @switch($dom->ssl_status)
                                    @case('active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/50">
                                        <i class="fa-solid fa-lock text-[10px]"></i>
                                        SSL Active
                                    </span>
                                        @break
                                    @case('pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-900/50">
                                        <i class="fa-solid fa-spinner animate-spin text-[10px]"></i>
                                        Provisioning
                                    </span>
                                        @break
                                    @case('failed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400 border border-rose-200/60 dark:border-rose-900/50">
                                        <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                        SSL Failed
                                    </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        {{ ucfirst($dom->ssl_status) }}
                                    </span>
                                @endswitch
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" onclick="openModal('editDomainModal{{ $dom->id }}')" class="p-2 text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:bg-sky-50 dark:hover:bg-sky-950/50 rounded-lg transition-colors cursor-pointer" title="Edit Domain">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <form action="{{ route('admin.domains.destroy', $dom->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this domain mapping?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-600 dark:text-rose-400 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors cursor-pointer" title="Delete Mapping">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div id="editDomainModal{{ $dom->id }}" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 dark:bg-slate-950/70 backdrop-blur-xs">
                            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-100 dark:border-slate-800 transform transition-all">
                                <form method="POST" action="{{ route('admin.domains.update', $dom->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Domain Mapping</h3>
                                        <button type="button" onclick="closeModal('editDomainModal{{ $dom->id }}')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition-colors cursor-pointer">
                                            <i class="fa-solid fa-xmark text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Domain Name</label>
                                            <input type="text" name="domain" value="{{ $dom->domain }}" required class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">SSL Provisioning Status</label>
                                            <select name="ssl_status" required class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all cursor-pointer">
                                                <option value="pending" {{ $dom->ssl_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="active" {{ $dom->ssl_status == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="failed" {{ $dom->ssl_status == 'failed' ? 'selected' : '' }}>Failed</option>
                                            </select>
                                        </div>

                                        <!-- Switches -->
                                        <div class="space-y-3 pt-2">
                                            <div class="flex items-center gap-3">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="is_custom" id="customDomEdit{{ $dom->id }}" value="1" class="sr-only peer" {{ $dom->is_custom ? 'checked' : '' }}>
                                                    <div class="w-9 h-5 bg-slate-300 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                                </label>
                                                <label for="customDomEdit{{ $dom->id }}" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">Custom Domain (CNAME)</label>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="is_primary" id="primaryDomEdit{{ $dom->id }}" value="1" class="sr-only peer" {{ $dom->is_primary ? 'checked' : '' }}>
                                                    <div class="w-9 h-5 bg-slate-300 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                                </label>
                                                <label for="primaryDomEdit{{ $dom->id }}" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">Set as Primary Domain</label>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="dns_verified" id="dnsDomEdit{{ $dom->id }}" value="1" class="sr-only peer" {{ $dom->dns_verified ? 'checked' : '' }}>
                                                    <div class="w-9 h-5 bg-slate-300 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                                </label>
                                                <label for="dnsDomEdit{{ $dom->id }}" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">DNS Verified</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                                        <button type="button" onclick="closeModal('editDomainModal{{ $dom->id }}')" class="px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-colors cursor-pointer">Close</button>
                                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">Update Domain</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 dark:text-slate-500 py-12 text-sm">No tenant domain mappings found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createDomainModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 dark:bg-slate-950/70 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-100 dark:border-slate-800 transform transition-all">
            <form method="POST" action="{{ route('admin.domains.store') }}">
                @csrf
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Attach New Domain</h3>
                    <button type="button" onclick="closeModal('createDomainModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Select Tenant</label>
                        <select name="tenant_id" required class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all cursor-pointer">
                            <option value="" disabled selected>-- Select Tenant --</option>
                            @foreach($tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->subdomain }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Domain Name</label>
                        <input type="text" name="domain" required placeholder="mystore.com" class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">SSL Provisioning Status</label>
                        <select name="ssl_status" required class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all cursor-pointer">
                            <option value="pending" selected>Pending</option>
                            <option value="active">Active</option>
                        </select>
                    </div>

                    <!-- Switches -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_custom" id="customDomCreate" value="1" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-300 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                            <label for="customDomCreate" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">Custom Domain</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_primary" id="primaryDomCreate" value="1" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-300 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                            <label for="primaryDomCreate" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">Set as Primary Domain</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="dns_verified" id="dnsDomCreate" value="1" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-300 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                            <label for="dnsDomCreate" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">DNS Verified</label>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closeModal('createDomainModal')" class="px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-colors cursor-pointer">Close</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-sm transition-colors cursor-pointer">Attach Domain</button>
                </div>
            </form>
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
