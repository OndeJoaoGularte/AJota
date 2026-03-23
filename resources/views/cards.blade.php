<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <span class="text-orange-500">A</span>Gota <span class="text-gray-400 font-normal ml-2">| Meus Cartões</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- A Oficina: Onde você cria e edita os cartões (Já está pronto!) --}}
            <livewire:manage-credit-cards />

            {{-- NOVO: O Visualizador de Faturas --}}
            <livewire:credit-card-invoice />
            
        </div>
    </div>
</x-app-layout>