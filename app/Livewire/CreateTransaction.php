<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreateTransaction extends Component
{
    // Variáveis do formulário básico
    public $transactionId = null;
    public $description;
    public $amount;
    public $type = 'expense';
    public $date;

    // Variáveis das Categorias
    public $categoryId = null;
    public $newCategoryName = '';

    // Variáveis de Tempo para navegação entre meses
    public $currentMonth;
    public $currentYear;

    // Variáveis dos Cartões de Crédito
    public $creditCardId = null;
    public $isInstallment = false;
    public $installmentType = 'installment_value';
    public $currentInstallment = 1;
    public $totalInstallments = 1;

    // Função que roda quando o componente é montado na tela pela primeira vez
    public function mount()
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    // Função para recuar um mês
    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    // Função para avançar um mês
    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    // Cria uma nova categoria diretamente no formulário de transação, para agilizar o processo de cadastro de despesas/receitas
    public function addCategory()
    {
        // Valida se você digitou algo
        $this->validate([
            'newCategoryName' => 'required|string|max:255'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Salva a nova gaveta no banco de dados
        $category = $user->categories()->create([
            'name' => $this->newCategoryName
        ]);

        // Limpa o campinho de texto e já deixa a categoria nova selecionada para você!
        $this->newCategoryName = '';
        $this->categoryId = $category->id;

        session()->flash('message', 'Categoria criada com sucesso!');
    }

    /**
     * Função chamada quando o formulário é enviado.
     * Serve tanto para criar novos registros quanto para atualizar existentes. Identifica se é edição, uma criação 
     * normal ou uma criação com parcelas, e trata cada caso adequadamente.
     **/
    public function save()
    {
        // Validação dos dados digitados pelo usuário
        $this->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'date' => 'required|date',
            'categoryId' => 'nullable|exists:categories,id',
            'creditCardId' => 'nullable|exists:credit_cards,id',
        ]);

        // Evita erros no VS Code indicando o tipo exato do usuário logado
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Tratamento para transformar texto vazio em nulo pro banco
        $catId = $this->categoryId === '' ? null : $this->categoryId;
        $cardId = $this->creditCardId === '' ? null : $this->creditCardId;

        // Lógica de Criação ou Atualização
        if ($this->transactionId) {
            // Se transactionId não for nulo, buscamos o registro no banco e atualizamos
            $transaction = $user->transactions()->findOrFail($this->transactionId);
            $transaction->update([
                'description' => $this->description,
                'amount' => $this->amount,
                'type' => $this->type,
                'date' => $this->date,
                'category_id' => $catId,
                'credit_card_id' => $cardId,
            ]);
            session()->flash('message', 'Registro atualizado com sucesso!');
        } else {
            // Verifica se é uma despesa parcelada
            if ($this->isInstallment && $this->type === 'expense') {
                
                // Calcula qual é o valor real da parcela com base no tipo escolhido
                $baseAmount = $this->installmentType === 'total_value' 
                    ? ($this->amount / $this->totalInstallments) 
                    : $this->amount;

                // Loop para gerar cada parcela no seu respectivo mês futuro
                for ($i = $this->currentInstallment; $i <= $this->totalInstallments; $i++) {
                    
                    // Avança 1 mês na data para cada parcela extra
                    $monthsToAdd = $i - $this->currentInstallment;
                    $installmentDate = Carbon::parse($this->date)->addMonths($monthsToAdd)->format('Y-m-d');
                    
                    $user->transactions()->create([
                        'description' => $this->description,
                        'amount' => $baseAmount,
                        'type' => $this->type,
                        'date' => $installmentDate,
                        'category_id' => $catId,
                        'credit_card_id' => $cardId,
                        'installment_number' => $i,
                        'total_installments' => $this->totalInstallments,
                    ]);
                }
                session()->flash('message', 'Parcelas geradas com sucesso!');
            } else {
                // Criação de registro único (Pix, Dinheiro, etc)
                $user->transactions()->create([
                    'description' => $this->description,
                    'amount' => $this->amount,
                    'type' => $this->type,
                    'date' => $this->date,
                    'category_id' => $catId,
                    'credit_card_id' => $cardId,
                ]);
                session()->flash('message', 'Registro adicionado com sucesso!');
            }
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
        $this->categoryId = $transaction->category_id;
        $this->creditCardId = $transaction->credit_card_id;
        // Na edição, bloqueamos a recriação de parcelas para evitar duplicidade
        $this->isInstallment = false; 
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

    // Função para duplicar uma transação para o próximo mês, facilitando o cadastro de despesas/receitas recorrentes
    public function duplicateToNextMonth($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Busca a transação original
        $original = $user->transactions()->findOrFail($id);
        
        // Pega a data original e soma exatamente 1 mês
        $nextMonthDate = \Carbon\Carbon::parse($original->date)->addMonth()->format('Y-m-d');

        // Cria a cópia no banco de dados
        $user->transactions()->create([
            'description' => $original->description,
            'amount' => $original->amount,
            'type' => $original->type,
            'date' => $nextMonthDate,
            'category_id' => $original->category_id,
            'credit_card_id' => $original->credit_card_id,
            // Propositalmente não copiamos as numerações de parcelas
            // para que seja tratada como uma conta recorrente normal.
        ]);

        session()->flash('message', 'Registro duplicado para o próximo mês!');
    }

    /**
     * Reseta as variáveis para limpar o formulário e voltar ao modo "Criação".
     **/
    public function resetForm()
    {
        $this->reset(['transactionId', 'description', 'amount', 'date', 'categoryId', 'newCategoryName', 'creditCardId', 'isInstallment', 'currentInstallment', 'totalInstallments']);
        $this->type = 'expense';
        $this->installmentType = 'installment_value';
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

        // Cálculo dos totais de receitas, despesas e saldo para o mês atual
        $totalIncome = $user->transactions()->where('type', 'income')->whereMonth('date', $this->currentMonth)->whereYear('date', $this->currentYear)->sum('amount');
        $totalExpense = $user->transactions()->where('type', 'expense')->whereMonth('date', $this->currentMonth)->whereYear('date', $this->currentYear)->sum('amount');
        $balance = $totalIncome - $totalExpense;

        $transactions = $user->transactions()
            ->with(['category', 'creditCard']) // Traz cartão e categoria juntos
            ->whereMonth('date', $this->currentMonth)
            ->whereYear('date', $this->currentYear)
            ->orderBy('date', 'desc')
            ->get();

        $categories = $user->categories()->orderBy('name')->get();
        $creditCards = $user->creditCards()->orderBy('name')->get(); // Puxa seus cartões

        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        $nomeMes = $meses[$this->currentMonth];

        return view('livewire.create-transaction', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'categories' => $categories,
            'creditCards' => $creditCards,
            'nomeMes' => $nomeMes,
        ]);
    }
}