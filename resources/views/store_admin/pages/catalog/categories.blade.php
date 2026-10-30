@extends('store_admin.layout.master')
@section('contents')
    @php($field = 'w-full min-w-0 rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white')

    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Categories</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Organize your products into a catalog.</p>
        </div>

        @if($errors->any())
            <div class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">
                <ul class="list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <section class="card min-w-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                        <tr>
                            <th class="p-4">Category</th>
                            <th class="p-4">Parent</th>
                            <th class="p-4">Products</th>
                            <th class="p-4">Status</th>
                            <th class="p-4"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $category)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="p-4 font-semibold text-slate-800 dark:text-slate-200">
                                    <div class="flex items-center gap-3">
                                        @if($category->image)
                                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="h-10 w-10 shrink-0 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-700">
                                        @else
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="truncate text-slate-800 dark:text-slate-200">{{ $category->name }}</div>
                                            <div class="truncate text-xs font-normal text-slate-400">{{ $category->slug }}</div>
                                            @if($category->meta_title || $category->h1_heading)
                                                <span class="mt-0.5 inline-flex items-center gap-1 rounded bg-indigo-50 px-1.5 py-0.5 text-[10px] font-medium text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300" title="SEO: {{ $category->meta_title ?? $category->h1_heading }}">
                                                    SEO configured
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">{{ $category->parent?->name ?? 'None' }}</td>
                                <td class="p-4">{{ $category->products_count }}</td>
                                <td class="p-4">{{ $category->status ? 'Active' : 'Inactive' }}</td>
                                <td class="p-4 text-right">
                                    @can('Store Category Edit')
                                        {{-- The panel is position:fixed and placed by the script below, so the table's
                                             overflow wrapper and the card can no longer clip it. --}}
                                        <details class="inline-block text-left" data-popover>
                                            <summary class="cursor-pointer text-indigo-600 font-medium hover:underline">Edit</summary>
                                            <form method="POST" action="{{ route('store.admin.categories.update', $category) }}"
                                                  enctype="multipart/form-data"
                                                  data-popover-panel
                                                  class="invisible fixed z-50 grid max-h-[calc(100vh-2rem)] w-88 sm:w-96 max-w-[calc(100vw-2rem)] gap-3 overflow-y-auto rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                                @csrf @method('PUT')
                                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Name *
                                                    <input name="name" value="{{ $category->name }}" required aria-label="Category name" class="mt-1 {{ $field }}">
                                                </label>
                                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Slug
                                                    <input name="slug" value="{{ $category->slug }}" aria-label="Slug" class="mt-1 {{ $field }}">
                                                </label>
                                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Parent category
                                                    <select name="parent_id" aria-label="Parent category" class="mt-1 {{ $field }}">
                                                        <option value="">No parent</option>
                                                        @foreach($parents->where('id', '!=', $category->id) as $parent)
                                                            <option value="{{ $parent->id }}" @selected((int) $category->parent_id === $parent->id)>{{ $parent->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </label>
                                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Position
                                                    <input name="position" type="number" min="0" value="{{ $category->position }}" aria-label="Position" class="mt-1 {{ $field }}">
                                                </label>

                                                {{-- Category Image --}}
                                                <div>
                                                    <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Category image</label>
                                                    @if($category->image)
                                                        <div class="mt-1 mb-2 flex items-center gap-2">
                                                            <img src="{{ asset('storage/' . $category->image) }}" class="h-12 w-12 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-700">
                                                            <label class="flex items-center gap-1.5 text-xs text-rose-600 cursor-pointer">
                                                                <input type="checkbox" name="remove_image" value="1">
                                                                Remove image
                                                            </label>
                                                        </div>
                                                    @endif
                                                    <input type="file" name="image" accept="image/*" class="mt-1 block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950 dark:file:text-indigo-300">
                                                </div>

                                                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                                    <input type="checkbox" name="status" value="1" @checked($category->status)>
                                                    Active
                                                </label>

                                                {{-- SEO Section --}}
                                                <details class="rounded-lg border border-slate-200 p-2.5 dark:border-slate-700" @if($category->meta_title || $category->h1_heading || $category->canonical_url || $category->meta_description) open @endif>
                                                    <summary class="cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">SEO Settings</summary>
                                                    <div class="mt-2.5 grid gap-2.5">
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Meta Title
                                                            <input name="meta_title" value="{{ $category->meta_title }}" class="mt-1 {{ $field }}">
                                                        </label>
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">H1 Heading
                                                            <input name="h1_heading" value="{{ $category->h1_heading }}" class="mt-1 {{ $field }}">
                                                        </label>
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Canonical URL
                                                            <input name="canonical_url" value="{{ $category->canonical_url }}" placeholder="https://..." class="mt-1 {{ $field }}">
                                                        </label>
                                                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Meta Description
                                                            <textarea name="meta_description" rows="2" class="mt-1 {{ $field }}">{{ $category->meta_description }}</textarea>
                                                        </label>
                                                    </div>
                                                </details>

                                                <button class="rounded-lg bg-indigo-600 p-2 font-medium text-white hover:bg-indigo-700 transition">Save changes</button>
                                            </form>
                                        </details>
                                    @endcan
                                    @can('Store Category Delete')
                                        <form method="POST" action="{{ route('store.admin.categories.destroy', $category) }}" class="inline"
                                              onsubmit="return confirm('Delete this category?')">
                                            @csrf @method('DELETE')
                                            <button class="ml-2 text-rose-600 hover:underline">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-10 text-center text-slate-400">No categories yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="max-w-full overflow-x-auto p-4">{{ $categories->links() }}</div>
            </section>

            @can('Store Category Create')
                <section class="card h-fit min-w-0 p-5">
                    <h2 class="font-bold text-ink dark:text-white">Add category</h2>
                    <form method="POST" action="{{ route('store.admin.categories.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4">
                        @csrf
                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Name *
                            <input name="name" value="{{ old('name') }}" required class="mt-1 {{ $field }}">
                        </label>
                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Slug (optional)
                            <input name="slug" value="{{ old('slug') }}" class="mt-1 {{ $field }}">
                        </label>
                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Parent
                            <select name="parent_id" class="mt-1 {{ $field }}">
                                <option value="">No parent</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Position
                            <input type="number" name="position" min="0" value="{{ old('position', 0) }}" class="mt-1 {{ $field }}">
                        </label>
                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Category image
                            <input type="file" name="image" accept="image/*" class="mt-1 block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950 dark:file:text-indigo-300">
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="status" value="1" @checked(old('status', true))>
                            Active
                        </label>

                        {{-- SEO Section --}}
                        <details class="rounded-lg border border-slate-200 p-2.5 dark:border-slate-700" @if(old('meta_title') || old('h1_heading') || old('canonical_url') || old('meta_description')) open @endif>
                            <summary class="cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">SEO Settings (optional)</summary>
                            <div class="mt-2.5 grid gap-2.5">
                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Meta Title
                                    <input name="meta_title" value="{{ old('meta_title') }}" class="mt-1 {{ $field }}">
                                </label>
                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">H1 Heading
                                    <input name="h1_heading" value="{{ old('h1_heading') }}" class="mt-1 {{ $field }}">
                                </label>
                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Canonical URL
                                    <input name="canonical_url" value="{{ old('canonical_url') }}" placeholder="https://..." class="mt-1 {{ $field }}">
                                </label>
                                <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Meta Description
                                    <textarea name="meta_description" rows="2" class="mt-1 {{ $field }}">{{ old('meta_description') }}</textarea>
                                </label>
                            </div>
                        </details>

                        <button class="rounded-lg bg-indigo-600 p-2 font-semibold text-white hover:bg-indigo-700 transition">Create category</button>
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
                // This panel is tall, so never let it run past the top or bottom of the screen.
                top = Math.max(margin, Math.min(top, window.innerHeight - h - margin));

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
