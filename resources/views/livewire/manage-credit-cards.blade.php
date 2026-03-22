<div class="p-6 bg-white rounded-lg shadow-md mb-6">
    <h2 class="text-xl font-bold mb-4 text-gray-800">Meus Cartões de Crédito</h2>

    @if (session()->has('card_message'))
        <div class="p-3 mb-4 text-green-700 bg-green-100 rounded text-sm">
            {{ session('card_message') }}
        </div>
    @endif

    {{-- Formulário Compacto --}}
    <form wire:submit="save" class="flex flex-col md:flex-row gap-4 items-end mb-6">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700">Nome do Cartão</label>
            <input type="text" wire:model="name" placeholder="Ex: Nubank, Inter..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="w-full md:w-32">
            <label class="block text-sm font-medium text-gray-700">Dia Fechamento</label>
            <input type="number" wire:model="closing_day" placeholder="Ex: 25" min="1" max="31" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('closing_day') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="w-full md:w-32">
            <label class="block text-sm font-medium text-gray-700">Dia Vencimento</label>
            <input type="number" wire:model="due_day" placeholder="Ex: 5" min="1" max="31" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('due_day') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full md:w-auto bg-purple-600 text-white px-6 py-2 rounded shadow hover:bg-purple-700 transition">
            {{ $cardId ? 'Atualizar' : 'Adicionar' }}
        </button>
    </form>

    {{-- Lista de Cartões --}}
    @if($cards->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($cards as $card)
                <div class="border border-gray-200 rounded-lg p-4 flex justify-between items-center bg-gray-50">
                    <div>
                        <div class="font-bold text-gray-800">{{ $card->name }}</div>
                        <div class="text-xs text-gray-500 mt-1">Fecha dia {{ $card->closing_day }} • Vence dia {{ $card->due_day }}</div>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="edit({{ $card->id }})" class="text-blue-500 hover:text-blue-700 text-sm">Editar</button>
                        <button wire:click="delete({{ $card->id }})" wire:confirm="Excluir este cartão?" class="text-red-500 hover:text-red-700 text-sm">Excluir</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>