<div>
    <div class="max-w-3xl space-y-6">
        <p class="text-sm text-gray-500 dark:text-slate-400">
            Job and invoice numbers are assigned automatically when each is created — you never type them in.
            This just sets where that automatic count picks up, so it can continue an existing paper ledger
            instead of restarting at 1.
        </p>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
            <div>
                <h3 class="font-semibold text-gray-800 dark:text-slate-200">Next Job Number</h3>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    The 4-digit sequence at the end of the job number (e.g. the <strong>3048</strong> in
                    <code class="text-xs bg-gray-100 dark:bg-slate-700 px-1 py-0.5 rounded">IMP/SEA/26/08/3048</code>).
                    Enter the next number to continue from your last paper job file.
                </p>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="nextJobNumber" value="Next Job Number" />
                    <x-text-input id="nextJobNumber" type="number" min="1" class="mt-1 block w-40" wire:model="nextJobNumber" />
                    <x-input-error :messages="$errors->get('nextJobNumber')" class="mt-1" />
                </div>
                <button wire:click="saveJobNumber" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">
                    Save
                </button>
                @if ($jobNumberSaved)
                    <span class="text-sm text-emerald-600 dark:text-emerald-400 pb-2">Saved.</span>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg p-6 space-y-4">
            <div>
                <h3 class="font-semibold text-gray-800 dark:text-slate-200">Next Invoice Number</h3>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    The 4-digit sequence at the end of the invoice number (e.g. the <strong>1290</strong> in
                    <code class="text-xs bg-gray-100 dark:bg-slate-700 px-1 py-0.5 rounded">INV/26/08/1290</code>).
                    Enter the next number to continue from your last paper invoice.
                </p>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="nextInvoiceNumber" value="Next Invoice Number" />
                    <x-text-input id="nextInvoiceNumber" type="number" min="1" class="mt-1 block w-40" wire:model="nextInvoiceNumber" />
                    <x-input-error :messages="$errors->get('nextInvoiceNumber')" class="mt-1" />
                </div>
                <button wire:click="saveInvoiceNumber" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">
                    Save
                </button>
                @if ($invoiceNumberSaved)
                    <span class="text-sm text-emerald-600 dark:text-emerald-400 pb-2">Saved.</span>
                @endif
            </div>
        </div>

        <p class="text-xs text-gray-400 dark:text-slate-500">
            The year and month in the number (e.g. <code class="bg-gray-100 dark:bg-slate-700 px-1 py-0.5 rounded">26/08</code>)
            always reflect today's date when a job or invoice is created — only the running sequence number carries
            over month to month, it never resets.
        </p>
    </div>
</div>
