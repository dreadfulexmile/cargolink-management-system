<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Settings')]
class Index extends Component
{
    // Kept in the URL (?tab=numbering) so the tab survives a refresh or a
    // shared link, same as the rest of the app's filter state.
    #[Url]
    public string $tab = 'users';

    public function selectTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
