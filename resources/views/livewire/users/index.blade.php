<div>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{{ __('Users') }}</h2>

        <div class="flex items-center justify-between gap-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search users..."
                class="w-full max-w-sm rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 shadow-sm">
            <button type="button" wire:click="create" x-data x-on:click="$dispatch('open-modal', 'user-details')"
                class="shrink-0 inline-flex items-center px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium hover:bg-brand-700 dark:hover:bg-brand-400">
                + New User
            </button>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-900/50 text-left text-gray-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($users as $user)
                        <tr class="text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-slate-100 max-w-[220px]">
                                <button type="button" wire:click="edit({{ $user->id }})" x-data x-on:click="$dispatch('open-modal', 'user-details')"
                                    class="hover:text-brand-600 dark:hover:text-brand-400 hover:underline text-left inline-flex items-center gap-2 min-w-0 max-w-full">
                                    <span class="truncate" title="{{ $user->name }}">{{ $user->name }}</span>
                                    @if ($user->id === auth()->id())
                                        <span class="shrink-0 text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400">You</span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-4 py-3 max-w-[220px] truncate" title="{{ $user->email }}">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full uppercase tracking-wide
                                    {{ $user->hasRole('gm') ? 'bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300' }}">
                                    {{ $user->getRoleNames()->first() ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $user->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $users->links() }}

        <div>
            <button type="button" wire:click="toggleShowTrashed" class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200 hover:underline">
                {{ $showTrashed ? 'Hide deactivated users' : 'Show deactivated users' }}
            </button>

            @if ($showTrashed)
                <div class="mt-3 bg-white dark:bg-slate-800 shadow-sm rounded-lg overflow-hidden">
                    @if ($trashedUsers->isEmpty())
                        <p class="px-4 py-3 text-sm text-gray-400 dark:text-slate-500">No deactivated users.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-slate-900/50 text-left text-gray-500 dark:text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Role</th>
                                    <th class="px-4 py-3">Deactivated</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                @foreach ($trashedUsers as $user)
                                    <tr class="text-gray-400 dark:text-slate-500">
                                        <td class="px-4 py-3 max-w-[220px] truncate" title="{{ $user->name }}">{{ $user->name }}</td>
                                        <td class="px-4 py-3 max-w-[220px] truncate" title="{{ $user->email }}">{{ $user->email }}</td>
                                        <td class="px-4 py-3 uppercase text-xs">{{ $user->getRoleNames()->first() ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $user->deleted_at->format('Y-m-d') }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" wire:click="reactivate({{ $user->id }})" class="text-brand-600 dark:text-brand-400 hover:underline text-sm font-medium">
                                                Reactivate
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <x-modal name="user-details" maxWidth="md">
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-lg text-gray-800 dark:text-slate-200">
                    {{ $editingId ? 'User Details' : 'New User' }}
                </h3>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300">
                    <x-icon name="close" class="w-5 h-5" />
                </button>
            </div>

            @if ($generatedPassword)
                <div class="rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4 space-y-2">
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                        Password for {{ $email }} — shown once, copy it now:
                    </p>
                    <div class="flex items-center gap-2">
                        <code id="generated-password" class="flex-1 px-3 py-2 rounded-md bg-white dark:bg-slate-900 text-sm font-mono text-gray-900 dark:text-slate-100 select-all">{{ $generatedPassword }}</code>
                        <button type="button"
                            x-data
                            x-on:click="navigator.clipboard.writeText(document.getElementById('generated-password').textContent)"
                            class="shrink-0 px-3 py-2 rounded-md bg-white dark:bg-slate-900 text-xs font-medium text-gray-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 ring-1 ring-gray-200 dark:ring-slate-700">
                            Copy
                        </button>
                    </div>
                    <p class="text-xs text-amber-700 dark:text-amber-400">
                        Hand this to them directly — it won't be shown again. They can change it from their Profile page after logging in.
                    </p>
                    <button type="button" wire:click="dismissGeneratedPassword" class="text-xs font-medium text-amber-800 dark:text-amber-300 hover:underline">
                        I've copied it — dismiss
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <x-input-label for="modal_user_name" value="Name" />
                        <x-text-input id="modal_user_name" type="text" class="mt-1 block w-full" wire:model="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="modal_user_email" value="Email" />
                        <x-text-input id="modal_user_email" type="email" class="mt-1 block w-full" wire:model="email" :disabled="(bool) $editingId" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        @if ($editingId)
                            <p class="mt-1 text-xs text-gray-400 dark:text-slate-500">Email can't be changed after the account is created.</p>
                        @endif
                    </div>
                    <div>
                        <x-input-label for="modal_user_role" value="Role" />
                        <select id="modal_user_role" wire:model="role" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            <option value="staff">Staff — operational access</option>
                            <option value="gm">GM — full access</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-1" />
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div class="flex gap-2">
                        <button wire:click="save" class="px-4 py-2 bg-brand-600 dark:bg-brand-500 text-white rounded-md text-sm font-medium">Save</button>
                        <button type="button" wire:click="cancel" x-on:click="$dispatch('close')" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-md text-sm font-medium">Cancel</button>
                    </div>

                    @if ($editingId)
                        <div class="flex items-center gap-3">
                            <button type="button" x-on:click="$dispatch('confirm-open', @js([
                                    'title' => 'Reset Password',
                                    'message' => "Generate a new password for {$name}? Their current password stops working immediately.",
                                    'method' => 'resetPassword',
                                    'params' => [$editingId],
                                    'confirmLabel' => 'Reset Password',
                                ]))" class="text-sm text-gray-500 dark:text-slate-400 hover:underline">
                                Reset Password
                            </button>

                            @if ($editingId === auth()->id())
                                <span class="text-xs text-gray-400 dark:text-slate-500">Can't deactivate your own account.</span>
                            @else
                                <button type="button" x-on:click="$dispatch('confirm-open', @js([
                                        'title' => 'Deactivate User',
                                        'message' => "Deactivate {$name}? They won't be able to log in, but their history is kept and this can be undone.",
                                        'method' => 'deactivate',
                                        'params' => [$editingId],
                                        'confirmLabel' => 'Deactivate',
                                    ])); $dispatch('close')" class="text-sm text-red-600 dark:text-red-400 hover:underline">
                                    Deactivate
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-modal>

    <x-confirm-modal />
</div>
