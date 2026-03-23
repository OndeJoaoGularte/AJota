<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InvestmentManager extends Component
{
    public $currentMonth;
    public $currentYear;

    // Variáveis do Formulário de Investimento
    public $investmentId = null;
    public $description;
    public $amount;
    public $date;

    // A meta do usuário (ex: 10%)
    public $goalPercentage;

    public function mount()
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->goalPercentage = $user->investment_goal_percentage;
    }

    // --- NAVEGAÇÃO DOS MESES ---
    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    // Atualiza a porcentagem da meta direto no perfil do usuário
    public function updateGoal()
    {
        $this->validate(['goalPercentage' => 'required|integer|min:1|max:100']);
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update(['investment_goal_percentage' => $this->goalPercentage]);
        session()->flash('goal_msg', 'Meta atualizada!');
    }

    // --- CRUD DOS INVESTIMENTOS ---
    public function save()
    {
        $this->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($this->investmentId) {
            $user->investments()->findOrFail($this->investmentId)->update([
                'description' => $this->description,
                'amount' => $this->amount,
                'date' => $this->date,
            ]);
            session()->flash('inv_msg', 'Aporte atualizado!');
        } else {
            $user->investments()->create([
                'description' => $this->description,
                'amount' => $this->amount,
                'date' => $this->date,
            ]);
            session()->flash('inv_msg', 'Aporte registrado com sucesso!');
        }

        $this->reset(['investmentId', 'description', 'amount', 'date']);
    }

    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $inv = $user->investments()->findOrFail($id);
        
        $this->investmentId = $inv->id;
        $this->description = $inv->description;
        $this->amount = $inv->amount;
        $this->date = $inv->date;
    }

    public function delete($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->investments()->findOrFail($id)->delete();
        session()->flash('inv_msg', 'Aporte excluído!');
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Calcula quanto dinheiro ENTROU neste mês
        $totalIncome = $user->transactions()
            ->where('type', 'income')
            ->whereMonth('date', $this->currentMonth)
            ->whereYear('date', $this->currentYear)
            ->sum('amount');

        // 2. Calcula a Meta em Dinheiro (Ex: 10% de 2000 = 200)
        $targetAmount = $totalIncome * ($this->goalPercentage / 100);

        // 3. Calcula quanto você JÁ INVESTIU neste mês
        $totalInvested = $user->investments()
            ->whereMonth('date', $this->currentMonth)
            ->whereYear('date', $this->currentYear)
            ->sum('amount');

        // 4. Calcula a porcentagem da barra de progresso (evita divisão por zero)
        $progress = $targetAmount > 0 ? ($totalInvested / $targetAmount) * 100 : 0;
        if ($progress > 100) $progress = 100; // Trava a barra visualmente em 100%

        // Busca a lista de investimentos do mês
        $investments = $user->investments()
            ->whereMonth('date', $this->currentMonth)
            ->whereYear('date', $this->currentYear)
            ->orderBy('date', 'desc')
            ->get();

        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

        return view('livewire.investment-manager', [
            'totalIncome' => $totalIncome,
            'targetAmount' => $targetAmount,
            'totalInvested' => $totalInvested,
            'progress' => $progress,
            'investments' => $investments,
            'nomeMes' => $meses[$this->currentMonth],
        ]);
    }
}