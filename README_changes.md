## 2025-09-20

- Adicionado suporte a tipos de mídia (Livro, Filme, Série):
	- Nova coluna `type` na tabela `books` (migração `2025_09_20_120000_add_type_to_books_table.php`).
	- Formulário em `pages/home.php` agora tem um select "Tipo".
	- `pages/processa_livro.php` salva o tipo escolhido.
	- Listagens em `home.php` e `livros.php` exibem o tipo.

Como aplicar:
- Execute as migrações para criar a coluna `type` antes de usar o novo formulário.

Mudanças realizadas:

- `pages/livros.php`: Substituída a tabela por um grid de "cards" para cada livro; adicionado formulário simples de favoritar (POST para `pages/processa_favorito.php`) e exibição de mensagens flash via session.
- `pages/processa_favorito.php`: Novo arquivo. Recebe POST com `book_id`, salva IDs de livros favoritos em `storage/favorites.json` e redireciona de volta para `pages/livros.php` com mensagem.
- `CSS/style.css`: Novas regras para `.books-grid`, `.book-card` e botão de favorito para harmonizar com o tema do site.

Como testar:
1. Abra `pages/livros.php` no navegador (via servidor local como XAMPP).
2. Clique em "Favoritar" em qualquer livro. Você deve ser redirecionado de volta com uma mensagem de sucesso.
3. Verifique `storage/favorites.json` para ver o array de IDs favoritados.

Notas e limitações:
- Não há autenticação associada; todos os visitantes podem favoritar livros e o armazenamento é global no arquivo JSON.
- Se desejar salvar favoritos por usuário, podemos ajustar para usar a tabela `favorites` no banco de dados ou salvar por ID de usuário na sessão.

Próximos passos sugeridos:
- Mostrar estado "já favoritado" no botão (ex.: ícone preenchido) lendo `storage/favorites.json`.
- Persistência por usuário no banco de dados.
- Adicionar confirmação visual imediata com JavaScript (AJAX) para evitar reload.

Atualização (2025-09-20): refatoração para Orientação a Objetos

- Criado um conjunto de classes para organizar a lógica, mantendo as páginas e URLs como estão:
	- `app/Support`: `Env`, `Database`, `Http`, `Storage`, `Str`
	- `app/Repositories`: `UserRepository`, `BookRepository`
	- `app/Services`: `AuthService`, `FavoriteService`
- Páginas atualizadas para usar as classes (sem alterar a aparência/comportamento):
	- `pages/processa_login.php`
	- `pages/processa_favorito.php`
	- `pages/processa_livro.php`
	- `pages/livros.php`

Observações
- Autoload via Composer: as páginas agora chamam `require_once __DIR__ . '/../vendor/autoload.php';`. Garanta que o `composer install` foi executado e que o PHP é 8.2+.
- Banco: respeita `.env` (mysql/sqlite). Padrão: SQLite em `database/database.sqlite`.
- Favoritos: persistidos em `storage/favorites.json` através do `FavoriteService`.

Como testar rapidamente
- Login: http://localhost/biblioteca/pages/index.php
- Livros: http://localhost/biblioteca/pages/livros.php
