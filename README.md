# Sistema de Gestão para Barbearias

Sistema completo, em português, para administrar uma barbearia: **site público com agendamento
online**, **painel administrativo** (agenda, PDV, estoque, compras, financeiro, comissões e
relatórios) e **área individual para cada barbeiro**.

Feito para rodar em **hospedagem compartilhada** (cPanel e similares): não exige Node, não exige
build de assets, não exige SSH e tem um **instalador pelo navegador** — o dono da barbearia
sobe os arquivos, abre `/install`, responde um formulário e sai usando.

---

## O que vem pronto

### Site público (o que o cliente final vê)

| Página | Rota | O que faz |
| --- | --- | --- |
| Início | `/` | Vitrine com serviços, horário, endereço e chamada para agendar |
| Serviços | `/servicos` | Lista de serviços com preço e duração |
| Sobre | `/sobre` | Apresentação da barbearia, equipe e contato |
| Agendar | `/agendar` | Cliente escolhe profissional, data e horário livre e envia o pedido |
| Cadastro | `/cadastro` | Cria a conta do cliente (pessoa + cliente + usuário) |

Os dados exibidos (nome, telefone, endereço, Instagram, WhatsApp, horário de funcionamento) vêm
das **Configurações** do painel — nada de editar código.

### Painel administrativo (`/admin`)

| Módulo | O que faz |
| --- | --- |
| **Dashboard** | Faturamento do dia e do mês, ticket médio, agenda de hoje, pedidos pendentes, comissões a pagar, contas a pagar/receber em aberto, produtos com estoque baixo e novos clientes do mês |
| **Agenda** | Agendamentos por dia/profissional/situação, criação e edição com horários livres em tempo real, confirmação, conclusão, falta e cancelamento |
| **Pessoas** | Um cadastro único para clientes, funcionários, fornecedores e outros, com vínculo automático (cliente, funcionário com cargo/comissão, fornecedor com prazo de pagamento) |
| **Usuários** | Acessos ao painel com perfil (admin, gerente, recepção, barbeiro) e ativação/desativação |
| **Serviços** | Serviços com preço, duração, categoria, percentual de comissão e disponibilidade online |
| **Produtos** | Produtos com custo, venda, código/código de barras, estoque atual e mínimo, categorias e ajuste de estoque com histórico |
| **Vendas (PDV)** | Venda com vários serviços e produtos, desconto, forma de pagamento (dinheiro, Pix, débito, crédito, fiado), comprovante para impressão, cancelamento com devolução de estoque |
| **Compras** | Entrada de mercadoria por fornecedor, com geração de conta a pagar e atualização do custo médio |
| **Financeiro** | Contas a pagar e a receber (com baixa e estorno), fiado com vencimento automático e controle de comissões (pagar/estornar) |
| **Relatórios** | Financeiro (receita, despesa, comissão, lucro, ticket médio, vendas por dia e por forma de pagamento), estoque, comissões por profissional, ranking de clientes, aniversariantes do mês e movimentações de estoque |
| **Configurações** | Dados da barbearia, horário da agenda, dias fechados, comissão padrão, prazo do fiado e regras de estoque — gravados no `.env` |

### Área do profissional (`/admin/barbeiro`)

Agenda do dia, histórico de atendimentos, comissões (mês, pendente e pago) e o controle de
**bloquear/liberar os próprios horários** (almoço, folga, compromisso), que some da agenda e do
agendamento online na hora.

### Regras de negócio já implementadas

- **Comissão automática** por venda: usa o percentual do serviço, senão o do profissional, senão o
  padrão das configurações. Produtos só geram comissão se você ligar essa opção.
- **Estoque** com movimentações rastreadas (entrada, saída e ajuste), origem (venda, compra ou
  manual), responsável e data — e bloqueio de venda sem saldo (configurável).
- **Fiado** vira conta a receber com vencimento calculado pelo prazo configurado.
- **Agendamento online** respeita horário de funcionamento, dias fechados, intervalo entre
  horários, antecedência mínima e conflitos de agenda por profissional.
- Cancelar uma venda **devolve o estoque** e remove as comissões ainda não pagas.

---

## Requisitos

| Item | Mínimo |
| --- | --- |
| PHP | **8.2** ou superior (8.3 recomendado) |
| Banco de dados | MySQL 5.7+/MariaDB 10.4+ **ou** SQLite 3 (arquivo) |
| Extensões do PHP | `pdo` + `pdo_mysql` **ou** `pdo_sqlite`, `mbstring`, `openssl`, `ctype`, `json`, `tokenizer`, `xml`, `fileinfo`, `curl` |
| Escrita em disco | `.env`, `storage/` e `bootstrap/cache/` |
| Navegador | Chrome, Edge, Firefox ou Safari recentes (o sistema é responsivo) |

