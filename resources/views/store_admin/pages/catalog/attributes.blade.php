@extends('store_admin.layout.master')
@section('contents')
    @php($field = 'w-full min-w-0 rounded-lg border-slate-300 bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-white')

    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Product attributes</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Create options such as size and color, then add reusable values.</p>
        </div>

        @if($errors->any())
            <div class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">{{ $errors->first() }}</div>
        @endif

        {{-- minmax(0,1fr) stops long content from stretching the list column past the page --}}
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <section class="min-w-0 space-y-4">
                @forelse($attributes as $attribute)
                    {{-- No overflow-hidden here: it clipped the edit popovers. --}}
                    <article class="card">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-4 dark:border-slate-800">
                            <div class="min-w-0">
                                <h2 class="break-words font-bold text-slate-900 dark:text-white">{{ $attribute->name }}</h2>
                                <p class="text-xs text-slate-400">{{ $attribute->values_count }} values - position {{ $attribute->position }}</p>
                            </div>

                            <div class="flex shrink-0 gap-3 items-center">
                                @can('Store Attribute Edit')
                                    <details class="relative" data-popover>
                                        <summary class="cursor-pointer text-sm text-indigo-600">Edit</summary>
                                        <form method="POST" action="{{ route('store.admin.attributes.update', $attribute) }}"
                                              data-popover-panel data-side="right"
                                              class="absolute right-0 z-20 mt-2 grid w-64 max-w-[calc(100vw-2rem)] gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                            @csrf @method('PUT')
                                            <input name="name" value="{{ $attribute->name }}" required aria-label="Attribute name" class="{{ $field }}">
                                            <input name="position" type="number" min="0" value="{{ $attribute->position }}" aria-label="Position" class="{{ $field }}">
                                            <button class="rounded-lg bg-indigo-600 p-2 text-white cursor-pointer">Save</button>
                                        </form>
                                    </details>
                                @endcan

                                @can('Store Attribute Delete')
                                    <form method="POST" action="{{ route('store.admin.attributes.destroy', $attribute) }}"
                                          onsubmit="return confirm('Delete this attribute and its values?')">
                                        @csrf @method('DELETE')
                                        <button class="text-sm text-rose-600 cursor-pointer">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 p-4">
                            @forelse($attribute->values as $value)
                                <details class="relative max-w-full" data-popover>
                                    <summary class="max-w-full cursor-pointer break-words rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                        {{ $value->value }}
                                        @unless($value->status)
                                            <span class="text-xs text-slate-400">(inactive)</span>
                                        @endunless
                                    </summary>

                                    @can('Store Attribute Edit')
                                        <form method="POST" action="{{ route('store.admin.attributes.values.update', [$attribute, $value]) }}"
                                              data-popover-panel data-side="left"
                                              class="absolute left-0 z-20 mt-2 grid w-64 max-w-[calc(100vw-2rem)] gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                            @csrf @method('PUT')
                                            <input name="value" value="{{ $value->value }}" required aria-label="Value" class="{{ $field }}">
                                            <input type="number" name="position" min="0" value="{{ $value->position }}" aria-label="Position" class="{{ $field }}">
                                            <label class="flex items-center gap-2 text-sm">
                                                <input type="checkbox" name="status" value="1" @checked($value->status)>
                                                Active
                                            </label>
                                            <button class="rounded-lg bg-indigo-600 p-2 text-white cursor-pointer">Save value</button>
                                        </form>
                                    @endcan
                                </details>
                            @empty
                                <span class="text-sm text-slate-400">No values yet.</span>
                            @endforelse
                        </div>

                        @can('Store Attribute Edit')
                            <form method="POST" action="{{ route('store.admin.attributes.values.store', $attribute) }}"
                                  class="flex flex-wrap items-end gap-3 rounded-b-xl border-t border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-800/30">
                                @csrf
                                {{-- min-w keeps the input from being squeezed to nothing on narrow screens; it wraps instead --}}
                                <label class="min-w-[9rem] flex-1 text-sm">New value
                                    <input name="value" required class="mt-1 {{ $field }}">
                                </label>
                                <label class="w-24 text-sm">Position
                                    <input type="number" name="position" min="0" value="0" class="mt-1 {{ $field }}">
                                </label>
                                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white cursor-pointer">Add value</button>
                            </form>
                        @endcan
                    </article>
                @empty
                    <div class="card p-10 text-center text-sm text-slate-400">No attributes defined.</div>
                @endforelse

                <div class="max-w-full overflow-x-auto">{{ $attributes->links() }}</div>
            </section>

            @can('Store Attribute Create')
                <section class="card h-fit min-w-0 p-5">
                    <h2 class="font-bold text-ink dark:text-white">Add attribute</h2>
                    <form method="POST" action="{{ route('store.admin.attributes.store') }}" class="mt-4 grid gap-4">
                        @csrf
                        <label>Name
                            <input name="name" required class="mt-1 {{ $field }}">
                        </label>
                        <label>Slug (optional)
                            <input name="slug" class="mt-1 {{ $field }}">
                        </label>
                        <label>Position
                            <input type="number" name="position" min="0" value="0" class="mt-1 {{ $field }}">
                        </label>
                        <button class="rounded-lg bg-indigo-600 p-2 font-semibold text-white cursor-pointer">Create attribute</button>
                    </form>
                </section>
            @endcan
        </div>
    </div>

    <script>
        (function () {
            // Edit popovers: open one at a time, keep them on screen, close on outside click or Escape.
            function place(panel) {
                panel.classList.remove('left-0', 'right-0');
                panel.classList.add(panel.dataset.side === 'right' ? 'right-0' : 'left-0');

                const r = panel.getBoundingClientRect();
                if (r.right > window.innerWidth - 8) {
                    panel.classList.remove('left-0');
                    panel.classList.add('right-0');
                } else if (r.left < 8) {
                    panel.classList.remove('right-0');
                    panel.classList.add('left-0');
                }
            }

            function closeAll(except) {
                document.querySelectorAll('details[data-popover][open]').forEach(function (d) {
                    if (d !== except) d.removeAttribute('open');
                });
            }

            document.addEventListener('toggle', function (e) {
                const d = e.target;
                if (!(d instanceof HTMLDetailsElement) || !d.hasAttribute('data-popover') || !d.open) return;

                closeAll(d);
                const panel = d.querySelector('[data-popover-panel]');
                if (panel) place(panel);
            }, true);

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
