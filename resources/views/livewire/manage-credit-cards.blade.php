<div class="p-6 bg-white rounded-lg shadow-md mb-6 border border-gray-200">
    <h2 class="text-xl font-bold mb-6 text-gray-800 border-b pb-2">Minha Carteira (Cartões)</h2>

    @if (session()->has('card_message'))
        <div class="p-3 mb-6 text-green-700 bg-green-100 rounded text-sm font-bold border border-green-200">
            {{ session('card_message') }}
        </div>
    @endif

    {{-- FORMULÁRIO COMPLETO --}}
    <form wire:submit="save" class="mb-8 bg-gray-50 p-5 rounded-lg border border-gray-100">
        <h3 class="text-sm font-bold text-gray-600 uppercase tracking-wider mb-4">
            {{ $cardId ? 'Editar Cartão' : 'Adicionar Novo Cartão' }}
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
            
            {{-- Nome do Cartão --}}
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700">Nome do Cartão</label>
                <input type="text" wire:model="name" placeholder="Ex: Nubank" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Limite Total --}}
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700">Limite Total (R$)</label>
                <input type="number" step="0.01" wire:model="limit" placeholder="Ex: 5000.00" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                @error('limit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Gasto Máximo (Meta) --}}
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700">Gasto Máximo Ideal (Opcional)</label>
                <input type="number" step="0.01" wire:model="max_spend" placeholder="Ex: 2000.00" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                @error('max_spend') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Dia de Fechamento --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Fechamento</label>
                <input type="number" wire:model="closing_day" placeholder="Ex: 25" min="1" max="31" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                @error('closing_day') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Dia de Vencimento --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Vencimento</label>
                <input type="number" wire:model="due_day" placeholder="Ex: 5" min="1" max="31" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                @error('due_day') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Seletor de Cor (Chips visuais) --}}
            <div class="md:col-span-8">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cor de Identificação</label>
                <div class="flex flex-wrap gap-3">
                    @foreach($availableColors as $colorKey => $colorName)
                        <label class="cursor-pointer group relative">
                            <input type="radio" wire:model.live="color" value="{{ $colorKey }}" class="hidden">
                            
                            {{-- A Bolinha de Cor. Usamos um mapa de classes do Tailwind para cada cor --}}
                            <div @class([
                                'w-8 h-8 rounded-full border-2 transition-transform duration-200',
                                'bg-purple-500' => $colorKey === 'purple',
                                'bg-orange-500' => $colorKey === 'orange',
                                'bg-blue-500' => $colorKey === 'blue',
                                'bg-green-500' => $colorKey === 'green',
                                'bg-red-500' => $colorKey === 'red',
                                'bg-gray-800' => $colorKey === 'gray',
                                'bg-yellow-400' => $colorKey === 'yellow',
                                'scale-125 border-gray-900 shadow-md ring-2 ring-offset-1 ring-gray-400' => $color === $colorKey,
                                'border-transparent hover:scale-110' => $color !== $colorKey
                            ]) title="{{ $colorName }}"></div>
                        </label>
                    @endforeach
                </div>
                @error('color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex gap-2 mt-4">
            <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded shadow hover:bg-gray-900 transition font-bold">
                {{ $cardId ? 'Atualizar Cartão' : 'Adicionar à Carteira' }}
            </button>
            @if($cardId)
                <button type="button" wire:click="resetForm" class="bg-gray-300 text-gray-700 px-6 py-2 rounded shadow hover:bg-gray-400 transition font-bold">
                    Cancelar
                </button>
            @endif
        </div>
    </form>

    {{-- LISTA DE CARTÕES ATUAIS --}}
    @if($cards->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($cards as $card)
                {{-- Aqui nós definimos o fundo do cartão com base na cor escolhida! --}}
                <div @class([
                    'rounded-xl p-5 text-white shadow-md relative overflow-hidden',
                    'bg-gradient-to-br from-purple-500 to-purple-700' => $card->color === 'purple',
                    'bg-gradient-to-br from-orange-500 to-orange-700' => $card->color === 'orange',
                    'bg-gradient-to-br from-blue-500 to-blue-700' => $card->color === 'blue',
                    'bg-gradient-to-br from-green-500 to-green-700' => $card->color === 'green',
                    'bg-gradient-to-br from-red-500 to-red-700' => $card->color === 'red',
                    'bg-gradient-to-br from-gray-700 to-gray-900' => $card->color === 'gray',
                    'bg-gradient-to-br from-yellow-400 to-yellow-600 text-gray-900' => $card->color === 'yellow',
                ])>
                    {{-- Efeito visual imitando chip de cartão --}}
                    <div class="absolute top-4 right-4 w-10 h-8 rounded bg-white/20 border border-white/30"></div>
                    
                    <div class="font-black text-xl mb-4 mt-2">{{ $card->name }}</div>
                    
                    <div class="text-sm opacity-90 mb-1">Limite: <strong>R$ {{ number_format($card->limit, 2, ',', '.') }}</strong></div>
                    @if($card->max_spend)
                        <div class="text-xs opacity-80 mb-4">Meta/Máx: R$ {{ number_format($card->max_spend, 2, ',', '.') }}</div>
                    @else
                        <div class="text-xs opacity-80 mb-4">Sem meta de gastos</div>
                    @endif

                    <div class="flex justify-between items-end">
                        <div class="text-xs opacity-90 font-medium">
                            Fecha: {{ $card->closing_day }} • Vence: {{ $card->due_day }}
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="edit({{ $card->id }})" class="bg-white/20 hover:bg-white/40 px-2 py-1 rounded text-xs transition backdrop-blur-sm">Editar</button>
                            <button wire:click="delete({{ $card->id }})" wire:confirm="Excluir este cartão?" class="bg-red-500/80 hover:bg-red-500 px-2 py-1 rounded text-xs transition text-white">X</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>