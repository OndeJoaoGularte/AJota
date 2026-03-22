<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CreateTransaction extends Component
{
    // Variáveis de estado do componente (ligadas ao wire:model no HTML)
    public $transactionId = null; // Guarda o ID se estivermos no modo "Edição"
    public $description;
    public $amount;
    public $type = 'expense'; // Define 'Saída' como valor padrão no select
    public $date;

    /**
    * Função chamada quando o formulário é enviado.
    * Serve tanto para criar novos registros quanto para atualizar existentes.
    **/
    public function save()
    {
        // Validação dos dados digitados pelo usuário
        $this->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'date' => 'required|date',
        ]);

        // Evita erros no VS Code indicando o tipo exato do usuário logado
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Lógica de Criação ou Atualização
        if ($this->transactionId) {
            // Se transactionId não for nulo, buscamos o registro no banco e atualizamos
            $transaction = $user->transactions()->findOrFail($this->transactionId);
            $transaction->update([
                'description' => $this->description,
                'amount' => $this->amount,
                'type' => $this->type,
                'date' => $this->date,
            ]);
            session()->flash('message', 'Registro atualizado com sucesso!');
        } else {
            // Se for nulo, criamos um registro novo atrelado ao usuário
            $user->transactions()->create([
                'description' => $this->description,
                'amount' => $this->amount,
                'type' => $this->type,
                'date' => $this->date,
            ]);
            session()->flash('message', 'Registro adicionado com sucesso!');
        }

        // Limpa os campos após salvar
        $this->resetForm();
    }

    /**
    * Busca os dados de uma transação específica no banco 
    * e os coloca nas variáveis públicas para preencher o formulário.
    **/
    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $transaction = $user->transactions()->findOrFail($id);
        
        $this->transactionId = $transaction->id;
        $this->description = $transaction->description;
        $this->amount = $transaction->amount;
        $this->type = $transaction->type;
        $this->date = $transaction->date;
    }

    /**
    * Exclui o registro do banco de dados permanentemente.
    **/
    public function delete($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $user->transactions()->findOrFail($id)->delete();
        session()->flash('message', 'Registro excluído com sucesso!');
    }

    /**
    * Reseta as variáveis para limpar o formulário e voltar ao modo "Criação".
    **/
    public function resetForm()
    {
        $this->reset(['transactionId', 'description', 'amount', 'date']);
        $this->type = 'expense';
    }

    /**
    * Renderiza a view na tela.
    * Toda vez que uma variável pública muda, esta função roda novamente,
    * recalculando os saldos e buscando a tabela atualizada.
    **/
    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Pega o mês e o ano atual do servidor
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Soma apenas as receitas do mês atual
        $totalIncome = $user->transactions()
            ->where('type', 'income')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        // Soma apenas as saídas do mês atual
        $totalExpense = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $balance = $totalIncome - $totalExpense;

        // Busca todas as transações para montar a tabela, da mais nova para a mais antiga
        $transactions = $user->transactions()
            ->orderBy('date', 'desc')
            ->get();

        // Passa os cálculos para a view Blade
        return view('livewire.create-transaction', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
        ]);
    }
}