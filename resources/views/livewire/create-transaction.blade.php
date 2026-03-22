<div class="p-6 bg-white rounded-lg shadow-md mb-6">
    {{-- BARRA DE NAVEGAÇÃO DOS MESES --}}
    <div class="flex items-center justify-between mb-8 bg-gray-50 p-4 rounded-lg border border-gray-100">
        <button wire:click="previousMonth" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            &laquo; Mês Anterior
        </button>

        <h2 class="text-xl font-bold text-gray-800 uppercase tracking-wide">
            {{ $nomeMes }} {{ $currentYear }}
        </h2>

        <button wire:click="nextMonth" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            Próximo Mês &raquo;
        </button>
    </div>
    {{-- FIM DA BARRA DE NAVEGAÇÃO --}}

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
                    <td class="py-3 px-4 text-sm">
                        <span class="font-medium text-gray-800 block">
                            {{ $transaction->description }}

                            {{-- Exibe 4/10 se for parcelado --}}
                            @if($transaction->total_installments > 1)
                            <span class="text-blue-600 ml-1">({{ $transaction->installment_number }}/{{ $transaction->total_installments }})</span>
                            @endif
                        </span>

                        <div class="flex items-center gap-2 mt-1">
                            @if($transaction->category)
                            <span class="text-xs text-gray-500">{{ $transaction->category->name }}</span>
                            @endif

                            @if($transaction->creditCard)
                            <span class="text-xs text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-100">💳 {{ $transaction->creditCard->name }}</span>
                            @endif
                        </div>
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

                        {{-- Botão Copiar --}}
                        <button wire:click="duplicateToNextMonth({{ $transaction->id }})" class="text-green-600 hover:text-green-800 mr-2" title="Duplicar para o mês seguinte">
                            Copiar
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
            <input type="text" wire:model="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="">
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
                <label class="block text-sm font-medium text-gray-700">Data (Pagamento/Fatura)</label>
                <input type="date" wire:model="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- CAMPO: Tipo (Entrada/Saída) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tipo</label>
                {{-- Usamos wire:model.live para a tela reagir na hora se escolher "Receita" --}}
                <select wire:model.live="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="expense">Saída (Gasto)</option>
                    <option value="income">Entrada (Receita)</option>
                </select>
                @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- CAMPO: Categoria com opção de criar na hora --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Categoria</label>

                <div class="flex gap-2 mt-1">
                    {{-- O Select de categorias --}}
                    <select wire:model="categoryId" class="block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Sem categoria</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>

                    {{-- O input e o botão para criar uma nova --}}
                    <input type="text" wire:model="newCategoryName" placeholder="Nova" class="block w-1/3 rounded-md border-gray-300 shadow-sm">

                    {{-- O wire:click.prevent impede que a página recarregue --}}
                    <button type="button" wire:click.prevent="addCategory" class="bg-green-600 text-white px-3 py-2 rounded shadow hover:bg-green-700 font-bold">
                        +
                    </button>
                </div>

                @error('categoryId') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                @error('newCategoryName') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- SEÇÃO DE CARTÃO DE CRÉDITO (Só aparece se for Saída) --}}
        @if($type === 'expense')
        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Cartão de Crédito (Opcional)</label>
                {{-- wire:model.live avisa o sistema se o usuário selecionou um cartão --}}
                <select wire:model.live="creditCardId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Pago no Pix / Dinheiro</option>
                    @foreach($creditCards as $card)
                    <option value="{{ $card->id }}">{{ $card->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- SEÇÃO DE PARCELAMENTO (Só aparece se escolheu um cartão e NÃO está editando) --}}
            @if($creditCardId && !$transactionId)
            <div class="flex items-center mt-2">
                <input type="checkbox" id="isInstallment" wire:model.live="isInstallment" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                <label for="isInstallment" class="ml-2 block text-sm font-bold text-gray-700">É uma compra parcelada?</label>
            </div>

            @if($isInstallment)
            <div class="p-4 bg-white border border-blue-100 rounded shadow-inner space-y-4">

                {{-- LÓGICA DO VALOR --}}
                <div class="flex gap-4 items-center">
                    <span class="text-sm font-medium text-gray-700">O valor lá de cima é:</span>
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="installmentType" value="installment_value" class="text-blue-600">
                        <span class="ml-2 text-sm text-gray-600">Valor da Parcela</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="installmentType" value="total_value" class="text-blue-600">
                        <span class="ml-2 text-sm text-gray-600">Valor Total</span>
                    </label>
                </div>

                {{-- LÓGICA DA PARCELA --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Parcela Atual</label>
                        <input type="number" min="1" wire:model="currentInstallment" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">De (Total de Parcelas)</label>
                        <input type="number" min="2" wire:model="totalInstallments" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
            </div>
            @endif
            @endif
        </div>
        @endif

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
</div>