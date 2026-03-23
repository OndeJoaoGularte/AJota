<div>
    <div>
        {{-- BARRA DE FILTROS: ETIQUETAS DE MESES --}}
        <div class="mb-8 bg-white p-5 rounded-lg shadow-sm border border-orange-100">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">Filtrar por Período:</h3>

            <div class="flex flex-wrap gap-2">
                @foreach($availableMonths as $value => $label)
                {{-- O wire:key ensina o Livewire a não se perder nos botões --}}
                <label wire:key="month-{{ $value }}" class="cursor-pointer select-none">
                    <input type="checkbox" wire:model.live="selectedMonths" value="{{ $value }}" class="hidden">

                    {{-- O @class do Laravel resolve as cores dinâmicas com perfeição! --}}
                    <div @class([ 'px-4 py-2 rounded-full border text-sm font-bold transition-all duration-200' , 'bg-orange-500 text-white border-orange-600 shadow-md'=> in_array($value, $selectedMonths),
                        'bg-white border-gray-200 text-gray-500 hover:bg-gray-50' => !in_array($value, $selectedMonths)
                        ])>
                        {{ $label }}
                    </div>
                </label>
                @endforeach
            </div>

            @if(empty($selectedMonths))
            <div class="mt-3 text-xs text-red-500 font-bold">
                ⚠️ Selecione pelo menos um mês para visualizar os dados.
            </div>
            @endif
        </div>

        {{-- O GRID DAS 3 COLUNAS PERFEITAS --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- COLUNA 1: O RAIO-X (Lista de Totais do Mês) --}}
            <div class="lg:col-span-1 space-y-3">
                <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Onde a água está vazando?</h3>

                @forelse($categoriesWithTotals as $category)
                @if($category->total_spent > 0)
                <button wire:click="selectCategory({{ $category->id }})"
                    class="w-full text-left p-4 rounded-lg border transition-all duration-200 shadow-sm flex justify-between items-center
                            {{ $selectedCategoryId === $category->id ? 'bg-orange-50 border-orange-400 ring-2 ring-orange-200' : 'bg-white border-gray-200 hover:border-orange-300 hover:shadow-md' }}">

                    <span class="font-bold text-gray-700">{{ $category->name }}</span>
                    <span class="font-black {{ $selectedCategoryId === $category->id ? 'text-orange-600' : 'text-gray-600' }}">
                        R$ {{ number_format($category->total_spent, 2, ',', '.') }}
                    </span>
                </button>
                @endif
                @empty
                <div class="text-gray-500 text-sm italic p-4 bg-white rounded-lg border border-gray-200">
                    Nenhuma gota gasta neste mês ainda.
                </div>
                @endforelse
            </div>

            {{-- COLUNA 2: OS DETALHES DA CATEGORIA CLICADA --}}
            <div class="lg:col-span-1">
                <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Detalhes do Vazamento</h3>

                @if($selectedCategoryId)
                <div class="bg-white p-4 rounded-lg shadow-md border-t-4 border-orange-500">
                    <h4 class="font-black text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-orange-500">💧</span> {{ $selectedCategoryName }}
                    </h4>

                    <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                        @forelse($selectedTransactions as $transaction)
                        <div class="bg-gray-50 p-3 rounded border border-gray-100 flex justify-between items-center hover:bg-orange-50/50 transition">
                            <div>
                                <div class="text-xs text-gray-400 mb-1">{{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}</div>
                                <div class="text-sm font-medium text-gray-800">
                                    {{ $transaction->description }}
                                    @if($transaction->total_installments > 1)
                                    <span class="text-orange-600 text-[10px] ml-1">({{ $transaction->installment_number }}/{{ $transaction->total_installments }})</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm font-bold text-red-600">
                                R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-gray-500 text-sm">Nenhum detalhe encontrado.</div>
                        @endforelse
                    </div>
                </div>
                @else
                <div class="bg-gray-50 p-8 rounded-lg border-2 border-dashed border-gray-300 text-center flex flex-col items-center justify-center h-48">
                    <span class="text-3xl mb-2">👈</span>
                    <p class="text-gray-500 text-sm">
                        Clique numa categoria ao lado para ver os detalhes.
                    </p>
                </div>
                @endif
            </div>

            {{-- COLUNA 3: A OFICINA (Gerenciador de Categorias) --}}
            <div class="lg:col-span-1">
                <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">A Oficina (Gerenciar)</h3>

                <div class="bg-white p-5 rounded-lg shadow-md border border-gray-200">

                    @if (session()->has('cat_msg'))
                    <div class="p-2 mb-4 text-green-700 bg-green-100 rounded text-sm font-medium">
                        {{ session('cat_msg') }}
                    </div>
                    @endif

                    {{-- TELA DE AVISO DE TRANSFERÊNCIA --}}
                    @if($categoryToDelete)
                    <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-4 rounded">
                        <h4 class="font-bold text-orange-800 flex items-center gap-2 mb-2">
                            ⚠️ Atenção!
                        </h4>
                        <p class="text-sm text-orange-700 mb-4">
                            Esta categoria possui <strong>{{ $transactionsCountToTransfer }} contas</strong> atribuídas a ela. Para apagá-la, por favor escolha um novo destino para essas contas:
                        </p>

                        <div class="space-y-3">
                            <div>
                                <select wire:model="replacementCategoryId" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">Selecione a nova categoria...</option>
                                    @foreach($allCategories as $cat)
                                    @if($cat->id !== $categoryToDelete)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endif
                                    @endforeach
                                </select>
                                @error('replacementCategoryId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex gap-2">
                                <button wire:click="deleteAndTransfer" class="w-full bg-orange-600 text-white text-sm px-3 py-2 rounded shadow hover:bg-orange-700 font-bold">
                                    Transferir e Apagar
                                </button>
                                <button wire:click="cancelDelete" class="w-full bg-gray-400 text-white text-sm px-3 py-2 rounded shadow hover:bg-gray-500">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- MODO NORMAL (Criar/Editar e Listar) --}}
                    @else
                    {{-- O Formulário --}}
                    <form wire:submit="saveCategory" class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $manageCategoryId ? 'Editar Nome' : 'Nova Categoria' }}
                        </label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="manageCategoryName" placeholder="Ex: Assinaturas" class="block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-900 transition">
                                {{ $manageCategoryId ? 'Salvar' : 'Criar' }}
                            </button>
                        </div>
                        @error('manageCategoryName') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </form>

                    {{-- A Lista para Editar/Excluir --}}
                    <div class="space-y-2 max-h-[350px] overflow-y-auto pr-2">
                        @foreach($allCategories as $cat)
                        <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded border border-transparent hover:border-gray-100 transition">
                            <span class="text-sm font-medium text-gray-700">{{ $cat->name }}</span>
                            <div class="flex gap-3">
                                <button wire:click="editCategory({{ $cat->id }})" class="text-blue-500 hover:text-blue-700 text-xs font-bold uppercase tracking-wider">
                                    Editar
                                </button>
                                <button wire:click="confirmDelete({{ $cat->id }})" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase tracking-wider">
                                    Apagar
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                </div>
            </div>

        </div>
    </div>