<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreditCardInvoice extends Component
{
    public $selectedCardId = null;
    public $selectedMonths = [];

    public $showForm = false;
    public $transactionId = null;
    public $description;
    public $amount;
    public $date;
    public $categoryId = null;

    public $isInstallment = false;
    public $installmentType = 'total_value';
    public $totalInstallments = 2;

    public function handleCardsUpdated()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $firstCard = $user->creditCards()->first();
        if ($firstCard && !$user->creditCards()->find($this->selectedCardId)) {
            $this->selectedCardId = $firstCard->id;
        } elseif (!$firstCard) {
            $this->selectedCardId = null;
        }
    }

    public function mount()
    {
        $this->selectedMonths = [now()->format('Y-m')];
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $firstCard = $user->creditCards()->first();
        if ($firstCard) {
            $this->selectedCardId = $firstCard->id;
        }
    }

    public function selectCard($id)
    {
        $this->selectedCardId = $id;
        $this->resetForm();
    }

    // --- FUNÇÃO DE PAGAR A FATURA ---
    public function payInvoice()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Pega as compras do cartão selecionado, nos meses marcados, que AINDA NÃO FORAM PAGAS
        $query = $user->transactions()
            ->where('credit_card_id', $this->selectedCardId)
            ->where('type', 'expense')
            ->where('is_paid', false);

        $this->applyMonthFilter($query);
        $unpaidTransactions = $query->get();

        $totalToPay = $unpaidTransactions->sum('amount');

        if ($totalToPay > 0) {
            $card = $user->creditCards()->find($this->selectedCardId);

            // 1. Cria a despesa consolidada no Caixa Geral (sem credit_card_id)
            $user->transactions()->create([
                'description' => 'Fatura ' . $card->name,
                'amount' => $totalToPay,
                'type' => 'expense',
                'date' => now()->format('Y-m-d'), // Data de hoje (pagamento)
                'is_paid' => true,
                'credit_card_id' => null,
                'category_id' => null,
            ]);

            // 2. Marca todas as comprinhas individuais do cartão como "Pagas"
            foreach ($unpaidTransactions as $tx) {
                $tx->update(['is_paid' => true]);
            }

            session()->flash('invoice_msg', '✅ Fatura de R$ ' . number_format($totalToPay, 2, ',', '.') . ' paga com sucesso! O valor consolidado já está no seu extrato principal.');
        }
    }

    // --- FUNÇÃO PARA REABRIR A FATURA ---
    public function unpayInvoice()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Pega as compras do cartão selecionado, nos meses marcados, que JÁ FORAM PAGAS
        $query = $user->transactions()
            ->where('credit_card_id', $this->selectedCardId)
            ->where('type', 'expense')
            ->where('is_paid', true);

        $this->applyMonthFilter($query);
        $paidTransactions = $query->get();

        if ($paidTransactions->count() > 0) {
            // Reverte todas as compras para "Pendentes"
            foreach ($paidTransactions as $tx) {
                $tx->update(['is_paid' => false]);
            }

            session()->flash('invoice_msg', '⏪ Fatura reaberta com sucesso! Lembre-se de ir na aba de Transações e apagar o pagamento consolidado (se houver).');
        }
    }

    // --- PREENCHE A DATA COM O DIA DE HOJE ---
    public function setToday()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function saveTransaction()
    {
        $this->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
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
                'date' => $this->date,
                'category_id' => $catId,
            ]);
            session()->flash('invoice_msg', 'Compra atualizada!');
        } else {
            if ($this->isInstallment) {
                $baseAmount = $this->installmentType === 'total_value'
                    ? ($this->amount / $this->totalInstallments)
                    : $this->amount;

                for ($i = 1; $i <= $this->totalInstallments; $i++) {
                    $monthsToAdd = $i - 1;
                    $installmentDate = Carbon::parse($this->date)->addMonths($monthsToAdd)->format('Y-m-d');

                    $user->transactions()->create([
                        'description' => $this->description,
                        'amount' => $baseAmount,
                        'type' => 'expense',
                        'date' => $installmentDate,
                        'category_id' => $catId,
                        'credit_card_id' => $this->selectedCardId,
                        'installment_number' => $i,
                        'total_installments' => $this->totalInstallments,
                        'is_paid' => false, // Sempre entra pendente
                    ]);
                }
                session()->flash('invoice_msg', 'Compra parcelada registrada!');
            } else {
                $user->transactions()->create([
                    'description' => $this->description,
                    'amount' => $this->amount,
                    'type' => 'expense',
                    'date' => $this->date,
                    'category_id' => $catId,
                    'credit_card_id' => $this->selectedCardId,
                    'is_paid' => false,
                ]);
                session()->flash('invoice_msg', 'Compra registrada na fatura!');
            }
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function editTransaction($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $transaction = $user->transactions()->findOrFail($id);

        $this->transactionId = $transaction->id;
        $this->description = $transaction->description;
        $this->amount = $transaction->amount;
        $this->date = $transaction->date;
        $this->categoryId = $transaction->category_id;
        $this->isInstallment = false;
        $this->showForm = true;
    }

    public function deleteTransaction($id, $deleteAll = false)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tx = $user->transactions()->findOrFail($id);

        if ($deleteAll && $tx->total_installments > 1) {
            $user->transactions()
                ->where('credit_card_id', $tx->credit_card_id)
                ->where('description', $tx->description)
                ->where('total_installments', $tx->total_installments)
                ->delete();
            session()->flash('invoice_msg', 'Todas as parcelas desta compra foram excluídas!');
        } else {
            $tx->delete();
            session()->flash('invoice_msg', 'Registro excluído!');
        }
    }

    public function resetForm()
    {
        $this->reset(['transactionId', 'description', 'amount', 'date', 'categoryId', 'isInstallment']);
        $this->totalInstallments = 2;
        $this->installmentType = 'total_value';
    }

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

        $cards = $user->creditCards()->orderBy('name')->get();
        $categories = $user->categories()->orderBy('name')->get();

        $selectedCard = null;
        $transactions = [];
        $totalInvoice = 0;
        $unpaidTotal = 0; // Total que ainda falta pagar
        $availableMonths = [];

        $limitPercentage = 0;
        $metaPercentage = 0;
        $dynamicMaxSpend = 0; // A meta que cresce conforme os meses selecionados
        $selectedMonthsCount = count($this->selectedMonths);

        if ($this->selectedCardId) {
            $selectedCard = $user->creditCards()->find($this->selectedCardId);

            if ($selectedCard) {
                $dates = $user->transactions()
                    ->where('credit_card_id', $this->selectedCardId)
                    ->select('date')
                    ->orderBy('date', 'desc')
                    ->get()
                    ->map(fn($t) => Carbon::parse($t->date)->format('Y-m'))
                    ->unique()->values()->toArray();

                $current = now()->format('Y-m');
                if (!in_array($current, $dates)) $dates[] = $current;

                $mesesNomes = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                foreach ($dates as $d) {
                    [$year, $month] = explode('-', $d);
                    $availableMonths[$d] = $mesesNomes[(int)$month] . '/' . substr($year, -2);
                }
                ksort($availableMonths);

                $query = $user->transactions()
                    ->with('category')
                    ->where('credit_card_id', $this->selectedCardId)
                    ->where('type', 'expense');

                $this->applyMonthFilter($query);

                $transactions = $query->orderBy('date', 'asc')->get();
                $totalInvoice = $transactions->sum('amount');

                // Soma apenas o que está "Pendente" para o botão de pagar
                $unpaidTotal = $transactions->where('is_paid', false)->sum('amount');

                // --- MATEMÁTICA ATUALIZADA ---
                if ($selectedCard->limit > 0) {
                    $limitPercentage = ($totalInvoice / $selectedCard->limit) * 100;
                    if ($limitPercentage > 100) $limitPercentage = 100;
                }

                if ($selectedCard->max_spend > 0 && $selectedMonthsCount > 0) {
                    // Multiplica a meta pela quantidade de meses selecionados
                    $dynamicMaxSpend = $selectedCard->max_spend * $selectedMonthsCount;

                    // Calcula a porcentagem SEM TRAVAR em 100 (para poder mostrar 150%, 200%)
                    $metaPercentage = ($totalInvoice / $dynamicMaxSpend) * 100;
                }
            }
        }

        return view('livewire.credit-card-invoice', [
            'cards' => $cards,
            'categories' => $categories,
            'selectedCard' => $selectedCard,
            'transactions' => $transactions,
            'totalInvoice' => $totalInvoice,
            'unpaidTotal' => $unpaidTotal,
            'availableMonths' => $availableMonths,
            'limitPercentage' => $limitPercentage,
            'metaPercentage' => $metaPercentage,
            'dynamicMaxSpend' => $dynamicMaxSpend,
            'selectedMonthsCount' => $selectedMonthsCount,
        ]);
    }
}