Não é preciso Node.js, npm, Redis, Composer no servidor nem acesso SSH (tudo já vai pronto na
pasta `vendor/`). O front-end usa Bootstrap 5 e Bootstrap Icons via CDN.

---

## Instalação

### 1) Pelo navegador — recomendada (hospedagem compartilhada)

Resumo: envie os arquivos, crie o banco no cPanel e abra `http://seudominio.com.br/install`.

O passo a passo completo, com imagens do que clicar no cPanel, soluções para os erros mais comuns
e o checklist pós-instalação está em **[INSTALACAO.md](INSTALACAO.md)**.

O instalador:

1. confere se o servidor tem o PHP e as extensões necessárias;
2. grava o `.env` (banco, nome da barbearia, URL, chave da aplicação);
3. cria todas as tabelas e os dados iniciais (cargos, categorias);
4. cria o usuário administrador com o e-mail e a senha que você informar;
5. opcionalmente inclui **dados de demonstração** para você conhecer o sistema;
6. bloqueia a própria página de instalação (arquivo `storage/app/installed.lock`).

Se algo der errado (senha do banco errada, por exemplo), ele **mostra a mensagem na tela e
devolve o `.env` ao estado anterior** — o sistema não fica quebrado no meio do caminho.

### 2) Pela linha de comando (SSH)

```bash
php artisan barbearia:install \
  --driver=mysql --host=localhost --port=3306 \
  --database=usuario_barbearia --username=usuario_db --password=SECRETA \
  --nome="Barbearia do João" --url=https://barbeariadojoao.com.br \
  --admin-email=joao@barbeariadojoao.com.br --admin-nome="João" --admin-senha=TrocarEstaSenha \
  --demo
```

Útil também: `--fresh` (recria as tabelas do zero) e `--force` (não faz perguntas).

