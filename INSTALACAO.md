# Instalação em hospedagem compartilhada (cPanel, HostGator, Hostinger, Locaweb…)

Este guia foi escrito para quem **não é programador**. Siga na ordem: em cerca de 15 minutos o
sistema está no ar. Você vai precisar de:

- os arquivos do sistema (`.zip` que você recebeu);
- acesso ao painel da hospedagem (cPanel ou equivalente);
- um domínio (ex.: `barbeariadojoao.com.br`).

> Prefere assistir a alguém fazer? Tudo que está aqui cabe em uma tela: **enviar arquivos →
> criar banco → abrir `/install` → entrar no sistema**.

---

## Passo 1 — Conferir a versão do PHP

1. No cPanel, abra **Selecionar versão do PHP** (ou *MultiPHP Manager*).
2. Escolha **PHP 8.2 ou superior** (8.3 é o ideal) e clique em *Aplicar*.
3. Na aba **Extensões**, marque estas (a maioria já vem marcada):

   `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `json`, `tokenizer`, `xml`, `fileinfo`,
   `curl`. Se for usar SQLite, marque `pdo_sqlite` no lugar de `pdo_mysql`.

> Sem isso o instalador avisa na tela o que está faltando (etapa 1 da página de instalação) e o
> botão de instalar fica bloqueado até a hospedagem resolver.

---

## Passo 2 — Enviar os arquivos

**Opção A — Gerenciador de Arquivos (mais fácil)**

1. No cPanel, abra **Gerenciador de Arquivos**.
2. Entre em `public_html` (é a pasta do seu site).
3. Clique em **Upload** e envie o `.zip` do sistema.
4. Volte, clique com o botão direito no `.zip` e escolha **Extract** (Extrair).
5. Confira se, dentro de `public_html`, aparecem as pastas `app`, `config`, `database`,
   `public`, `resources`, `routes`, `storage`, `vendor` e os arquivos `.htaccess`, `artisan` e
   `.env.example`.

> Se a extração criou uma subpasta (ex.: `public_html/barbearia/`), entre nela, selecione tudo e
> use **Move** para `public_html`.

**Opção B — FTP (FileZilla)**: conecte com os dados da hospedagem e envie o conteúdo da pasta do
sistema para `public_html`.

---

## Passo 3 — Criar o banco de dados MySQL

1. No cPanel, abra **Bancos de dados MySQL** (*MySQL Databases*).
2. **Criar novo banco**: dê um nome (ex.: `barbearia`) e clique em *Criar*. O cPanel mostra o nome
   completo, algo como `usuario_barbearia` — **anote**.
3. **Usuários MySQL**: crie um usuário (ex.: `sistema`) com uma **senha forte** — clique em
   *Password Generator* se quiser. **Anote usuário e senha**.
4. **Adicionar usuário ao banco**: selecione o usuário e o banco, clique em *Adicionar* e marque
   **ALL PRIVILEGES** (todos os privilégios) → *Fazer alterações*.

Você vai usar esses três dados no instalador:

| Campo | Exemplo |
| --- | --- |
| Servidor (host) | `localhost` |
| Porta | `3306` |
| Nome do banco | `usuario_barbearia` |
| Usuário | `usuario_sistema` |
| Senha | a que você criou |

> **Hospedagem sem MySQL?** Use o tipo **SQLite**: o sistema cria um arquivo
> `database/database.sqlite` sozinho. Serve bem para uma barbearia só; para várias unidades ou
> muito movimento, prefira MySQL.

---

## Passo 4 — Apontar o site para a pasta `public` (recomendado)

O Laravel precisa que o domínio abra a pasta **`public`**. Você tem duas opções:

**Opção A — Mudar o Document Root (melhor)**

1. No cPanel, abra **Domínios** (ou *Subdomínios* / *Addon Domains*).
2. Ao lado do seu domínio, clique em **Manage** e troque o *Document Root* de `public_html` para
   `public_html/public`.
3. Salve.

**Opção B — Não mudar nada**

O sistema já traz um arquivo `.htaccess` na raiz que envia tudo para `public/` sozinho e ainda
bloqueia o acesso a arquivos sensíveis (como o `.env`). Se o seu painel não permite alterar o
Document Root, simplesmente siga em frente.

---

## Passo 5 — Rodar o instalador

1. No navegador, abra **`http://seudominio.com.br/install`**.
2. A tela mostra a **etapa 1 (verificação do servidor)**: tudo em verde? Ótimo. Algum item em
   vermelho? Volte ao Passo 1 ou chame o suporte da hospedagem.
