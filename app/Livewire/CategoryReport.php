<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CategoryReport extends Component
{
    // O array que vai guardar os meses marcados (Ex: ['2026-03', '2026-04'])
    public $selectedMonths = []; 

    // Variáveis do Raio-X
    public $selectedCategoryId = null;

    // Variáveis do Gerenciador de Categorias (CRUD)
    public $manageCategoryId = null;
    public $manageCategoryName = '';
    
    // Variáveis para a lógica de Transferência
    public $categoryToDelete = null;
    public $replacementCategoryId = '';
    public $transactionsCountToTransfer = 0;

    public function mount()
    {
        // Ao abrir a tela, marcamos o mês atual por padrão
        $this->selectedMonths = [now()->format('Y-m')];
    }

    public function selectCategory($id)
    {
        $this->selectedCategoryId = ($this->selectedCategoryId === $id) ? null : $id;
    }

    // --- LÓGICA DO GERENCIADOR DE CATEGORIAS ---
    public function saveCategory()
    {
        $this->validate(['manageCategoryName' => 'required|string|max:255']);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($this->manageCategoryId) {
            $user->categories()->findOrFail($this->manageCategoryId)->update(['name' => $this->manageCategoryName]);
            session()->flash('cat_msg', 'Categoria atualizada!');
        } else {
            $user->categories()->create(['name' => $this->manageCategoryName]);
            session()->flash('cat_msg', 'Nova categoria criada!');
        }
        $this->reset(['manageCategoryId', 'manageCategoryName']);
    }

    public function editCategory($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $category = $user->categories()->findOrFail($id);
        $this->manageCategoryId = $category->id;
        $this->manageCategoryName = $category->name;
    }

    public function confirmDelete($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $count = $user->transactions()->where('category_id', $id)->count();

        if ($count > 0) {
            $this->categoryToDelete = $id;
            $this->transactionsCountToTransfer = $count;
            $this->replacementCategoryId = ''; 
        } else {
            $user->categories()->findOrFail($id)->delete();
            session()->flash('cat_msg', 'Categoria excluída!');
        }
    }

    public function deleteAndTransfer()
    {
        $this->validate(['replacementCategoryId' => 'required|exists:categories,id']);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->transactions()
             ->where('category_id', $this->categoryToDelete)
             ->update(['category_id' => $this->replacementCategoryId]);

        $user->categories()->findOrFail($this->categoryToDelete)->delete();

        if ($this->selectedCategoryId == $this->categoryToDelete) {
            $this->selectedCategoryId = null;
        }

        session()->flash('cat_msg', 'Gotas movidas e categoria antiga apagada!');
        $this->reset(['categoryToDelete', 'replacementCategoryId', 'transactionsCountToTransfer']);
    }

    public function cancelDelete()
    {
        $this->reset(['categoryToDelete', 'replacementCategoryId', 'transactionsCountToTransfer']);
    }

    // --- FUNÇÃO AUXILIAR PARA MONTAR A QUERY DE DATAS ---
    private function applyMonthFilter($query)
    {
        if (empty($this->selectedMonths)) {
            // Se o usuário desmarcar tudo, não trazemos nada
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

        // --- GERA A LISTA DE MESES DISPONÍVEIS COM BASE NO QUE ESTÁ NO BANCO ---
        $availableMonths = [];
        $dates = $user->transactions()->select('date')->orderBy('date', 'desc')->get()
            ->map(fn($t) => Carbon::parse($t->date)->format('Y-m'))
            ->unique()->values()->toArray();

        // Garante que o mês atual apareça na lista
        $current = now()->format('Y-m');
        if (!in_array($current, $dates)) $dates[] = $current;
        
        $mesesNomes = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        foreach ($dates as $d) {
            [$year, $month] = explode('-', $d);
            $availableMonths[$d] = $mesesNomes[(int)$month] . '/' . substr($year, -2);
        }

        // FORÇA A ORDEM DECRESCENTE (Mais recente na esquerda, mais antigo na direita)
        ksort($availableMonths);

        // --- RAIO-X: Traz as categorias somadas baseadas nos meses selecionados ---
        $categoriesWithTotals = $user->categories()->withSum([
            'transactions as total_spent' => function ($query) {
                $query->where('type', 'expense');
                $this->applyMonthFilter($query);
            }
        ], 'amount')->orderByDesc('total_spent')->get();

        // --- DETALHES: Traz os gastos da categoria clicada baseados nos meses selecionados ---
        $selectedTransactions = [];
        $selectedCategoryName = '';
        
        if ($this->selectedCategoryId) {
            $selectedCategory = $user->categories()->find($this->selectedCategoryId);
            $selectedCategoryName = $selectedCategory ? $selectedCategory->name : '';
            
            $query = $user->transactions()
                ->where('category_id', $this->selectedCategoryId)
                ->where('type', 'expense');

            $this->applyMonthFilter($query);
            $selectedTransactions = $query->orderBy('date', 'desc')->get();
        }

        // Para a Oficina
        $allCategories = $user->categories()->orderBy('name')->get();
        
        return view('livewire.category-report', [
            'categoriesWithTotals' => $categoriesWithTotals,
            'selectedTransactions' => $selectedTransactions,
            'selectedCategoryName' => $selectedCategoryName,
            'allCategories' => $allCategories,
            'availableMonths' => $availableMonths,
        ]);
    }
}