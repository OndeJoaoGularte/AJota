<div class="p-6 bg-white rounded-lg shadow-md mb-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Resumo deste Mês</h2>

    {{-- GRID DE CARDS: Usa o Tailwind para colocar 1 coluna no celular e 3 no PC --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">


        {{-- CARD DE RECEITAS (Verde) --}}
        <div class="bg-green-50 rounded-lg p-5 border border-green-100 shadow-sm">
            <div class="text-green-500 text-sm font-semibold uppercase tracking-wider mb-1">Entradas</div>
            <div class="text-2xl font-bold text-green-700">
                R$ {{ number_format($totalIncome, 2, ',', '.') }}
            </div>
        </div>

        {{-- CARD DE GASTOS (Vermelho) --}}
        <div class="bg-red-50 rounded-lg p-5 border border-red-100 shadow-sm">
            <div class="text-red-500 text-sm font-semibold uppercase tracking-wider mb-1">Saídas</div>
            <div class="text-2xl font-bold text-red-700">
                R$ {{ number_format($totalExpense, 2, ',', '.') }}
            </div>
        </div>

        {{-- CARD DE SALDO FINAL (Dinâmico: Azul se positivo, Laranja se negativo) --}}
        <div class="{{ $balance >= 0 ? 'bg-blue-50 border-blue-100' : 'bg-orange-50 border-orange-100' }} rounded-lg p-5 border shadow-sm">
            <div class="{{ $balance >= 0 ? 'text-blue-500' : 'text-orange-500' }} text-sm font-semibold uppercase tracking-wider mb-1">Saldo Atual</div>
            <div class="text-2xl font-bold {{ $balance >= 0 ? 'text-blue-700' : 'text-orange-700' }}">
                R$ {{ number_format($balance, 2, ',', '.') }}
            </div>
        </div>
    </div>

    <hr class="mb-6 border-gray-200">

    {{-- TÍTULO DO FORMULÁRIO: Muda dinamicamente se estamos criando ou editando um registro --}}
    <h2 class="text-xl font-bold mb-4">
        {{ $transactionId ? 'Editar Transação' : 'Adicionar Nova Transação' }}
    </h2>

    {{-- FEEDBACK VISUAL: Mostra a mensagem verde quando salva ou deleta com sucesso --}}
    @if (session()->has('message'))
    <div class="p-3 mb-4 text-green-700 bg-green-100 rounded">
        {{ session('message') }}
    </div>
    @endif

    {{-- FORMULÁRIO: wire:submit intercepta o envio e chama a função save() no PHP --}}
    <form wire:submit="save" class="space-y-4">

        {{-- CAMPO: Descrição --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">Descrição</label>
            {{-- wire:model: Liga este input à variável $description lá no PHP em tempo real --}}
            <input type="text" wire:model="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ex: Mercado, Salário...">
            {{-- Mostra erro de validação (ex: campo vazio) --}}
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            {{-- CAMPO: Valor --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Valor (R$)</label>
                <input type="number" step="0.01" wire:model="amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- CAMPO: Data --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Data</label>
                <input type="date" wire:model="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- CAMPO: Tipo (Entrada/Saída) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">Tipo</label>
            <select wire:model="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="expense">Saída (Gasto)</option>
                <option value="income">Entrada (Receita)</option>
            </select>
            @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- BOTÕES DE AÇÃO --}}
        <div class="flex gap-2">
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">
                {{ $transactionId ? 'Atualizar' : 'Salvar' }}
            </button>

            {{-- Mostra o botão Cancelar apenas se estivermos editando um registro existente --}}
            @if($transactionId)
            <button type="button" wire:click="resetForm" class="w-full bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600">
                Cancelar
            </button>
            @endif
        </div>
    </form>

    <hr class="my-8 border-gray-200">

    <h3 class="text-lg font-bold mb-4">Meus Lançamentos</h3>

    {{-- TABELA DE REGISTROS --}}
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="py-3 px-4 text-left text-sm font-semibold">Data</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold">Descrição</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold">Tipo</th>
                    <th class="py-3 px-4 text-right text-sm font-semibold">Valor</th>
                    <th class="py-3 px-4 text-center text-sm font-semibold">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                {{-- Loop para percorrer todas as transações. O 'forelse' tem um fallback caso esteja vazio --}}
                @forelse ($transactions as $transaction)
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-3 px-4 text-sm text-gray-600">
                        {{-- Formata a data do banco (Y-m-d) para o formato brasileiro (d/m/Y) --}}
                        {{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}
                    </td>
                    <td class="py-3 px-4 text-sm font-medium text-gray-800">
                        {{ $transaction->description }}
                    </td>
                    <td class="py-3 px-4 text-sm">
                        {{-- Badge visual dependendo do tipo --}}
                        @if($transaction->type === 'income')
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">Receita</span>
                        @else
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">Gasto</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-sm text-right font-bold {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                        R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                    </td>
                    <td class="py-3 px-4 text-sm text-center">
                        {{-- Botão Editar: Chama a função edit() passando o ID do registro clicado --}}
                        <button wire:click="edit({{ $transaction->id }})" class="text-blue-600 hover:text-blue-800 mr-2">
                            Editar
                        </button>
                        {{-- Botão Excluir: O wire:confirm gera um alerta no navegador para evitar exclusões acidentais --}}
                        <button wire:click="delete({{ $transaction->id }})" wire:confirm="Tem certeza que deseja excluir esse registro?" class="text-red-600 hover:text-red-800">
                            Excluir
                        </button>
                    </td>
                </tr>
                @empty
                {{-- Caso não tenha nenhum registro no banco --}}
                <tr>
                    <td colspan="5" class="py-4 text-center text-gray-500 text-sm">
                        Nenhuma transação cadastrada ainda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>