3. Preencha o formulário:
   - **Banco de dados**: tipo *MySQL*, host, porta, nome do banco, usuário e senha (Passo 3).
   - **Sua barbearia**: nome (aparece no site) e endereço do site (`https://seudominio.com.br`).
   - **Acesso do administrador**: seu nome, seu e-mail e uma senha com pelo menos 6 caracteres.
   - **Opções**:
     - *Dados de demonstração* — marque para conhecer o sistema com clientes, serviços, produtos e
       vendas de exemplo. Você apaga depois.
     - *Recriar as tabelas* — só marque se estiver refazendo uma instalação (isso **apaga** os
       dados do banco).
     - *Modo de depuração* — deixe **desmarcado** em produção.
4. Clique em **Instalar agora** e aguarde alguns segundos.

Pronto: aparece a tela **"Instalação concluída com sucesso!"** com o seu e-mail de acesso.

### O que o instalador fez por você

- criou o arquivo `.env` com banco, URL, nome da barbearia e a chave de segurança (`APP_KEY`);
- criou todas as tabelas e os cadastros base (cargos e categorias de serviços/produtos);
- criou o usuário administrador com o e-mail e a senha informados;
- gravou `storage/app/installed.lock`, que **bloqueia a página de instalação** para ninguém mais
  mexer.

### Se deu erro

A tela mostra a mensagem exata em português e **devolve o `.env` ao estado anterior** (o site não
fica quebrado). Corrija e clique em instalar de novo. As três causas mais comuns:

| Mensagem | O que fazer |
| --- | --- |
| `SQLSTATE[HY000] [1045] Access denied` | Usuário ou senha do banco errados, ou o usuário não foi adicionado ao banco (Passo 3, item 4) |
| `SQLSTATE[HY000] [2002] Connection refused` | Host errado: tente `localhost` ou `127.0.0.1`; algumas hospedagens usam um host próprio (ex.: `mysql.seudominio.com.br`) |
| `Unknown database 'xxx'` | O nome do banco está sem o prefixo da hospedagem: use o nome completo (`usuario_barbearia`) |
| `Não consegui gravar o arquivo .env` | Permissão: no Gerenciador de Arquivos, clique com o botão direito no `.env.example` → *Change Permissions* → `644`; nas pastas `storage` e `bootstrap/cache` → `755` |

---

## Passo 6 — Entrar e configurar

1. Abra **`http://seudominio.com.br/login`**.
2. Entre com o e-mail e a senha do administrador.
3. **Troque a senha** e confira os dados da barbearia em **Configurações** (telefone, endereço,
   WhatsApp, horário de funcionamento, dias fechados e comissão padrão).
4. Siga o checklist:

   | Ordem | Menu | O que cadastrar |
   | --- | --- | --- |
   | 1 | Configurações | Dados da barbearia e horário da agenda |
   | 2 | Pessoas → Funcionários | Equipe, cargo e percentual de comissão |
   | 3 | Usuários | Acesso da recepção e dos barbeiros |
   | 4 | Serviços | Corte, barba, combos: preço, duração e comissão |
   | 5 | Produtos | Pomadas, óleos etc. + estoque inicial (botão *Estoque*) |
   | 6 | Financeiro | Contas fixas (aluguel, luz, fornecedores) |
   | 7 | Vendas → Nova venda | Um teste: confira comprovante, comissão e estoque |

5. Apague os dados de demonstração quando terminar de conhecer o sistema (veja no fim desta
   página).

---

## Instalação por linha de comando (SSH ou Terminal do cPanel)

Se você tem acesso ao terminal, dá para fazer tudo em um comando:

```bash
cd public_html
php artisan barbearia:install \
  --driver=mysql --host=localhost --port=3306 \
  --database=usuario_barbearia --username=usuario_sistema --password=SENHA \
  --nome="Barbearia do João" --url=https://barbeariadojoao.com.br \
  --admin-email=joao@barbeariadojoao.com.br --admin-nome="João" --admin-senha=TrocarEstaSenha \
  --demo
```

Outras opções: `--fresh` (recria as tabelas), `--force` (não faz perguntas).

Manual, sem o instalador:

```bash
cp .env.example .env
php artisan key:generate      # gera a APP_KEY
# edite o .env com os dados do banco
php artisan migrate --seed --force
php artisan barbearia:admin joao@barbeariadojoao.com.br --nome="João" --password=TrocarEstaSenha
php artisan config:clear
```

---

## Ferramenta de diagnóstico (use antes de pedir ajuda)

```bash
php artisan barbearia:diagnostico
```

Ele confere versão do PHP, extensões, `.env`, `APP_KEY`, permissões de escrita, conexão com o
banco, existência de cada tabela, migrations pendentes, usuário administrador e **abre todas as
telas do sistema**, mostrando `✔` ou `✘` em cada uma. Mande o resultado para o suporte e o
problema é resolvido muito mais rápido. (Use `--sem-rotas` para pular a parte das telas.)

---

## Permissões de arquivos (quando a hospedagem exige)

| Item | Permissão |
| --- | --- |
| Pastas do sistema | `755` |
| Arquivos | `644` |
| `.env` | `644` (durante a instalação; depois pode voltar para `600`) |
| `storage/` (e tudo dentro) | `775` |
| `bootstrap/cache/` | `775` |
| `database/database.sqlite` (se usar SQLite) | `664` |

