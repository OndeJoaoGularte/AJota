<div class="bg-slate-800 p-6 rounded-lg shadow-md border border-slate-700">
    <h3 class="text-xl font-bold text-slate-100 mb-6 border-b border-slate-700 pb-2">Faturas e Extratos</h3>

    @if($cards->isEmpty())
        <div class="text-center py-8 text-slate-500">
            <div class="text-4xl mb-3">💳</div>
            <p>Você ainda não tem cartões cadastrados.</p>
        </div>
    @else
        {{-- ABAS DOS CARTÕES --}}
        <div class="flex flex-wrap gap-2 mb-6 border-b border-slate-700 pb-4">
            @foreach($cards as $card)
                <button wire:click="selectCard({{ $card->id }})" 
                    @class([
                        'px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 flex items-center gap-2 shadow-sm',
                        'bg-slate-700 text-white border-slate-600 scale-105' => $selectedCardId === $card->id,
                        'bg-slate-900 text-slate-400 border border-slate-700 hover:bg-slate-800 hover:text-slate-200' => $selectedCardId !== $card->id
                    ])>
                    💳 {{ $card->name }}
                </button>
            @endforeach
        </div>

        @if($selectedCard)
            <div class="bg-slate-900/50 rounded-xl border border-slate-700 p-1 md:p-6 mb-6">
                
                {{-- NAVEGAÇÃO DOS MESES EM CHIPS --}}
                <div class="mb-6 bg-slate-900 p-4 rounded-lg shadow-sm border border-slate-700">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Selecione as Faturas:</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($availableMonths as $value => $label)
                            <label wire:key="month-{{ $value }}" class="cursor-pointer select-none">
                                <input type="checkbox" wire:model.live="selectedMonths" value="{{ $value }}" class="hidden">
                                <div @class([
                                    'px-4 py-2 rounded-full border text-sm font-bold transition-all duration-200',
                                    'bg-orange-500 text-white border-orange-600 shadow-md' => in_array($value, $selectedMonths),
                                    'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 hover:text-slate-200' => !in_array($value, $selectedMonths)
                                ])>
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- O GRANDE PAINEL: CARTÃO FÍSICO + LIMITES --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    
                    {{-- LADO ESQUERDO: O Cartão --}}
                    <div class="flex flex-col gap-3">
                        <div @class([
                            'rounded-xl p-6 text-white shadow-lg relative overflow-hidden flex flex-col justify-between h-48 transition-all',
                            'bg-gradient-to-br from-purple-500 to-purple-800' => $selectedCard->color === 'purple',
                            'bg-gradient-to-br from-orange-500 to-orange-800' => $selectedCard->color === 'orange',
                            'bg-gradient-to-br from-blue-500 to-blue-800' => $selectedCard->color === 'blue',
                            'bg-gradient-to-br from-green-500 to-green-800' => $selectedCard->color === 'green',
                            'bg-gradient-to-br from-red-500 to-red-800' => $selectedCard->color === 'red',
                            'bg-gradient-to-br from-gray-700 to-gray-900' => $selectedCard->color === 'gray',
                            'bg-gradient-to-br from-yellow-400 to-yellow-600 text-gray-900' => $selectedCard->color === 'yellow',
                        ])>
                            <div class="absolute top-6 right-6 w-12 h-8 rounded bg-white/20 border border-white/30 backdrop-blur-sm"></div>
                            <div class="font-black text-2xl tracking-widest mt-2">{{ $selectedCard->name }}</div>
                            <div>
                                <div class="text-3xl font-black mb-1">R$ {{ number_format($totalInvoice, 2, ',', '.') }}</div>
                                <div class="text-sm font-medium opacity-90 uppercase tracking-wider">Total Selecionado</div>
                            </div>
                        </div>

                        {{-- BOTÃO PAGAR / DESMARCAR FATURA --}}
                        @if($totalInvoice == 0)
                            <div class="w-full bg-slate-800 text-slate-500 border border-slate-700 py-3 rounded-lg text-sm font-black text-center flex items-center justify-center gap-2 uppercase tracking-wide">
                                📭 Nenhuma compra neste período
                            </div>
                        @elseif($unpaidTotal > 0)
                            <button wire:click="payInvoice" wire:confirm="Isso vai abater R$ {{ number_format($unpaidTotal, 2, ',', '.') }} do seu saldo principal. Confirmar pagamento?" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg shadow-md font-black tracking-wide uppercase transition-all duration-200 flex justify-center items-center gap-2">
                                ✅ Marcar como Paga (R$ {{ number_format($unpaidTotal, 2, ',', '.') }})
                            </button>
                        @else
                            <button wire:click="unpayInvoice" wire:confirm="Deseja reabrir esta fatura? As compras voltarão a ficar pendentes." class="w-full bg-slate-800 hover:bg-red-900/40 text-green-500 hover:text-red-400 border border-slate-700 hover:border-red-800 py-3 rounded-lg text-sm font-black text-center flex items-center justify-center gap-2 uppercase tracking-wide transition-all duration-200 group">
                                <span class="group-hover:hidden">✅ Fatura Marcada como Paga</span>
                                <span class="hidden group-hover:inline">⏪ Desmarcar Pagamento</span>
                            </button>
                        @endif
                    </div>

                    {{-- LADO DIREITO: As Barras de Perigo --}}
                    <div class="bg-slate-900 p-6 rounded-xl border border-slate-700 shadow-sm flex flex-col justify-center space-y-6">
                        {{-- BARRA 1: LIMITE TOTAL --}}
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <div>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Limite do Cartão</span>
                                    <span class="text-sm font-black text-slate-200">R$ {{ number_format($selectedCard->limit, 2, ',', '.') }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Disponível</span>
                                    <span class="text-sm font-bold text-green-500">R$ {{ number_format(max(0, $selectedCard->limit - $totalInvoice), 2, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-3 text-xs flex rounded-full bg-slate-800">
                                <div {!! 'style="width: ' . min(100, $limitPercentage) . '%;"' !!} @class([
                                    'shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center transition-all duration-1000',
                                    'bg-green-500' => $limitPercentage < 50,
                                    'bg-yellow-500' => $limitPercentage >= 50 && $limitPercentage < 85,
                                    'bg-red-500' => $limitPercentage >= 85,
                                ])></div>
                            </div>
                        </div>

                        {{-- BARRA 2: META MÁXIMA --}}
                        @if($selectedCard->max_spend)
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <div>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1 mb-1">
                                        Meta: Gasto Máximo 
                                        @if($selectedMonthsCount > 1)
                                            <span class="bg-orange-500/20 text-orange-400 px-1.5 py-0.5 rounded text-[10px]">Somando {{ $selectedMonthsCount }} meses</span>
                                        @else
                                            <span class="bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded text-[10px]">Mensal</span>
                                        @endif
                                    </span>
                                    <span class="text-sm font-black text-slate-200">R$ {{ number_format($dynamicMaxSpend, 2, ',', '.') }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-bold {{ $metaPercentage > 100 ? 'text-red-400 text-lg' : 'text-slate-400' }}">
                                        {{ number_format($metaPercentage, 1, ',', '.') }}%
                                    </span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-3 text-xs flex rounded-full bg-slate-800 relative">
                                <div {!! 'style="width: ' . min(100, $metaPercentage) . '%;"' !!} @class([
                                    'shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center transition-all duration-1000',
                                    'bg-blue-500' => $metaPercentage < 80,
                                    'bg-orange-500' => $metaPercentage >= 80 && $metaPercentage <= 100,
                                    'bg-red-500 animate-pulse' => $metaPercentage > 100,
                                ])></div>
                                <div class="absolute top-0 bottom-0 left-0 border-r-2 border-red-500 z-10" style="width: 100%;"></div>
                            </div>
                            @if($metaPercentage > 100)
                                <p class="text-xs font-bold text-red-400 mt-2">⚠️ Você estourou a sua meta em {{ number_format($metaPercentage - 100, 1, ',', '.') }}%!</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- BOTÃO PARA ABRIR O FORMULÁRIO --}}
                <div class="flex justify-between items-end mb-4">
                    <h4 class="text-lg font-bold text-slate-200">Compras na Fatura</h4>
                    <button wire:click="$toggle('showForm')" class="bg-slate-700 text-slate-200 px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-slate-600 transition flex items-center gap-2 border border-slate-600">
                        <span>{{ $showForm ? 'Fechar Formulário' : '+ Adicionar Compra' }}</span>
                    </button>
                </div>

                @if (session()->has('invoice_msg'))
                    <div class="p-3 mb-4 text-green-400 bg-green-900/30 rounded text-sm font-bold border border-green-800">
                        {{ session('invoice_msg') }}
                    </div>
                @endif

                {{-- FORMULÁRIO DE COMPRAS --}}
                @if($showForm)
                    <div class="bg-slate-900 p-5 rounded-lg border border-orange-500/50 shadow-inner mb-6">
                        <form wire:submit="saveTransaction" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-5">
                                    <label class="block text-sm font-medium text-slate-300">Descrição</label>
                                    <input type="text" wire:model="description" class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-slate-200 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                                    @error('description') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-slate-300">Valor (R$)</label>
                                    <input type="number" step="0.01" wire:model="amount" class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-slate-200 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                                    @error('amount') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-4">
                                    <div class="flex justify-between items-end mb-1">
                                        <label class="block text-sm font-medium text-slate-300">Data da Compra</label>
                                        <button type="button" wire:click="setToday" class="text-[11px] font-black uppercase tracking-wider text-orange-400 hover:text-orange-300 bg-orange-500/10 hover:bg-orange-500/20 px-2 py-0.5 rounded transition">
                                            Hoje
                                        </button>
                                    </div>
                                    <input type="date" wire:model="date" class="block w-full rounded-md bg-slate-800 border-slate-700 text-slate-200 shadow-sm focus:ring-orange-500 focus:border-orange-500" style="color-scheme: dark;">
                                    @error('date') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-5">
                                    <label class="block text-sm font-medium text-slate-300">Categoria (Opcional)</label>
                                    <select wire:model="categoryId" class="mt-1 block w-full rounded-md bg-slate-800 border-slate-700 text-slate-200 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                                        <option value="">Sem categoria</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if(!$transactionId)
                                <div class="md:col-span-7 flex flex-col justify-end">
                                    <label class="inline-flex items-center mb-2 cursor-pointer">
                                        <input type="checkbox" wire:model.live="isInstallment" class="rounded bg-slate-800 border-slate-600 text-orange-500 shadow-sm focus:ring-orange-500 focus:ring-offset-slate-900">
                                        <span class="ml-2 text-sm font-bold text-slate-300">É uma compra parcelada?</span>
                                    </label>
                                    
                                    @if($isInstallment)
                                        <div class="flex gap-4 p-3 bg-slate-800/50 rounded border border-slate-700 items-end mt-2">
                                            <div class="w-1/2">
                                                <label class="block text-xs text-slate-400 mb-1">O Valor acima é:</label>
                                                <select wire:model="installmentType" class="block w-full text-sm rounded-md bg-slate-900 border-slate-700 text-slate-200 shadow-sm">
                                                    <option value="total_value">Valor Total (Ex: 1000)</option>
                                                    <option value="installment_value">Valor da Parcela (Ex: 100)</option>
                                                </select>
                                            </div>
                                            <div class="w-1/2">
                                                <label class="block text-xs text-slate-400 mb-1">Em quantas vezes?</label>
                                                <input type="number" wire:model="totalInstallments" min="2" class="block w-full text-sm rounded-md bg-slate-900 border-slate-700 text-slate-200 shadow-sm">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @endif
                            </div>

                            <div class="flex justify-end gap-2 mt-2 pt-4 border-t border-slate-700">
                                <button type="button" wire:click="$toggle('showForm')" class="bg-slate-700 text-slate-300 px-4 py-2 rounded shadow hover:bg-slate-600 font-bold transition">Cancelar</button>
                                <button type="submit" class="bg-orange-600 text-white px-6 py-2 rounded shadow hover:bg-orange-500 font-bold transition">
                                    {{ $transactionId ? 'Salvar Alterações' : 'Registrar Compra' }}
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- LISTA DE COMPRAS --}}
                <div class="bg-slate-900 rounded-lg border border-slate-700 overflow-hidden shadow-sm">
                    <table class="min-w-full">
                        <thead class="bg-slate-800/50 text-slate-400 border-b border-slate-700">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-bold uppercase tracking-wider">Data</th>
                                <th class="py-3 px-4 text-left text-xs font-bold uppercase tracking-wider">Descrição</th>
                                <th class="py-3 px-4 text-right text-xs font-bold uppercase tracking-wider">Valor</th>
                                <th class="py-3 px-4 text-center text-xs font-bold uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-slate-800/50 transition">
                                    <td class="py-3 px-4 text-sm text-slate-400 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-sm font-medium text-slate-200">
                                        {{ $transaction->description }}
                                        @if($transaction->is_paid) <span class="text-[10px] ml-1 opacity-70">✅</span> @endif
                                        @if($transaction->total_installments > 1)
                                            <span class="text-orange-400 text-[10px] ml-1 px-1.5 py-0.5 bg-orange-500/10 border border-orange-500/20 rounded-full font-bold">
                                                {{ $transaction->installment_number }}/{{ $transaction->total_installments }}
                                            </span>
                                        @endif
                                        @if($transaction->category)
                                            <span class="block text-xs text-slate-500 mt-0.5">{{ $transaction->category->name }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-right font-bold text-slate-200 whitespace-nowrap">
                                        R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-center whitespace-nowrap">
                                        <button wire:click="editTransaction({{ $transaction->id }})" class="text-blue-400 hover:text-blue-300 mr-2 text-xs font-bold uppercase tracking-wider">Editar</button>
                                        
                                        @if($transaction->total_installments > 1)
                                            <div class="inline-block relative" x-data="{ open: false }">
                                                <button @click="open = !open" @click.away="open = false" class="text-red-400 hover:text-red-300 text-xs font-bold uppercase tracking-wider">Apagar ▾</button>
                                                <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-48 bg-slate-800 rounded-md shadow-xl z-50 border border-slate-700">
                                                    <button wire:click="deleteTransaction({{ $transaction->id }}, false)" class="block w-full text-left px-4 py-2 text-xs text-slate-300 hover:bg-slate-700">Só esta parcela</button>
                                                    <button wire:click="deleteTransaction({{ $transaction->id }}, true)" wire:confirm="Apagar TODAS as parcelas futuras desta compra?" class="block w-full text-left px-4 py-2 text-xs text-red-400 font-bold hover:bg-slate-700 border-t border-slate-700">Apagar todas (1 ao {{ $transaction->total_installments }})</button>
                                                </div>
                                            </div>
                                        @else
                                            <button wire:click="deleteTransaction({{ $transaction->id }})" wire:confirm="Apagar esta compra?" class="text-red-400 hover:text-red-300 text-xs font-bold uppercase tracking-wider">Apagar</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-slate-500">
                                        <p class="mb-1 text-2xl opacity-50">🛍️</p>
                                        Nenhuma compra registrada nos meses selecionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>