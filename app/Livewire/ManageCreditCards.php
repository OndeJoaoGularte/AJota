<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On; // <-- Importante para ouvir eventos!

class ManageCreditCards extends Component
{
    public $showForm = false; // Começa invisível!

    public $cardId = null;
    public $name;
    public $closing_day;
    public $due_day;
    public $limit;
    public $max_spend;
    public $color = 'purple';

    public $availableColors = [
        'purple' => 'Roxo (Ex: Nubank)',
        'orange' => 'Laranja (Ex: Inter)',
        'blue' => 'Azul (Ex: Neon, CAIXA)',
        'green' => 'Verde (Ex: Sicredi, Next)',
        'red' => 'Vermelho (Ex: Santander)',
        'gray' => 'Cinza / Black (Ex: C6, Black)',
        'yellow' => 'Amarelo (Ex: BB, Will)'
    ];

    // Ouve o grito de "Novo Cartão"
    #[On('trigger-create-card')]
    public function openCreate()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    // Ouve o grito de "Editar Cartão"
    #[On('trigger-edit-card')]
    public function openEdit($id)
    {
        $this->edit($id);
        $this->showForm = true;
    }

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

        $this->showForm = false; // Esconde o formulário
        $this->dispatch('cards-updated'); // Avisa a outra tela para atualizar as Abas
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

    // Agora deletamos baseado no cartão que está aberto no formulário
    public function delete()
    {
        if ($this->cardId) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->creditCards()->findOrFail($this->cardId)->delete();
            
            $this->showForm = false;
            $this->dispatch('cards-updated');
            session()->flash('card_message', 'Cartão e compras excluídos!');
        }
    }

    public function resetForm()
    {
        $this->reset(['cardId', 'name', 'closing_day', 'due_day', 'limit', 'max_spend']);
        $this->color = 'purple';
    }

    public function closeForm()
    {
        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.manage-credit-cards');
    }
}
