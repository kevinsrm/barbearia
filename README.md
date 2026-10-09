# Sistema de Barbearia - Laravel

Sistema completo de gestão para barbearia com painel administrativo, agenda online e site público.

## Funcionalidades Principais
- Gestão de pessoas (usuários, funcionários, clientes, fornecedores)
- Cadastro de serviços, produtos e estoque
- Controle financeiro (vendas, compras, comissões)
- Agendamento online
- Relatórios e análises
- Site público com CMS
- Área individual para barbeiros

## Tecnologias
- Laravel 12.x
- MySQL
- Bootstrap 5 (responsive)
- Vue.js para componentes interativos

## Instalação

1. Composer install
2. Configurar arquivo .env
3. Executar migrações: `php artisan migrate`
4. Seeders: `php artisan db:seed`

## Estrutura do Projeto

### Database
- Migrations para todas as tabelas
- Factories para testes
- Seeders para dados iniciais

### Models
- Pessoa (User, Funcionario, Cliente, Fornecedor)
- Servico, ServicoCategoria, ServicoValor
- Produto, ProdutoCategoria, Estoque
- Venda, Compra, ContaPagar, ContaReceber, Comissao
- Agendamento, AgendamentoServico

### Controllers
- Auth, Dashboard, Pessoa, Servico, Produto, Financeiro, Agenda, Relatorio, Site

### Views
- Admin (painel administrativo)
- Site (público)
- Components (reutilizáveis)

## Licença
MIT License