// resources/js/agendamento.js

document.addEventListener('DOMContentLoaded', function() {
    const servicoSelect = document.getElementById('servico');
    const funcionarioSelect = document.getElementById('funcionario');
    const dataInput = document.getElementById('data');
    const horaSelect = document.getElementById('hora');
    
    // Carregar funcionários disponíveis
    async function carregarFuncionarios() {
        try {
            const response = await fetch('/api/funcionarios');
            const funcionarios = await response.json();
            funcionarioSelect.innerHTML = '<option value="">Selecione um barbeiro...</option>' + 
                funcionarios.map(func => `<option value="${func.id}">${func.pessoa.nome}</option>`).join('');
        } catch (error) {
            console.error('Erro ao carregar funcionários:', error);
        }
    }
    
    // Carregar horários disponíveis
    async function carregarHorarios() {
        const funcionarioId = funcionarioSelect.value;
        const data = dataInput.value;
        
        if (!funcionarioId || !data) {
            horaSelect.innerHTML = '<option value="">Selecione um horário...</option>';
            return;
        }
        
        horaSelect.innerHTML = '<option value="">Carregando...</option>';
        
        try {
            const response = await fetch(`/api/agendamentos/horarios-disponiveis?funcionario_id=${funcionarioId}&data=${data}`);
            const horarios = await response.json();
            let options = '<option value="">Selecione um horário...</option>';
            horarios.forEach(hora => {
                options += `<option value="${hora}">${hora}</option>`;
            });
            horaSelect.innerHTML = options;
        } catch (error) {
            console.error('Erro ao carregar horários:', error);
            horaSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
        }
    }
    
    // Carregar serviços
    async function carregarServicos() {
        try {
            const response = await fetch('/api/servicos');
            const servicos = await response.json();
            servicoSelect.innerHTML = '<option value="">Selecione um serviço...</option>' + 
                servicos.map(serv => `<option value="${serv.id}" data-duracao="${serv.duracao}" data-valor="${serv.valor}">${serv.nome} - R$ ${new Intl.NumberFormat('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(serv.valor)}</option>`).join('');
        } catch (error) {
            console.error('Erro ao carregar serviços:', error);
        }
    }
    
    // Event listeners
    funcionarioSelect.addEventListener('change', carregarHorarios);
    dataInput.addEventListener('change', carregarHorarios);
    
    // Inicializar ao carregar a página
    carregarFuncionarios();
    carregarServicos();
    
    // Se já tiver funcionário selecionado, carregar horários
    if (funcionarioSelect.value) {
        carregarHorarios();
    }
});