<div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
    <h3 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Faturas e Extratos</h3>

    @if($cards->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <div class="text-4xl mb-3">💳</div>
            <p>Você ainda não tem cartões cadastrados.</p>
            <p class="text-sm">Adicione um cartão logo acima para ver as suas faturas aqui.</p>
        </div>
    @else
        {{-- ABAS DOS CARTÕES (Tabs) --}}
        <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-100 pb-4">
            @foreach($cards as $card)
                <button wire:click="selectCard({{ $card->id }})" 
                    @class([
                        'px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 flex items-center gap-2 shadow-sm',
                        'bg-gray-800 text-white border-gray-900 scale-105' => $selectedCardId === $card->id,
                        'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:text-gray-900' => $selectedCardId !== $card->id
                    ])>
                    💳 {{ $card->name }}
                </button>
            @endforeach
        </div>

        {{-- SE TEM UM CARTÃO SELECIONADO, MOSTRA A FATURA --}}
        @if($selectedCard)
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-1 md:p-6">
                
                {{-- NAVEGAÇÃO DOS MESES EM CHIPS --}}
                <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Selecione as Faturas:</h3>
                    
                    <div class="flex flex-wrap gap-2">
                        @foreach($availableMonths as $value => $label)
                            <label wire:key="month-{{ $value }}" class="cursor-pointer select-none">
                                <input type="checkbox" wire:model.live="selectedMonths" value="{{ $value }}" class="hidden">
                                
                                <div @class([
                                    'px-4 py-2 rounded-full border text-sm font-bold transition-all duration-200',
                                    'bg-orange-500 text-white border-orange-600 shadow-md' => in_array($value, $selectedMonths),
                                    'bg-white border-gray-200 text-gray-500 hover:bg-gray-50' => !in_array($value, $selectedMonths)
                                ])>
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    
                    @if(empty($selectedMonths))
                        <div class="mt-3 text-xs text-red-500 font-bold">
                            ⚠️ Selecione pelo menos um mês para visualizar a fatura.
                        </div>
                    @endif
                </div>

                {{-- CABEÇALHO DA FATURA --}}
                <div class="flex flex-col md:flex-row justify-between items-center bg-white p-5 rounded-lg border-l-4 border-orange-500 shadow-sm mb-6">
                    <div>
                        <h4 class="text-2xl font-black text-gray-800">Total Selecionado</h4>
                        <p class="text-sm text-gray-500 font-medium">
                            Vencimento dia {{ $selectedCard->due_day }} • Fechamento dia {{ $selectedCard->closing_day }}
                        </p>
                    </div>
                    <div class="text-3xl font-black text-orange-600 mt-2 md:mt-0">
                        R$ {{ number_format($totalInvoice, 2, ',', '.') }}
                    </div>
                </div>

                {{-- LISTA DE COMPRAS DA FATURA --}}
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 text-gray-700">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-bold uppercase tracking-wider">Data</th>
                                <th class="py-3 px-4 text-left text-xs font-bold uppercase tracking-wider">Descrição</th>
                                <th class="py-3 px-4 text-right text-xs font-bold uppercase tracking-wider">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-orange-50/30 transition">
                                    <td class="py-3 px-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-sm font-medium text-gray-800">
                                        {{ $transaction->description }}
                                        @if($transaction->total_installments > 1)
                                            <span class="text-orange-600 text-[10px] ml-1 px-1.5 py-0.5 bg-orange-100 rounded-full">
                                                {{ $transaction->installment_number }}/{{ $transaction->total_installments }}
                                            </span>
                                        @endif
                                        @if($transaction->category)
                                            <span class="block text-xs text-gray-400 mt-0.5">{{ $transaction->category->name }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-right font-bold text-gray-800 whitespace-nowrap">
                                        R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-8 text-gray-500">
                                        <p class="mb-1 text-2xl">😎</p>
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