No Gerenciador de Arquivos do cPanel: botão direito → **Change Permissions**. Marque "Recurse"
para aplicar às subpastas.

---

## HTTPS (cadeado verde)

1. No cPanel, abra **SSL/TLS Status** e rode *AutoSSL* para o seu domínio (gratuito na maioria das
   hospedagens).
2. Ajuste `APP_URL` para `https://seudominio.com.br` em **Configurações** (ou no `.env`).
3. Rode `php artisan config:clear`.

---

## Atualizar para uma versão nova

1. **Backup**: exporte o banco (phpMyAdmin → *Exportar*) e baixe o `.env`.
2. Envie os arquivos novos por cima dos antigos, **sem apagar** `.env`, `storage/` e
   `database/database.sqlite`.
3. Rode `php artisan migrate --force` e depois `php artisan optimize:clear`.
4. Confira com `php artisan barbearia:diagnostico`.

Suas configurações e seus dados ficam no banco e no `.env`, então nunca se perdem na troca de
arquivos.

---

## Backup e restauração

**Backup (faça toda semana)**

- *MySQL*: cPanel → **phpMyAdmin** → selecione o banco → **Exportar** → *Executar* → salva um
  `.sql`.
- *SQLite*: baixe o arquivo `database/database.sqlite`.
- Baixe também o `.env`.

**Restauração**

1. Crie o banco e o usuário (Passo 3) e importe o `.sql` no phpMyAdmin (**Importar**).
2. Recoloque o `.env` na raiz do sistema.
3. Rode `php artisan config:clear`.

---

## Zerar o sistema (recomeçar do zero)

- **Com SSH**: `php artisan migrate:fresh --seed --force` (adicione `DEMO_DATA=true` no `.env` se
  quiser os dados de exemplo).
- **Sem SSH**: apague `storage/app/installed.lock`, abra `/install`, marque **"Recriar as
  tabelas"** e instale de novo.

---

## Problemas comuns

| Sintoma | Causa | Solução |
| --- | --- | --- |
| Página em branco, sem mensagem | Erro escondido com `APP_DEBUG=false` | Abra `storage/logs/laravel.log` (últimas linhas) ou rode `barbearia:diagnostico` |
| Erro 500 em todas as páginas | Permissão de escrita | Passo "Permissões de arquivos"; confira também `storage/framework/views` |
| Erro 404 em tudo, menos a home | Domínio não aponta para `public/` | Faça o Passo 4 (opção A) ou confira se o `.htaccess` da raiz foi enviado; no cPanel, verifique se *AllowOverride* está liberado |
| "No application encryption key has been specified" | `.env` sem `APP_KEY` | `php artisan key:generate` ou rode o instalador de novo |
| "could not find driver" | Extensão do banco desligada | Ative `pdo_mysql`/`pdo_sqlite` no Passo 1 |
| "Base table or view not found: usuarios" | Migrations não rodaram | `php artisan migrate --force` (ou "Recriar as tabelas" no instalador) |
| "table usuarios already exists" | Banco com tabelas de uma instalação antiga | Marque "Recriar as tabelas" no instalador ou rode `migrate:fresh` |
| Erro 419 ao salvar um formulário | Sessão não está sendo gravada | Deixe `SESSION_DRIVER=database` (padrão) ou dê permissão `775` em `storage/framework/sessions` |
| Sai do login sozinho | Idem, ou `SESSION_LIFETIME` muito baixo | Ajuste `SESSION_LIFETIME=120` (minutos) |
| Esqueci a senha do administrador | — | `php artisan barbearia:admin seu@email.com --password=NovaSenha`; sem SSH, peça à hospedagem para rodar o comando |
| Quero reinstalar do zero | Instalador bloqueado | Apague `storage/app/installed.lock` e abra `/install` |
| E-mails não chegam | `MAIL_MAILER=log` (padrão) | Configure o SMTP da hospedagem no `.env` (`MAIL_MAILER=smtp`, host, porta, usuário, senha) |

---

## Segurança: o que fazer no primeiro dia

1. Trocar a senha do administrador.
2. Deixar `APP_DEBUG=false` e `APP_ENV=production` no `.env`.
3. Apagar o `.zip` da instalação de dentro de `public_html`.
4. Conferir se `http://seudominio.com.br/.env` **não** abre (deve dar erro 403/404 — o `.htaccess`
   da raiz cuida disso).
5. Criar um usuário para cada pessoa da equipe (não compartilhe o login do administrador).
6. Ativar o HTTPS (AutoSSL).

---

## Suporte

Ao pedir ajuda, envie:

1. a saída de `php artisan barbearia:diagnostico`;
2. as últimas 30 linhas de `storage/logs/laravel.log`;
3. a versão do PHP e o tipo de banco que você usa.
