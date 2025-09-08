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
