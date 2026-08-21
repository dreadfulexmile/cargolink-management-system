{{--
    Single reusable confirm dialog. Include once inside each Livewire component's
    root element. Trigger it instead of wire:confirm via:

    <button type="button" x-on:click="$dispatch('confirm-open', @js([
        'title' => 'Delete Lorry',
        'message' => "Delete {$lorry->reg_no}? This cannot be undone.",
        'method' => 'deleteLorry',
        'params' => [$lorry->id],
        'danger' => true,
        'confirmLabel' => 'Delete',
    ]))">Delete</button>

    'method' must be a public method on the enclosing Livewire component; 'params'
    (optional) are passed to it positionally. 'danger' (default true) styles the
    confirm button red vs the brand color; 'confirmLabel' (default "Confirm").
--}}
<div
    x-data="{
        show: false,
        title: '',
        message: '',
        confirmLabel: 'Confirm',
        danger: true,
        method: null,
        params: [],
        open(detail) {
            this.title = detail.title ?? 'Please confirm';
            this.message = detail.message ?? '';
            this.confirmLabel = detail.confirmLabel ?? 'Confirm';
            this.danger = detail.danger ?? true;
            this.method = detail.method;
            this.params = detail.params ?? [];
            this.show = true;
        },
        confirm() {
            if (this.method) {
                $wire.call(this.method, ...this.params);
            }
            this.show = false;
        }
    }"
    x-on:confirm-open.window="open($event.detail)"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: none;"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 dark:bg-slate-900 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 bg-white dark:bg-slate-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full sm:mx-auto sm:my-8"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100" x-text="title"></h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-slate-400" x-text="message"></p>
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-slate-900/50 flex justify-end gap-2">
            <button type="button" x-on:click="show = false" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
            <button type="button" x-on:click="confirm()"
                :class="danger ? 'bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-400' : 'bg-brand-600 hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-400'"
                class="px-4 py-2 text-white rounded-md text-sm font-medium" x-text="confirmLabel"></button>
        </div>
    </div>
</div>
