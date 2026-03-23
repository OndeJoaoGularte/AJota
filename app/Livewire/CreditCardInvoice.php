<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreditCardInvoice extends Component
{
    public $selectedCardId = null;
    public $selectedMonths = []; 

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
        
        $selectedCard = null;
        $transactions = [];
        $totalInvoice = 0;
        $availableMonths = [];

        // Novas variáveis para a barra de progresso
        $limitPercentage = 0;
        $metaPercentage = 0;

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
                    ->where('credit_card_id', $this->selectedCardId)
                    ->where('type', 'expense');

                $this->applyMonthFilter($query);
                
                $transactions = $query->orderBy('date', 'desc')->get();
                $totalInvoice = $transactions->sum('amount');

                // --- MATEMÁTICA DAS BARRAS DE PERIGO ---
                if ($selectedCard->limit > 0) {
                    $limitPercentage = ($totalInvoice / $selectedCard->limit) * 100;
                    if ($limitPercentage > 100) $limitPercentage = 100;
                }

                if ($selectedCard->max_spend > 0) {
                    $metaPercentage = ($totalInvoice / $selectedCard->max_spend) * 100;
                    if ($metaPercentage > 100) $metaPercentage = 100;
                }
            }
        }

        return view('livewire.credit-card-invoice', [
            'cards' => $cards,
            'selectedCard' => $selectedCard,
            'transactions' => $transactions,
            'totalInvoice' => $totalInvoice,
            'availableMonths' => $availableMonths,
            'limitPercentage' => $limitPercentage,
            'metaPercentage' => $metaPercentage,
        ]);
    }
}