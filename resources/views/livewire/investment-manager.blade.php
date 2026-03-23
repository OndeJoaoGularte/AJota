<div>
    {{-- BARRA DE NAVEGAÇÃO DOS MESES --}}
    <div class="flex items-center justify-between mb-8 bg-white p-4 rounded-lg shadow-sm border border-orange-100">
        <button wire:click="previousMonth" class="px-4 py-2 bg-orange-50 border border-orange-200 rounded-md shadow-sm text-sm font-medium text-orange-700 hover:bg-orange-100 transition">
            &laquo; Mês Anterior
        </button>
        <h2 class="text-xl font-bold text-gray-800 uppercase tracking-wide">
            {{ $nomeMes }} {{ $currentYear }}
        </h2>
        <button wire:click="nextMonth" class="px-4 py-2 bg-orange-50 border border-orange-200 rounded-md shadow-sm text-sm font-medium text-orange-700 hover:bg-orange-100 transition">
            Próximo Mês &raquo;
        </button>
    </div>

    {{-- PAINEL PRINCIPAL: A META E O PROGRESSO --}}
    <div class="bg-white rounded-lg shadow-md border-t-4 border-orange-500 p-6 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h3 class="text-2xl font-black text-gray-800">Sua Meta de Investimento</h3>
                <p class="text-gray-500 text-sm">Baseado em todas as receitas (entradas) deste mês.</p>
            </div>
            
            {{-- Formulário pequeno para ajustar a % da meta --}}
            <form wire:submit="updateGoal" class="flex items-center gap-2 bg-gray-50 p-2 rounded-lg border border-gray-200">
                <label class="text-sm font-bold text-gray-700">Meta:</label>
                <div class="relative w-20">
                    <input type="number" wire:model="goalPercentage" min="1" max="100" class="block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 pr-6">
                </div>
                <button type="submit" class="bg-gray-800 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-900 transition">Salvar</button>
                @if (session()->has('goal_msg'))
                    <span class="text-green-600 text-xs font-bold">{{ session('goal_msg') }}</span>
                @endif
            </form>
        </div>

        {{-- RESUMO FINANCEIRO --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-center">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Receita do Mês</div>
                <div class="text-xl font-bold text-gray-800">R$ {{ number_format($totalIncome, 2, ',', '.') }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <div class="text-xs font-bold text-orange-500 uppercase tracking-wider mb-1">Alvo para Investir ({{ $goalPercentage }}%)</div>
                <div class="text-xl font-black text-orange-600">R$ {{ number_format($targetAmount, 2, ',', '.') }}</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                <div class="text-xs font-bold text-green-600 uppercase tracking-wider mb-1">Total Já Investido</div>
                <div class="text-xl font-black text-green-700">R$ {{ number_format($totalInvested, 2, ',', '.') }}</div>
            </div>
        </div>

        {{-- A BARRA DE PROGRESSO ANIMADA --}}
        <div class="relative pt-1">
            <div class="flex mb-2 items-center justify-between">
                <div>
                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-orange-600 bg-orange-200">
                        Progresso da Meta
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-semibold inline-block text-orange-600">
                        {{ number_format($progress, 1, ',', '.') }}%
                    </span>
                </div>
            </div>
            <div class="overflow-hidden h-4 mb-4 text-xs flex rounded-full bg-orange-100">
                {{-- Colocamos o ponto e vírgula (;) no final para o validador ficar feliz --}}
                <div {!! 'style="width: ' . $progress . ';"' !!} class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-orange-500 transition-all duration-1000 ease-out"></div>
            </div>
            @if($progress >= 100)
                <p class="text-center text-sm font-bold text-green-600 mt-2">🎉 Parabéns! Você bateu a sua meta de investimentos neste mês!</p>
            @elseif($targetAmount > 0)
                <p class="text-center text-sm text-gray-500 mt-2">Faltam <span class="font-bold text-gray-700">R$ {{ number_format($targetAmount - $totalInvested, 2, ',', '.') }}</span> para você atingir o seu objetivo.</p>
            @endif
        </div>
    </div>

    {{-- O GRID: FORMULÁRIO E LISTA --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- FORMULÁRIO DE APORTE --}}
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                    {{ $investmentId ? 'Editar Aporte' : 'Registrar Novo Aporte' }}
                </h3>

                @if (session()->has('inv_msg'))
                    <div class="p-3 mb-4 text-green-700 bg-green-100 rounded text-sm font-medium">
                        {{ session('inv_msg') }}
                    </div>
                @endif

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Onde você investiu?</label>
                        <input type="text" wire:model="description" placeholder="Ex: CDB, Tesouro Direto..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Valor (R$)</label>
                            <input type="number" step="0.01" wire:model="amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data</label>
                            <input type="date" wire:model="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="w-full bg-orange-600 text-white px-4 py-2 rounded shadow hover:bg-orange-700 font-bold transition">
                            {{ $investmentId ? 'Atualizar' : 'Salvar Aporte' }}
                        </button>
                        @if($investmentId)
                            <button type="button" wire:click="$set('investmentId', null); $reset(['description', 'amount', 'date'])" class="w-full bg-gray-400 text-white px-4 py-2 rounded shadow hover:bg-gray-500">Cancelar</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- LISTA DE APORTES --}}
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Aportes Realizados em {{ $nomeMes }}</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-50 text-gray-700">
                            <tr>
                                <th class="py-3 px-4 text-left text-sm font-semibold rounded-tl-lg">Data</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold">Investimento</th>
                                <th class="py-3 px-4 text-right text-sm font-semibold">Valor</th>
                                <th class="py-3 px-4 text-center text-sm font-semibold rounded-tr-lg">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($investments as $inv)
                                <tr class="hover:bg-orange-50/50 transition">
                                    <td class="py-3 px-4 text-sm text-gray-600 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($inv->date)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-sm font-medium text-gray-800">
                                        {{ $inv->description }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-right font-black text-green-600 whitespace-nowrap">
                                        R$ {{ number_format($inv->amount, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-center whitespace-nowrap">
                                        <button wire:click="edit({{ $inv->id }})" class="text-blue-500 hover:text-blue-700 mr-3 text-xs font-bold uppercase tracking-wider">Editar</button>
                                        <button wire:click="delete({{ $inv->id }})" wire:confirm="Excluir este aporte?" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase tracking-wider">Apagar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-500">
                                        <div class="text-4xl mb-2">🌱</div>
                                        <p>Nenhum investimento registrado neste mês.</p>
                                        <p class="text-sm">Registre seu primeiro aporte ao lado para ver a barra crescer!</p>
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