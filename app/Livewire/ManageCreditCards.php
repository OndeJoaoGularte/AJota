<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ManageCreditCards extends Component
{
    // Identifica se estamos editando um cartão existente ou criando um novo
    public $cardId = null;
    public $name;
    public $closing_day;
    public $due_day;

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'closing_day' => 'required|integer|min:1|max:31',
            'due_day' => 'required|integer|min:1|max:31',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($this->cardId) {
            $user->creditCards()->findOrFail($this->cardId)->update([
                'name' => $this->name,
                'closing_day' => $this->closing_day,
                'due_day' => $this->due_day,
            ]);
            session()->flash('card_message', 'Cartão atualizado com sucesso!');
        } else {
            $user->creditCards()->create([
                'name' => $this->name,
                'closing_day' => $this->closing_day,
                'due_day' => $this->due_day,
            ]);
            session()->flash('card_message', 'Cartão adicionado com sucesso!');
        }

        $this->reset(['cardId', 'name', 'closing_day', 'due_day']);
    }

    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $card = $user->creditCards()->findOrFail($id);
        
        $this->cardId = $card->id;
        $this->name = $card->name;
        $this->closing_day = $card->closing_day;
        $this->due_day = $card->due_day;
    }

    public function delete($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->creditCards()->findOrFail($id)->delete();
        session()->flash('card_message', 'Cartão excluído!');
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        return view('livewire.manage-credit-cards', [
            'cards' => $user->creditCards()->orderBy('name')->get()
        ]);
    }
}