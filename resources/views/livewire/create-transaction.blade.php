<div class="space-y-6">
    
    {{-- BARRA DE SELEÇÃO DE MESES (CHIPS) --}}
    <div class="bg-slate-800 p-4 rounded-xl shadow-md border border-slate-700">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Filtrar Período (Visão Geral):</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($availableMonths as $value => $label)
                <label wire:key="month-{{ $value }}" class="cursor-pointer select-none">
                    <input type="checkbox" wire:model.live="selectedMonths" value="{{ $value }}" class="hidden">
                    <div @class([
                        'px-4 py-2 rounded-full border text-sm font-bold transition-all duration-200 shadow-sm',
                        'bg-orange-500 text-white border-orange-600' => in_array($value, $selectedMonths),
                        'bg-slate-900 border-slate-700 text-slate-400 hover:bg-slate-700 hover:text-slate-200' => !in_array($value, $selectedMonths)
                    ])>
                        {{ $label }}
                    </div>
                </label>
            @endforeach
        </div>
        @if(empty($selectedMonths))
            <div class="mt-3 text-xs text-red-400 font-bold">
                ⚠️ Selecione pelo menos um mês para visualizar o balanço.
            </div>
        @endif
    </div>

    {{-- PAINEL DE RESUMO FINANCEIRO (CARDS) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Card de Saldo --}}
        <div class="bg-slate-800 p-6 rounded-xl shadow-md border border-slate-700 flex flex-col justify-center">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Saldo do Período</h3>
            <div class="text-3xl font-black {{ $balance >= 0 ? 'text-green-500' : 'text-red-500' }}">
                R$ {{ number_format($balance, 2, ',', '.') }}
            </div>
        </div>

        {{-- Card de Receitas --}}
        <div class="bg-slate-800 p-6 rounded-xl shadow-md border border-slate-700 border-b-4 border-b-green-500 flex flex-col justify-center">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Entradas</h3>
            <div class="text-2xl font-black text-green-500">
                + R$ {{ number_format($totalIncome, 2, ',', '.') }}
            </div>
        </div>

        {{-- Card de Despesas --}}
        <div class="bg-slate-800 p-6 rounded-xl shadow-md border border-slate-700 border-b-4 border-b-red-500 flex flex-col justify-center">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Saídas</h3>
            <div class="text-2xl font-black text-red-500">
                - R$ {{ number_format($totalExpense, 2, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- COLUNA ESQUERDA: FORMULÁRIO DE CADASTRO --}}
        <div class="lg:col-span-1">
            <div class="bg-slate-800 p-6 rounded-xl shadow-md border border-slate-700 sticky top-6">
                <h3 class="text-lg font-bold text-slate-100 mb-6 border-b border-slate-700 pb-2">
                    {{ $transactionId ? '✏️ Editar Lançamento' : '✨ Novo Lançamento' }}
                </h3>

                @if (session()->has('message'))
                    <div class="p-3 mb-4 text-sm font-bold text-green-400 bg-green-900/30 rounded-lg border border-green-800">
                        {{ session('message') }}
                    </div>
                @endif

                <form wire:submit="save" class="space-y-4">
                    {{-- Tipo (Receita/Despesa) --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Tipo de Movimentação</label>
                        <div class="flex gap-4">
                            <label class="flex items-center cursor-pointer group">
                                {{-- Adicionamos o .live aqui! --}}
                                <input type="radio" wire:model.live="type" value="expense" class="hidden">
                                <span class="px-4 py-2 rounded-lg border text-sm font-bold transition-all duration-200 {{ $type === 'expense' ? 'bg-red-500/20 border-red-500 text-red-400 shadow-sm' : 'bg-slate-900 border-slate-700 text-slate-500 hover:bg-slate-700' }}">Saída</span>
                            </label>
                            <label class="flex items-center cursor-pointer group">
                                {{-- E adicionamos o .live aqui! --}}
                                <input type="radio" wire:model.live="type" value="income" class="hidden">
                                <span class="px-4 py-2 rounded-lg border text-sm font-bold transition-all duration-200 {{ $type === 'income' ? 'bg-green-500/20 border-green-500 text-green-400 shadow-sm' : 'bg-slate-900 border-slate-700 text-slate-500 hover:bg-slate-700' }}">Entrada</span>
                            </label>
                        </div>
                        @error('type') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Descrição --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Descrição</label>
                        <input type="text" wire:model="description" placeholder="Ex: Conta de Luz" class="mt-1 block w-full rounded-md bg-slate-900 border-slate-700 text-slate-200 placeholder-slate-600 shadow-sm focus:ring-orange-500">
                        @error('description') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Valor --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Valor (R$)</label>
                        <input type="number" step="0.01" wire:model="amount" placeholder="0.00" class="mt-1 block w-full rounded-md bg-slate-900 border-slate-700 text-slate-200 placeholder-slate-600 shadow-sm focus:ring-orange-500">
                        @error('amount') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Data com botão Hoje --}}
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <label class="block text-sm font-medium text-slate-300">Data</label>
                            <button type="button" wire:click="setToday" class="text-[11px] font-black uppercase tracking-wider text-orange-400 hover:text-orange-300 bg-orange-500/10 hover:bg-orange-500/20 px-2 py-0.5 rounded transition">
                                Hoje
                            </button>
                        </div>
                        <input type="date" wire:model="date" class="block w-full rounded-md bg-slate-900 border-slate-700 text-slate-200 shadow-sm focus:ring-orange-500" style="color-scheme: dark;">
                        @error('date') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Categoria --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Categoria</label>
                        <div class="flex gap-2 mt-1">
                            <select wire:model="categoryId" class="block w-full rounded-md bg-slate-900 border-slate-700 text-slate-200 shadow-sm focus:ring-orange-500">
                                <option value="">Sem categoria</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Nova Categoria Rápida --}}
                    <div class="bg-slate-900 p-3 rounded-lg border border-slate-700">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Criar Nova Categoria Rápida</label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="newCategoryName" placeholder="Ex: Supermercado" class="block w-full text-sm rounded-md bg-slate-800 border-slate-600 text-slate-200 placeholder-slate-500 focus:ring-orange-500">
                            <button type="button" wire:click="addCategory" class="bg-slate-700 text-slate-200 px-3 py-2 rounded-md hover:bg-slate-600 transition font-bold text-sm border border-slate-600">Criar</button>
                        </div>
                        @error('newCategoryName') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Parcelamento (Só p/ despesas e criação) --}}
                    @if(!$transactionId && $type === 'expense')
                    <div class="border-t border-slate-700 pt-4 mt-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="isInstallment" class="rounded bg-slate-900 border-slate-600 text-orange-500 shadow-sm focus:ring-orange-500 focus:ring-offset-slate-800">
                            <span class="ml-2 text-sm font-bold text-slate-300">É um pagamento parcelado? (Ex: Carnê)</span>
                        </label>
                        
                        @if($isInstallment)
                            <div class="mt-3 p-3 bg-slate-900 rounded-lg border border-slate-700 space-y-3">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">O Valor informado acima é:</label>
                                    <select wire:model="installmentType" class="block w-full text-sm rounded-md bg-slate-800 border-slate-600 text-slate-200 focus:ring-orange-500">
                                        <option value="total_value">O Valor Total (O sistema divide)</option>
                                        <option value="installment_value">O Valor da Parcela</option>
                                    </select>
                                </div>
                                <div class="flex gap-2">
                                    <div class="w-1/2">
                                        <label class="block text-xs text-slate-400 mb-1">Total de Parcelas</label>
                                        <input type="number" wire:model="totalInstallments" min="2" class="block w-full text-sm rounded-md bg-slate-800 border-slate-600 text-slate-200 focus:ring-orange-500">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif

                    {{-- Botões de Ação --}}
                    <div class="pt-4 flex gap-2">
                        <button type="submit" class="w-full bg-orange-600 text-white font-bold py-3 rounded-lg shadow-md hover:bg-orange-500 transition tracking-wide">
                            {{ $transactionId ? 'Salvar Edição' : 'Registrar' }}
                        </button>
                        @if($transactionId)
                            <button type="button" wire:click="resetForm" class="bg-slate-700 text-slate-300 font-bold px-4 py-3 rounded-lg shadow-md hover:bg-slate-600 transition">
                                Cancelar
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- COLUNA DIREITA: LISTA DE TRANSAÇÕES --}}
        <div class="lg:col-span-2">
            <div class="bg-slate-800 rounded-xl shadow-md border border-slate-700 overflow-hidden">
                <div class="p-4 bg-slate-800 border-b border-slate-700">
                    <h3 class="font-bold text-slate-100 text-lg">Extrato Geral</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-900/50 text-slate-400">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-bold uppercase tracking-wider">Data</th>
                                <th class="py-3 px-4 text-left text-xs font-bold uppercase tracking-wider">Descrição</th>
                                <th class="py-3 px-4 text-right text-xs font-bold uppercase tracking-wider">Valor</th>
                                <th class="py-3 px-4 text-center text-xs font-bold uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse ($transactions as $transaction)
                                <tr class="hover:bg-slate-800/80 transition group">
                                    <td class="py-3 px-4 text-sm text-slate-400 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-sm">
                                        <div class="font-medium text-slate-200 flex items-center gap-2">
                                            {{ $transaction->description }}
                                            @if($transaction->total_installments > 1)
                                                <span class="text-orange-400 text-[10px] px-1.5 py-0.5 bg-orange-500/10 border border-orange-500/20 rounded-full font-bold">
                                                    {{ $transaction->installment_number }}/{{ $transaction->total_installments }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($transaction->category)
                                            <div class="text-xs text-slate-500 mt-0.5">{{ $transaction->category->name }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-right font-black whitespace-nowrap {{ $transaction->type === 'income' ? 'text-green-500' : 'text-slate-200' }}">
                                        {{ $transaction->type === 'income' ? '+' : '-' }} R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-center whitespace-nowrap">
                                        <div class="flex justify-center items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button wire:click="edit({{ $transaction->id }})" title="Editar" class="text-slate-400 hover:text-blue-400 transition text-lg">✏️</button>
                                            <button wire:click="duplicateToNextMonth({{ $transaction->id }})" title="Duplicar para o mês seguinte" class="text-slate-400 hover:text-green-400 transition text-lg">🔁</button>
                                            <button wire:click="delete({{ $transaction->id }})" wire:confirm="Tem certeza que deseja apagar?" title="Excluir" class="text-slate-400 hover:text-red-400 transition text-lg">🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-500">
                                        <div class="text-4xl mb-2 opacity-50">🍃</div>
                                        Nenhum registro encontrado nos meses selecionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>