# Sistema de Controle de Despesas de Viagem (PHP + MySQL)

## Estrutura de pastas

```text
/despesas
├── app/
│   ├── helpers.php
│   ├── simple_pdf.php
│   └── views/
│       ├── footer.php
│       └── header.php
├── config/
│   ├── config.php
│   └── db.php
├── database/
│   └── schema.sql
├── public/
│   ├── .htaccess
│   ├── advances.php
│   ├── categories.php
│   ├── dashboard.php
│   ├── expenses.php
│   ├── index.php
│   ├── logout.php
│   ├── report_export.php
│   └── reports.php
├── uploads/
│   ├── .htaccess
│   └── index.html
├── lib/
└── README.md
```

## Deploy na Hostinger (passo a passo)

1. **Criar banco MySQL/MariaDB**
   - No hPanel, abra **Bancos de Dados MySQL**.
   - Crie banco, usuário e senha.

2. **Importar schema**
   - Abra **phpMyAdmin**.
   - Selecione o banco criado.
   - Importe `database/schema.sql`.

3. **Configurar o projeto**
   - Edite `config/config.php`:
     - host, porta, nome do banco, usuário e senha.
     - timezone (padrão: `America/Sao_Paulo`).
     - `base_url` se necessário (ex.: `https://seu-dominio.com`).

4. **Enviar arquivos**
   - Via File Manager ou FTP, envie toda a pasta do projeto.
   - Em hospedagem compartilhada, você pode apontar o domínio para a pasta `public/`.
   - Se não puder apontar para `public/`, copie o conteúdo de `public/` para `public_html/` e ajuste os `require` conforme estrutura escolhida.

5. **Permissões de upload**
   - Garanta escrita na pasta `uploads/`.
   - Permissão sugerida: `755` (ou `775` se necessário no servidor).
   - Não use `777` em produção.

6. **Acesso inicial**
   - Login padrão:
     - email: `admin@exemplo.com`
     - senha: `Admin@123`
   - Altere a senha no banco após primeiro acesso (ou implemente tela de troca de senha).

## Segurança implementada

- PDO com prepared statements em todas as queries.
- `password_hash()`/`password_verify()`.
- Proteção CSRF em todos os formulários POST.
- Rotas protegidas por sessão.
- Upload seguro com:
  - validação de extensão e MIME,
  - limite de tamanho,
  - nome único aleatório,
  - bloqueio de execução via `.htaccess`.
- Escape de saída com `htmlspecialchars`.

## Checklist rápido de testes

- [ ] Login com credenciais válidas.
- [ ] Bloqueio com credenciais inválidas.
- [ ] Cadastro de categoria.
- [ ] Lançamento de despesa com upload obrigatório.
- [ ] Edição de despesa (troca opcional do comprovante).
- [ ] Exclusão de despesa e remoção do arquivo.
- [ ] Registro de múltiplos adiantamentos.
- [ ] Conferir saldo = adiantamentos - despesas.
- [ ] Filtros de despesas por período/categoria/observação.
- [ ] Exportação CSV.
- [ ] Geração de PDF.
