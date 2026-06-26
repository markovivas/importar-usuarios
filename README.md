# Importador de Usuários para WordPress

Este projeto contém um script PHP para importar usuários em lote para uma instalação do WordPress a partir de um arquivo `cadastro_usuario.csv`.

## Funcionalidades

*   **Importação em Lote**: Carrega múltiplos usuários de uma vez.
*   **Interface Visual**: Mostra o progresso da importação em tempo real, com barra de progresso e status individual.
*   **Mapeamento de Dados**: Mapeia automaticamente as colunas do CSV para os campos de usuário do WordPress.
*   **Performance**: Otimizado para processar um grande número de usuários, desabilitando gatilhos e e-mails desnecessários durante a importação.

## Pré-requisitos

1.  **Acesso ao servidor**: Você precisa ter acesso aos arquivos da sua instalação WordPress.
2.  **Arquivo CSV**: Um arquivo chamado `cadastro_usuario.csv` deve estar presente na mesma pasta do script.

### Formato do `cadastro_usuario.csv`

O arquivo CSV deve conter um cabeçalho e as seguintes colunas, separadas por **ponto e vírgula (;)**:

`MATRICULA;NOME;NASCIMENTO;SECRETARIA`

*   `MATRICULA`: Usado como **nome de usuário** (login) e para criar o **e-mail** no formato `matricula@trescoracoes.mg.gov.br`.
*   `NOME`: Nome completo do usuário. Será dividido em nome e sobrenome.
*   `NASCIMENTO`: Data de nascimento no formato `dd/mm/aaaa`. Usado para gerar a **senha** (apenas números) e integrar com o plugin de aniversariantes.
*   `SECRETARIA`: Secretaria ou departamento do usuário. Salvo como metadado do perfil.

## Como Usar

1.  **Copie os arquivos**: Coloque o script `importar-usuarios.php` e o seu arquivo `cadastro_usuario.csv` na pasta raiz da sua instalação do WordPress (a mesma pasta que contém o `wp-config.php`).
2.  **Execute o script**: Abra o seu navegador e acesse a URL do script. Por exemplo: `https://seusite.com.br/importar-usuarios.php`.
3.  **Acompanhe**: A importação começará automaticamente e você poderá acompanhar o progresso na tela.

## Como criar o arquivo CSV

Para garantir que o arquivo funcione corretamente, siga estes passos no Excel ou LibreOffice:

1.  Crie uma planilha com 4 colunas na ordem: **Matrícula**, **Nome**, **Nascimento**, **Secretaria**.
2.  Preencha os dados (ex: Nascimento como `24/09/1989`).
3.  Vá em **Arquivo > Salvar Como**.
4.  Escolha o formato **CSV (separado por ponto e vírgula)** ou apenas **CSV**.
5.  Nomeie o arquivo como `cadastro_usuario.csv`.

**Exemplo de conteúdo do arquivo:**
```csv
MATRICULA;NOME;NASCIMENTO;SECRETARIA
10445;JOAO DA SILVA;26/01/1975;Secretaria Municipal de Educação
21216;MARIA SOUZA;28/01/1981;Secretaria Municipal de Saúde
```

> **Nota:** Se o usuário já existir no WordPress (mesma matrícula), o script **atualiza** os dados dele em vez de criar um duplicado.