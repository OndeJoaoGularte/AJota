<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ManageCreditCards extends Component
{
    public $cardId = null;
    public $name;
    public $closing_day;
    public $due_day;
    
    // Novas variáveis
    public $limit;
    public $max_spend;
    public $color = 'purple'; // Cor padrão

    // Lista de cores disponíveis para o usuário escolher
    public $availableColors = [
        'purple' => 'Roxo (Ex: Nubank)',
        'orange' => 'Laranja (Ex: Inter)',
        'blue' => 'Azul (Ex: Neon, CAIXA)',
        'green' => 'Verde (Ex: Sicredi, Next)',
        'red' => 'Vermelho (Ex: Santander)',
        'gray' => 'Cinza / Black (Ex: C6, Black)',
        'yellow' => 'Amarelo (Ex: BB, Will)'
    ];

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'closing_day' => 'required|integer|min:1|max:31',
            'due_day' => 'required|integer|min:1|max:31',
            'limit' => 'required|numeric|min:0',
            'max_spend' => 'nullable|numeric|min:0',
            'color' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Se o max_spend vier vazio do formulário, salvamos como null no banco
        $maxSpendValue = $this->max_spend === '' ? null : $this->max_spend;

        if ($this->cardId) {
            $user->creditCards()->findOrFail($this->cardId)->update([
                'name' => $this->name,
                'closing_day' => $this->closing_day,
                'due_day' => $this->due_day,
                'limit' => $this->limit,
                'max_spend' => $maxSpendValue,
                'color' => $this->color,
            ]);
            session()->flash('card_message', 'Cartão atualizado com sucesso!');
        } else {
            $user->creditCards()->create([
                'name' => $this->name,
                'closing_day' => $this->closing_day,
                'due_day' => $this->due_day,
                'limit' => $this->limit,
                'max_spend' => $maxSpendValue,
                'color' => $this->color,
            ]);
            session()->flash('card_message', 'Cartão adicionado com sucesso!');
        }

        $this->resetForm();
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
        $this->limit = $card->limit;
        $this->max_spend = $card->max_spend;
        $this->color = $card->color;
    }

    public function delete($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->creditCards()->findOrFail($id)->delete();
        session()->flash('card_message', 'Cartão excluído!');
    }

    public function resetForm()
    {
        $this->reset(['cardId', 'name', 'closing_day', 'due_day', 'limit', 'max_spend']);
        $this->color = 'purple';
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