### 3) Ambiente de desenvolvimento

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed        # adicione DEMO_DATA=true no .env para dados de exemplo
php artisan serve
```

Acesso: <http://localhost:8000> · Painel: <http://localhost:8000/admin>

---

## Primeiro acesso

| Situação | E-mail | Senha |
| --- | --- | --- |
| Instalador web ou `barbearia:install` | o que você informou | a que você informou |
| `php artisan migrate --seed` (dev) | `ADMIN_EMAIL` do `.env` (padrão `admin@barbearia.com`) | `ADMIN_PASSWORD` (padrão `admin123`) |
| Profissional de demonstração | `joao@barbearia.exemplo` | `barbeiro123` |

Para criar a conta administrativa específica de desenvolvimento (`admin@eu.com` / `12345678`), execute:

```bash
php artisan db:seed --class=AdminUserSeeder
```

Esse seeder é executado explicitamente e não faz parte do `DatabaseSeeder` padrão. Se executado novamente, redefine a senha para `12345678`. Use apenas em desenvolvimento ou troque a senha imediatamente; não mantenha essa credencial em produção.

> **Troque a senha do administrador assim que entrar** e nunca deixe `APP_DEBUG=true` em produção.

Criou o administrador errado ou esqueceu a senha?

```bash
php artisan barbearia:admin seu@email.com --nome="Seu Nome" --password=NovaSenha
```

Sem `--password`, o comando gera uma senha e mostra na tela.

### Checklist depois de instalar

1. **Configurações** → endereço, telefone, WhatsApp, Instagram e horário de funcionamento.
2. **Pessoas** → cadastre a equipe como *funcionário* (cargo, percentual de comissão, salário).
3. **Usuários** → dê acesso à recepção e aos barbeiros com o perfil certo.
4. **Serviços** → preços, duração e quais aparecem no agendamento online.
5. **Produtos** → cadastro + estoque inicial (menu Produtos → botão *Estoque*).
6. **Financeiro** → lance as contas fixas (aluguel, energia, fornecedores).
7. Faça uma **venda de teste** e confira o comprovante, a comissão e a baixa de estoque.
8. Apague os dados de demonstração se os tiver instalado (veja *Zerar o sistema*, abaixo).

---

## Papéis e permissões

O papel `admin` tem acesso a tudo. Os demais seguem a tabela (configurável em
`config/barbearia.php`):

| Área | admin | gerente | recepcao | barbeiro | cliente |
| --- | :-: | :-: | :-: | :-: | :-: |
| Painel (`/admin`) | ✔ | ✔ | ✔ | ✔ | ✘ |
| Pessoas (clientes, equipe, fornecedores) | ✔ | ✔ | ✔ | ✘ | ✘ |
| Serviços e categorias | ✔ | ✔ | ✘ | ✘ | ✘ |
| Produtos, estoque e compras | ✔ | ✔ | ✘ | ✘ | ✘ |
| Agenda | ✔ | ✔ | ✔ | ✘ | ✘ |
| Vendas (PDV) | ✔ | ✔ | ✔ | ✘ | ✘ |
| Financeiro e comissões | ✔ | ✔ | ✘ | ✘ | ✘ |
| Relatórios | ✔ | ✔ | ✘ | ✘ | ✘ |
| Usuários e configurações | ✔ | ✘ | ✘ | ✘ | ✘ |
| Área do profissional | ✔ | ✔ | ✘ | ✔ | ✘ |

Clientes cadastrados pelo site só acessam o site público e o agendamento.

---

## Configuração (`.env`)

Tudo que o dono da barbearia costuma ajustar está na tela **Configurações** do painel, que grava
direto no `.env`. Para editar manualmente, copie `.env.example` como `.env` e rode
`php artisan config:clear` depois de salvar.

| Variável | Padrão | O que faz |
| --- | --- | --- |
| `BARBEARIA_NOME` | `Barbearia` | Nome exibido no site, no painel e nos comprovantes |
| `BARBEARIA_TELEFONE` / `_EMAIL` / `_ENDERECO` / `_CIDADE` | — | Contato e rodapé do site |
| `BARBEARIA_INSTAGRAM` / `BARBEARIA_WHATSAPP` | — | Botões de contato (WhatsApp só com DDD + número) |
| `APP_URL` | `http://localhost` | Base dos links gerados |
| `AGENDA_ABERTURA` / `AGENDA_FECHAMENTO` | `09:00` / `20:00` | Horário de funcionamento da agenda |
| `AGENDA_INTERVALO` | `30` | Minutos entre os horários (10, 15, 20, 30, 45 ou 60) |
| `AGENDA_DIAS_FECHADOS` | `0` | Dias sem atendimento: `0`=domingo … `6`=sábado (ex.: `0,6`) |
| `AGENDA_ANTECEDENCIA_MINIMA` | `60` | Minutos mínimos de antecedência para agendar |
| `AGENDA_DIAS_VISIVEIS` | `30` | Quantos dias à frente o cliente vê no site |
| `COMISSAO_PADRAO` | `40` | Comissão (%) quando ninguém definiu outra |
| `COMISSAO_PRODUTOS` | `false` | Pagar comissão também sobre produtos |
| `BLOQUEAR_VENDA_SEM_ESTOQUE` | `true` | Impedir venda acima do saldo em estoque |
| `PRAZO_FIADO_DIAS` | `30` | Vencimento das vendas no fiado |
| `PAGINACAO_POR_PAGINA` | `15` | Itens por página nas listagens |
| `DEMO_DATA` | `false` | `true` no `db:seed` inclui dados de demonstração |
| `DB_CONNECTION` | `mysql` | `mysql` ou `sqlite` |
| `SESSION_DRIVER` | `database` | `database` (recomendado) ou `file` |
| `APP_DEBUG` | `false` | **Deixe `false` em produção** |

---

## Comandos úteis

| Comando | Para que serve |
| --- | --- |
| `php artisan barbearia:diagnostico` | **Ferramenta de suporte**: confere PHP, extensões, `.env`, conexão, tabelas, migrations pendentes e abre todas as telas principais, mostrando o que está errado |
| `php artisan barbearia:install` | Instalação pela linha de comando |
| `php artisan barbearia:admin email@x.com --nome=Nome --password=Senha` | Cria ou redefine o administrador |
| `php artisan migrate --force` | Aplica atualizações de banco depois de trocar os arquivos |
| `php artisan migrate:fresh --seed --force` | **Zera o sistema** e recria tudo (com demo se `DEMO_DATA=true`) |
| `php artisan config:clear` | Limpa o cache de configuração (use depois de editar o `.env`) |
| `php artisan optimize:clear` | Limpa configuração, rotas e views cacheadas |

---

## Estrutura do projeto

