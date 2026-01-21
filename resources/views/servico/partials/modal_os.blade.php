<div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="open = false"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border-t-8 border-[#358054]">
            
            {{-- MARCA D'ÁGUA --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0">
                <img src="{{ asset('images/logo.png') }}" class="w-2/3 object-contain opacity-15">
            </div>

            <div class="relative z-10 p-8"> 
                
                 <div class="flex justify-between items-center mb-6 border-b pb-4 print:mb-2 print:pb-2">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/secretaria_logo.png') }}" class="h-16 w-auto object-contain print:h-12" alt="Logo">
                    <div class="text-xs text-gray-600 leading-tight font-bold uppercase text-left">
                        ESTADO DO RIO DE JANEIRO<br>
                        MUNICÍPIO DE PARACAMBI<br>
                        SECRETARIA MUNICIPAL DE MEIO AMBIENTE<br>
                        SUPER INTENDÊNCIA DE ÁREAS VERDES<br>
                        DIRETORIA DE ARBORIZAÇÃO URBANA
                    </div>
                </div>
                <div class="text-right">
                    <h3 class="font-bold mb-1 uppercase text-black uppercase border-b-2 border-black">ORDEM DE SERVIÇO</h3>
                    <p class="text-sm font-bold mt-1 text-black">Poda e Remoção de Árvores</p>
                </div>
            </div>

                {{-- DADOS (SOMENTE LEITURA) --}}
                <div class="text-sm space-y-6">

                    {{-- DADOS ESTÁTICOS --}}
                    <div class="grid grid-cols-2 gap-4 border-b border-gray-300 pb-2">
                        <div>
                            <label class="font-bold mb-1 uppercase text-black">Nº Solicitação:</label>
                            <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.contact ? '#' + item.contact.id : ''"></p>
                        </div>
                        <div>
                            <label class="font-bold mb-1 uppercase text-black">Data de Emissão:</label>
                            <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.created_at ? new Date(item.created_at).toLocaleDateString('pt-BR') : 'N/A'"></p>
                        </div>
                    </div>

                    <div class="border-b border-gray-300 pb-2 text-left">
                        <h4 class="font-bold mb-1 uppercase text-black">Identificação da Área</h4>
                        <p class="mb-2"><strong>Endereço:</strong> <span x-text="item.contact ? (item.contact.rua + ', ' + item.contact.numero + ' - ' + item.contact.bairro) : ''"></span></p>
                        
                        {{-- LAT/LONG --}}
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">Latitude:</label>
                                <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.latitude || 'N/R'"></p>
                            </div>
                            <div>
                                <label class="font-bold mb-1 uppercase text-black">Longitude:</label>
                                <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.longitude || 'N/R'"></p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 border-b border-gray-300 pb-2 text-left">
                        <div class="col-span-2">
                            <label class="font-bold mb-1 uppercase text-black">Espécie(s):</label>
                            <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.especies ? (Array.isArray(item.especies) ? item.especies.join(', ') : item.especies) : 'N/A'"></p>
                        </div>
                        <div>
                            <label class="font-bold mb-1 uppercase text-black">Quantidade:</label>
                            <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.quantidade || '1'"></p>
                        </div>
                    </div>

                    {{-- MOTIVO DA INTERVENÇÃO --}}
                    <div class="border-b border-gray-300 pb-2 bg-gray-50/80 p-2 rounded">
                        <h4 class="font-bold mb-1 uppercase text-black">Motivo da Intervenção</h4>
                        <div class="space-y-1">
                            <template x-for="motivo in (item.motivos || [])">
                                <div class="flex items-center gap-2">
                                    <span class="text-[#358054]">✔</span> 
                                    <span class="text-sm text-black" x-text="motivo"></span>
                                </div>
                            </template>
                            <div x-show="!item.motivos || item.motivos.length === 0" class="text-sm text-gray-500 italic">Não informado</div>
                        </div>
                    </div>

                    {{-- SERVIÇOS E EPI'S --}}
                    <div class="grid grid-cols-2 gap-4 text-left mb-4 mt-4">
                        <div class="bg-gray-50/80 p-3 rounded border border-gray-200">
                            <h4 class="font-bold mb-1 uppercase text-black">Serviços a Executar</h4>
                            <div class="space-y-1">
                                <template x-for="servico in (item.servicos || [])">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[#358054]">✔</span> 
                                        <span class="text-sm text-black" x-text="servico"></span>
                                    </div>
                                </template>
                                <div x-show="!item.servicos || item.servicos.length === 0" class="text-sm text-gray-500 italic">Nenhum serviço especificado.</div>
                            </div>
                        </div>
                        <div class="bg-gray-50/80 p-3 rounded border border-gray-200">
                            <h4 class="font-bold mb-1 uppercase text-black">Equipamentos Necessários</h4>
                            <div class="space-y-1">
                                <template x-for="eq in (item.equipamentos || [])">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[#358054]">✔</span> 
                                        <span class="text-sm text-black" x-text="eq"></span>
                                    </div>
                                </template>
                                <div x-show="!item.equipamentos || item.equipamentos.length === 0" class="text-sm text-gray-500 italic">Nenhum equipamento listado.</div>
                            </div>
                        </div>
                    </div>

                    {{-- AGENDAMENTO --}}
                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 text-left mb-4">
                        <label class="font-bold block text-yellow-800 uppercase text-[12px] mb-1">Data Programada para Execução:</label>
                        
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-6 h-6 text-yellow-700"></i>
                            {{-- DATA CORRIGIDA PARA EVITAR INVALID DATE --}}
                            <span class="text-xl font-extrabold text-gray-900" 
                                  x-text="item.data_execucao ? item.data_execucao.substring(0, 10).split('-').reverse().join('/') : 'Não agendado'">
                            </span>
                        </div>
                        <p class="text-[10px] text-yellow-700 mt-1 italic">* Atenção ao prazo definido.</p>
                    </div>

                    {{-- OBSERVAÇÕES --}}
                    <div x-show="item.observacoes" class="border p-2 bg-gray-50 rounded">
                        <label class="font-bold mb-1 uppercase text-black">Observações:</label>
                        <p class="w-full border-0 border-b border-gray-400 bg-gray-50 p-1 focus:ring-0 text-black" x-text="item.observacoes"></p>
                    </div>

                    {{-- RODAPÉ (ASSINATURAS) --}}
                    <div class="grid grid-cols-2 gap-8 mt-12 pt-4 print:mt-6 text-center">
                        <div class="border-t border-black pt-2"><p class="text-xs font-bold uppercase">Responsável Técnico</p></div>
                        <div class="border-t border-black pt-2"><p class="text-xs font-bold uppercase">Executado por</p></div>
                    </div>

                   {{-- BOTÕES DO MODAL --}}
                    <div class="mt-8 flex justify-end gap-3 print:hidden items-center">
                        <button type="button" @click="open = false" class="bg-gray-100 border border-gray-300 text-gray-700 px-4 py-2 rounded text-xs font-bold hover:bg-gray-200 transition">
                            Fechar
                        </button>
                        {{-- BOTÃO DE IMPRIMIR AGORA É PRETO (bg-black) --}}
                        <button type="button" onclick="window.print()" class="bg-black text-white px-6 py-2 rounded text-xs font-bold shadow-lg hover:bg-gray-800 flex items-center gap-2">
                            <i data-lucide="printer" class="w-4 h-4"></i> Imprimir OS
                        </button>

                        {{-- AÇÃO: VISTO (Para Recebidas) --}}
                        @if($action === 'visto')
                            <form method="POST" :action="'/pbi-servico/tarefas/' + item.id + '/confirmar'" class="inline">
                                @csrf
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded text-xs font-bold shadow-lg flex items-center gap-2">
                                    <i data-lucide="play" class="w-4 h-4"></i> Visto
                                </button>
                            </form>
                        @endif

                        {{-- AÇÃO: CONCLUIR (Para Em Andamento) --}}
                        @if($action === 'concluir')
                                {{-- CONCLUIR --}}
                                <form method="POST" :action="'/pbi-servico/tarefas/' + item.id + '/concluir'">
                                    @csrf
                                    <button onclick="return confirm('Tem certeza que concluiu este serviço?')" 
                                        class="bg-[#358054] hover:bg-green-700 text-white px-6 py-2 rounded text-xs font-bold shadow-lg flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4"></i> Concluir
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

