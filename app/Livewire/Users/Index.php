<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

// Rendered as a tab inside Settings\Index — see resources/views/livewire/settings/index.blade.php.
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showTrashed = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $role = 'staff';

    // Set right after creating a user or resetting their password — the only
    // moment the plaintext is ever available. Shown once in the modal, then
    // cleared; never persisted anywhere except as the user's password hash.
    public ?string $generatedPassword = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->getRoleNames()->first() ?? 'staff';
        $this->generatedPassword = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        if ($this->editingId) {
            $this->validate([
                'name' => 'required|string|max:255',
                'role' => 'required|in:gm,co-gm,staff',
            ]);

            $user = User::findOrFail($this->editingId);
            $user->update(['name' => $this->name]);
            $user->syncRoles([$this->role]);

            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|in:gm,co-gm,staff',
        ]);

        // No self-registration and no mailer configured for this app (see
        // CLAUDE.md — shared hosting, single cron) — so a random password is
        // generated here and handed to the GM to relay, rather than emailed.
        $password = Str::password(20);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($password),
        ]);
        $user->assignRole($this->role);

        $this->editingId = $user->id;
        $this->generatedPassword = $password;
    }

    public function resetPassword(int $id): void
    {
        $user = User::findOrFail($id);
        $password = Str::password(20);
        $user->update(['password' => Hash::make($password)]);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->getRoleNames()->first() ?? 'staff';
        $this->generatedPassword = $password;
    }

    // Soft-deletes the account (see CascadesSoftDeletes note on User — none
    // needed here, jobs.salesperson_id is nullOnDelete, not cascading). A GM
    // can never deactivate their own account — that would lock them out with
    // no one left to reactivate them.
    public function deactivate(int $id): void
    {
        if ($id === auth()->id()) {
            return;
        }

        User::findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->cancel();
        }
    }

    public function reactivate(int $id): void
    {
        User::onlyTrashed()->findOrFail($id)->restore();
    }

    public function toggleShowTrashed(): void
    {
        $this->showTrashed = ! $this->showTrashed;
    }

    public function dismissGeneratedPassword(): void
    {
        $this->generatedPassword = null;
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'generatedPassword']);
        $this->role = 'staff';
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->paginate(15);

        $trashedUsers = $this->showTrashed
            ? User::onlyTrashed()->with('roles')->orderBy('name')->get()
            : collect();

        return view('livewire.users.index', [
            'users' => $users,
            'trashedUsers' => $trashedUsers,
        ]);
    }
}