```
app/
  Console/Commands/       barbearia:install, barbearia:admin, barbearia:diagnostico
  Http/Controllers/       Site, Auth, Dashboard, Agenda, Pessoas, Usuários, Serviços,
                          Produtos, Vendas, Compras, Financeiro, Relatórios, Barbeiro,
                          Configurações e Install
  Http/Middleware/        role: e permission: (papéis e permissões)
  Models/                 19 models com escopos, accessors e constantes de status
  Services/               AgendaService, VendaService, CompraService, EstoqueService
                          (regras de negócio com transações)
  Support/                EnvEditor (grava o .env) e Number (números no padrão brasileiro)
config/barbearia.php      horários, comissões, permissões e regras do negócio
database/
  migrations/             22 migrations idempotentes (não estouram se a tabela já existir)
  seeders/                DatabaseSeeder, AdminUserSeeder (uso explícito) e DemoSeeder (dados de exemplo)
public/                   único diretório que deve ficar exposto (index.php + .htaccess)
resources/views/          Blade: site público, painel, área do barbeiro e instalador
routes/web.php            todas as rotas (site, auth, admin, barbeiro, install)
.htaccess                 raiz: encaminha tudo para public/ e bloqueia arquivos sensíveis
INSTALACAO.md             passo a passo para hospedagem compartilhada
```

---

## Atualizar o sistema sem perder dados

Os seus dados ficam no **banco** e no **`.env`** — nunca nas pastas `app/`, `resources/` ou
`vendor/`. Portanto:

1. Faça backup do banco e do `.env` (veja abaixo).
2. Envie os arquivos novos por cima, **sem apagar** `.env`, `storage/` e `database/database.sqlite`
   (se usar SQLite).
3. Rode `php artisan migrate --force` (aplica só o que mudou no banco).
4. Rode `php artisan optimize:clear`.
5. Confira com `php artisan barbearia:diagnostico`.

Pelo cPanel, sem SSH: envie os arquivos novos e acesse `http://seudominio.com.br/install` — se o
sistema já estiver instalado, a página avisa e não mexe em nada. Para aplicar migrations sem SSH,
peça à hospedagem para rodar o comando ou use o terminal do cPanel, quando disponível.

---

## Backup

- **MySQL**: cPanel → *phpMyAdmin* → selecione o banco → *Exportar* → *Executar* (gera um `.sql`).
- **SQLite**: copie o arquivo `database/database.sqlite`.
- Copie também o `.env` (tem a chave da aplicação e as configurações) e, se quiser,
  `storage/app/installed.lock`.
- Restauração: crie o banco, importe o `.sql` (phpMyAdmin → *Importar*), recoloque o `.env` e rode
  `php artisan config:clear`.

---

## Zerar o sistema (apagar tudo e recomeçar)

```bash
# apaga todos os dados e recria as tabelas com a base mínima
php artisan migrate:fresh --seed --force

# idem, mas com dados de demonstração (DEMO_DATA=true no .env)
php artisan migrate:fresh --seed --force
```

Sem SSH: rode o instalador novamente marcando **"Recriar as tabelas"** — apague antes o arquivo
`storage/app/installed.lock`.

---

## Problemas rápidos

| Sintoma | Causa provável | Solução |
| --- | --- | --- |
| Página em branco | `APP_DEBUG=false` escondendo o erro | Olhe `storage/logs/laravel.log` ou rode `barbearia:diagnostico` |
| Erro 500 ao abrir qualquer página | Permissão de escrita em `storage/` e `bootstrap/cache/` | `chmod 755` nas pastas e `644` no `.env` |
| "No application encryption key has been specified" | `.env` sem `APP_KEY` | `php artisan key:generate` ou use o instalador |
| "could not find driver" | Extensão do banco desligada no PHP | Ative `pdo_mysql` (ou `pdo_sqlite`) no cPanel |
| "table ... already exists" | Banco com tabelas de outra versão | Use "Recriar as tabelas" no instalador ou `migrate:fresh` |
| Erro 404 em todas as páginas | Domínio não aponta para `public/` e o `.htaccess` não foi lido | Veja o passo 3 do [INSTALACAO.md](INSTALACAO.md) |
| Saiu do login sozinho / erro 419 | Sessão sem permissão de escrita | Use `SESSION_DRIVER=database` ou dê escrita em `storage/framework/sessions` |

Detalhes, com o que fazer em cada caso: **[INSTALACAO.md](INSTALACAO.md)**.

---

## Licença

Software proprietário. A licença de uso é definida no contrato de venda: cada barbearia adquire o
direito de usar o sistema em um domínio. A remoção dos créditos e a revenda do código não estão
incluídas.
