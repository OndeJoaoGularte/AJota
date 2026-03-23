<div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
    <h3 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Faturas e Extratos</h3>

    @if($cards->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <div class="text-4xl mb-3">💳</div>
            <p>Você ainda não tem cartões cadastrados.</p>
        </div>
    @else
        {{-- ABAS DOS CARTÕES --}}
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

        @if($selectedCard)
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-1 md:p-6 mb-6">
                
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
                </div>

                {{-- O GRANDE PAINEL: CARTÃO FÍSICO + LIMITES --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    
                    {{-- LADO ESQUERDO: O Cartão --}}
                    <div @class([
                        'rounded-xl p-6 text-white shadow-lg relative overflow-hidden flex flex-col justify-between h-48',
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
                            <div class="text-3xl font-black mb-1">
                                R$ {{ number_format($totalInvoice, 2, ',', '.') }}
                            </div>
                            <div class="text-sm font-medium opacity-90 uppercase tracking-wider">
                                Total Selecionado
                            </div>
                        </div>
                    </div>

                    {{-- LADO DIREITO: As Barras de Perigo --}}
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center space-y-6">
                        
                        {{-- BARRA 1: LIMITE TOTAL --}}
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <div>
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Limite do Cartão</span>
                                    <span class="text-sm font-black text-gray-800">R$ {{ number_format($selectedCard->limit, 2, ',', '.') }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Disponível</span>
                                    <span class="text-sm font-bold text-green-600">R$ {{ number_format(max(0, $selectedCard->limit - $totalInvoice), 2, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-3 text-xs flex rounded-full bg-gray-100">
                                <div {!! 'style="width: ' . $limitPercentage . '%;"' !!} @class([
                                    'shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center transition-all duration-1000',
                                    'bg-green-500' => $limitPercentage < 50,
                                    'bg-yellow-500' => $limitPercentage >= 50 && $limitPercentage < 85,
                                    'bg-red-600' => $limitPercentage >= 85,
                                ])></div>
                            </div>
                        </div>

                        {{-- BARRA 2: META MÁXIMA (Se existir) --}}
                        @if($selectedCard->max_spend)
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <div>
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Meta: Gasto Máximo</span>
                                    <span class="text-sm font-black text-gray-800">R$ {{ number_format($selectedCard->max_spend, 2, ',', '.') }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-bold {{ $metaPercentage >= 100 ? 'text-red-600' : 'text-gray-400' }}">
                                        {{ number_format($metaPercentage, 1, ',', '.') }}%
                                    </span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-3 text-xs flex rounded-full bg-gray-100 relative">
                                <div {!! 'style="width: ' . $metaPercentage . '%;"' !!} @class([
                                    'shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center transition-all duration-1000',
                                    'bg-blue-400' => $metaPercentage < 80,
                                    'bg-orange-500' => $metaPercentage >= 80 && $metaPercentage < 100,
                                    'bg-red-600 animate-pulse' => $metaPercentage >= 100,
                                ])></div>
                                
                                {{-- A linha que marca os 100% da meta --}}
                                <div class="absolute top-0 bottom-0 left-0 border-r-2 border-red-500 z-10" style="width: 100%;"></div>
                            </div>
                            @if($metaPercentage >= 100)
                                <p class="text-xs font-bold text-red-600 mt-2">⚠️ Você estourou a sua meta de gastos neste cartão!</p>
                            @endif
                        </div>
                        @endif

                    </div>
                </div>

                {{-- LISTA DE COMPRAS --}}
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 text-gray-700 border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-bold uppercase tracking-wider">Data</th>
                                <th class="py-3 px-4 text-left text-xs font-bold uppercase tracking-wider">Descrição</th>
                                <th class="py-3 px-4 text-right text-xs font-bold uppercase tracking-wider">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-gray-50 transition">
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