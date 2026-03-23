{{-- x-app-layout é o layout padrão do Laravel Breeze, que inclui o menu superior de navegação e a estrutura básica da página --}}
{{-- Dentro do x-app-layout, temos um slot chamado "header" onde colocamos o título da página --}}
{{-- O conteúdo principal da página fica dentro do div com padding, onde injetamos nosso componente Livewire --}}
{{-- O componente Livewire é responsável por toda a lógica de criar, editar e listar as transações, e tudo isso acontece sem recarregar a página inteira, graças ao poder do Livewire --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <livewire:create-transaction />

        </div>
    </div>
</x-app-layout>