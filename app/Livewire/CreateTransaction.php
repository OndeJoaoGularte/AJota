<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreateTransaction extends Component
{
    public $transactionId = null;
    public $description;
    public $amount;
    public $type = 'expense';
    public $date;

    public $categoryId = null;
    public $newCategoryName = '';

    // Nova variável de seleção de meses
    public $selectedMonths = [];

    // Variáveis de parcelamento (Mantidas para compras s/ cartão, ex: Carnê/Boleto)
    public $isInstallment = false;
    public $installmentType = 'installment_value';
    public $currentInstallment = 1;
    public $totalInstallments = 1;

    public function mount()
    {
        // Começa marcando o mês atual
        $this->selectedMonths = [now()->format('Y-m')];
    }

    public function setToday()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function addCategory()
    {
        $this->validate(['newCategoryName' => 'required|string|max:255']);
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $category = $user->categories()->create(['name' => $this->newCategoryName]);
        $this->newCategoryName = '';
        $this->categoryId = $category->id;
        session()->flash('message', 'Categoria criada com sucesso!');
    }

    public function save()
    {
        $this->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'date' => 'required|date',
            'categoryId' => 'nullable|exists:categories,id',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $catId = $this->categoryId === '' ? null : $this->categoryId;

        if ($this->transactionId) {
            $transaction = $user->transactions()->findOrFail($this->transactionId);
            $transaction->update([
                'description' => $this->description,
                'amount' => $this->amount,
                'type' => $this->type,
                'date' => $this->date,
                'category_id' => $catId,
            ]);
            session()->flash('message', 'Registro atualizado!');
        } else {
            if ($this->isInstallment && $this->type === 'expense') {
                $baseAmount = $this->installmentType === 'total_value' 
                    ? ($this->amount / $this->totalInstallments) 
                    : $this->amount;

                for ($i = $this->currentInstallment; $i <= $this->totalInstallments; $i++) {
                    $monthsToAdd = $i - $this->currentInstallment;
                    $installmentDate = Carbon::parse($this->date)->addMonths($monthsToAdd)->format('Y-m-d');
                    
                    $user->transactions()->create([
                        'description' => $this->description,
                        'amount' => $baseAmount,
                        'type' => $this->type,
                        'date' => $installmentDate,
                        'category_id' => $catId,
                        'installment_number' => $i,
                        'total_installments' => $this->totalInstallments,
                        'is_paid' => true, // Lançamento no caixa principal entra como pago
                    ]);
                }
                session()->flash('message', 'Parcelas geradas!');
            } else {
                $user->transactions()->create([
                    'description' => $this->description,
                    'amount' => $this->amount,
                    'type' => $this->type,
                    'date' => $this->date,
                    'category_id' => $catId,
                    'is_paid' => true, 
                ]);
                session()->flash('message', 'Registro adicionado!');
            }
        }
        $this->resetForm();
    }

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
        $this->isInstallment = false; 
    }

    public function delete($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->transactions()->findOrFail($id)->delete();
        session()->flash('message', 'Registro excluído!');
    }

    public function duplicateToNextMonth($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $original = $user->transactions()->findOrFail($id);
        $nextMonthDate = Carbon::parse($original->date)->addMonth()->format('Y-m-d');

        $user->transactions()->create([
            'description' => $original->description,
            'amount' => $original->amount,
            'type' => $original->type,
            'date' => $nextMonthDate,
            'category_id' => $original->category_id,
        ]);
        session()->flash('message', 'Registro duplicado para o mês seguinte!');
    }

    public function resetForm()
    {
        $this->reset(['transactionId', 'description', 'amount', 'date', 'categoryId', 'newCategoryName', 'isInstallment', 'currentInstallment', 'totalInstallments']);
        $this->type = 'expense';
        $this->installmentType = 'installment_value';
    }

    // Função que aplica a busca baseado nos Chips clicados
    private function applyMonthFilter($query)
    {
        if (empty($this->selectedMonths)) {
            $query->whereRaw('1 = 0'); 
        } else {
            $query->where(function ($q) {
                foreach ($this->selectedMonths as $period) {
                    [$y, $m] = explode('-', $period);
                    $q->orWhere(function ($sq) use ($y, $m) {
                        $sq->whereYear('date', $y)->whereMonth('date', $m);
                    });
                }
            });
        }
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. GERA OS MESES DISPONÍVEIS BASEADO NO SEU HISTÓRICO
        $dates = $user->transactions()
            ->whereNull('credit_card_id')
            ->select('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($t) => Carbon::parse($t->date)->format('Y-m'))
            ->unique()->values()->toArray();

        $current = now()->format('Y-m');
        if (!in_array($current, $dates)) $dates[] = $current;
        
        $availableMonths = [];
        $mesesNomes = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        foreach ($dates as $d) {
            [$year, $month] = explode('-', $d);
            $availableMonths[$d] = $mesesNomes[(int)$month] . '/' . substr($year, -2);
        }
        ksort($availableMonths);

        // 2. APLICA O FILTRO E CALCULA AS MATEMÁTICAS
        $incomeQuery = $user->transactions()->whereNull('credit_card_id')->where('type', 'income');
        $this->applyMonthFilter($incomeQuery);
        $totalIncome = $incomeQuery->sum('amount');

        $expenseQuery = $user->transactions()->whereNull('credit_card_id')->where('type', 'expense');
        $this->applyMonthFilter($expenseQuery);
        $totalExpense = $expenseQuery->sum('amount');

        $balance = $totalIncome - $totalExpense;

        $transactionsQuery = $user->transactions()->with('category')->whereNull('credit_card_id');
        $this->applyMonthFilter($transactionsQuery);
        // Lista geral a gente lê de cima para baixo (do mais recente para o mais antigo)
        $transactions = $transactionsQuery->orderBy('date', 'asc')->get();

        $categories = $user->categories()->orderBy('name')->get();

        return view('livewire.create-transaction', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'categories' => $categories,
            'availableMonths' => $availableMonths,
        ]);
    }
}