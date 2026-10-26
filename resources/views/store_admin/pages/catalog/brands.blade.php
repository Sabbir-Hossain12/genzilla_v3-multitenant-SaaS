@extends('store_admin.layout.master')
@section('contents')
    @php($field = 'w-full min-w-0 rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white')

    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Brands</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage product brands for this store.</p>
        </div>

        @if($errors->any())
            <div class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <section class="card min-w-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                        <tr>
                            <th class="p-4">Brand</th>
                            <th class="p-4">Products</th>
                            <th class="p-4">Status</th>
                            <th class="p-4"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($brands as $brand)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="p-4 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $brand->name }}
                                    <div class="text-xs font-normal text-slate-400">{{ $brand->slug }}</div>
                                </td>
                                <td class="p-4">{{ $brand->products_count }}</td>
                                <td class="p-4">{{ $brand->status ? 'Active' : 'Inactive' }}</td>
                                <td class="p-4 text-right">
                                    @can('Store Brand Edit')
                                        {{-- The panel is position:fixed and placed by the script below, so the table's
                                             overflow wrapper and the card can no longer clip it. --}}
                                        <details class="inline-block text-left" data-popover>
                                            <summary class="cursor-pointer text-indigo-600">Edit</summary>
                                            <form method="POST" action="{{ route('store.admin.brands.update', $brand) }}"
                                                  data-popover-panel
                                                  class="invisible fixed z-50 grid w-64 max-w-[calc(100vw-2rem)] gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                                @csrf @method('PUT')
                                                <input name="name" value="{{ $brand->name }}" required aria-label="Brand name" class="{{ $field }}">
                                                <input name="slug" value="{{ $brand->slug }}" aria-label="Slug" class="{{ $field }}">
                                                <label class="flex items-center gap-2 text-sm">
                                                    <input type="checkbox" name="status" value="1" @checked($brand->status)>
                                                    Active
                                                </label>
                                                <button class="rounded-lg bg-indigo-600 p-2 text-white">Save</button>
                                            </form>
                                        </details>
                                    @endcan
                                    @can('Store Brand Delete')
                                        <form method="POST" action="{{ route('store.admin.brands.destroy', $brand) }}" class="inline"
                                              onsubmit="return confirm('Delete this brand?')">
                                            @csrf @method('DELETE')
                                            <button class="ml-2 text-rose-600">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-10 text-center text-slate-400">No brands yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="max-w-full overflow-x-auto p-4">{{ $brands->links() }}</div>
            </section>

            @can('Store Brand Create')
                <section class="card h-fit min-w-0 p-5">
                    <h2 class="font-bold text-ink dark:text-white">Add brand</h2>
                    <form method="POST" action="{{ route('store.admin.brands.store') }}" class="mt-4 grid gap-4">
                        @csrf
                        <label>Name
                            <input name="name" required class="mt-1 {{ $field }}">
                        </label>
                        <label>Slug (optional)
                            <input name="slug" class="mt-1 {{ $field }}">
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="status" value="1" checked>
                            Active
                        </label>
                        <button class="rounded-lg bg-indigo-600 p-2 font-semibold text-white">Create brand</button>
                    </form>
                </section>
            @endcan
        </div>
    </div>

    <script>
        (function () {
            // A native <details> only closes when its own <summary> is clicked, so outside clicks and Escape
            // are handled here. Only one popover stays open at a time.
            let active = null;

            function position(details, panel) {
                const trigger = details.querySelector('summary').getBoundingClientRect();
                const w = panel.offsetWidth;
                const h = panel.offsetHeight;
                const gap = 8, margin = 8;

                // Right edge of the panel lines up with the trigger, then stays inside the viewport.
                let left = Math.max(margin, Math.min(trigger.right - w, window.innerWidth - w - margin));
                let top = trigger.bottom + gap;

                // Not enough room below: open above the trigger if that fits.
                if (top + h > window.innerHeight - margin && trigger.top - gap - h > margin) {
                    top = trigger.top - gap - h;
                }

                panel.style.left = left + 'px';
                panel.style.top = top + 'px';
            }

            function closeAll(except) {
                document.querySelectorAll('details[data-popover][open]').forEach(function (d) {
                    if (d !== except) d.removeAttribute('open');
                });
            }

            document.addEventListener('toggle', function (e) {
                const d = e.target;
                if (!(d instanceof HTMLDetailsElement) || !d.hasAttribute('data-popover')) return;

                const panel = d.querySelector('[data-popover-panel]');
                if (!panel) return;

                if (d.open) {
                    closeAll(d);
                    active = d;
                    panel.classList.add('invisible');   // stay hidden until positioned
                    position(d, panel);
                    panel.classList.remove('invisible');
                } else {
                    panel.classList.add('invisible');
                    if (active === d) active = null;
                }
            }, true);

            function reposition() {
                if (!active) return;
                const panel = active.querySelector('[data-popover-panel]');
                if (panel) position(active, panel);
            }

            // The panel is fixed, so follow the trigger when the page or the table scrolls or resizes.
            window.addEventListener('scroll', reposition, true);
            window.addEventListener('resize', reposition);

            document.addEventListener('click', function (e) {
                document.querySelectorAll('details[data-popover][open]').forEach(function (d) {
                    if (!d.contains(e.target)) d.removeAttribute('open');
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeAll(null);
            });
        })();
    </script>
@endsection
