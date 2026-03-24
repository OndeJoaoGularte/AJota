<div>
    @if (session()->has('card_message'))
        <div class="p-3 mb-6 text-green-400 bg-green-900/30 rounded text-sm font-bold border border-green-800 shadow-sm">
            {{ session('card_message') }}
        </div>
    @endif

    {{-- O FORMULÁRIO SÓ APARECE SE FOR CHAMADO --}}
    @if($showForm)
        <div class="p-6 bg-slate-800 rounded-lg shadow-xl mb-6 border border-orange-500/50 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-orange-400 to-orange-600"></div>
            
            <h3 class="text-lg font-black text-slate-100 uppercase tracking-wider mb-6 flex items-center gap-2">
                {{ $cardId ? '✏️ Editar Cartão' : '✨ Adicionar Novo Cartão' }}
            </h3>
            
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
                    {{-- Nome do Cartão --}}
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-slate-300">Nome do Cartão</label>
                        <input type="text" wire:model="name" placeholder="Ex: Nubank" class="mt-1 block w-full rounded-md bg-slate-900 border-slate-700 text-slate-200 shadow-sm focus:ring-orange-500">
                        @error('name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Limite Total --}}
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-slate-300">Limite Total (R$)</label>
                        <input type="number" step="0.01" wire:model="limit" class="mt-1 block w-full rounded-md bg-slate-900 border-slate-700 text-slate-200 shadow-sm focus:ring-orange-500">
                        @error('limit') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Gasto Máximo (Meta) --}}
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-slate-300">Meta Mensal (Opcional)</label>
                        <input type="number" step="0.01" wire:model="max_spend" class="mt-1 block w-full rounded-md bg-slate-900 border-slate-700 text-slate-200 shadow-sm focus:ring-orange-500">
                        @error('max_spend') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Dia de Fechamento --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Fechamento</label>
                        <input type="number" wire:model="closing_day" min="1" max="31" class="mt-1 block w-full rounded-md bg-slate-900 border-slate-700 text-slate-200 shadow-sm focus:ring-orange-500">
                        @error('closing_day') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Dia de Vencimento --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Vencimento</label>
                        <input type="number" wire:model="due_day" min="1" max="31" class="mt-1 block w-full rounded-md bg-slate-900 border-slate-700 text-slate-200 shadow-sm focus:ring-orange-500">
                        @error('due_day') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Seletor de Cor --}}
                    <div class="md:col-span-8">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Cor de Identificação</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach($availableColors as $colorKey => $colorName)
                                <label class="cursor-pointer group relative">
                                    <input type="radio" wire:model.live="color" value="{{ $colorKey }}" class="hidden">
                                    <div @class([
                                        'w-8 h-8 rounded-full border-2 transition-transform duration-200',
                                        'bg-purple-500' => $colorKey === 'purple', 'bg-orange-500' => $colorKey === 'orange',
                                        'bg-blue-500' => $colorKey === 'blue', 'bg-green-500' => $colorKey === 'green',
                                        'bg-red-500' => $colorKey === 'red', 'bg-gray-500' => $colorKey === 'gray',
                                        'bg-yellow-400' => $colorKey === 'yellow',
                                        'scale-125 border-slate-200 shadow-md ring-2 ring-offset-2 ring-offset-slate-900 ring-slate-500' => $color === $colorKey,
                                        'border-transparent hover:scale-110' => $color !== $colorKey
                                    ]) title="{{ $colorName }}"></div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center border-t border-slate-700 pt-4 mt-2">
                    <div class="flex gap-2">
                        <button type="submit" class="bg-orange-600 text-white px-6 py-2 rounded shadow hover:bg-orange-500 transition font-bold">
                            {{ $cardId ? 'Salvar Edição' : 'Criar Cartão' }}
                        </button>
                        <button type="button" wire:click="closeForm" class="bg-slate-700 text-slate-300 px-6 py-2 rounded shadow hover:bg-slate-600 transition font-bold">
                            Ocultar
                        </button>
                    </div>
                    
                    {{-- O BOTÃO DE EXCLUIR VEM PARA DENTRO DO FORMULÁRIO DE EDIÇÃO --}}
                    @if($cardId)
                        <button type="button" wire:click="delete" wire:confirm="🚨 PERIGO: Isso apagará o cartão E TODAS as compras atreladas a ele para sempre! Continuar?" class="bg-red-900/30 text-red-400 hover:bg-red-600 hover:text-white border border-red-900/50 hover:border-red-500 px-4 py-2 rounded shadow transition font-bold">
                            Excluir Cartão
                        </button>
                    @endif
                </div>
            </form>
        </div>
    @endif
</div>