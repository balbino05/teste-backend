# 🚀 Como fazer push para o GitHub

## Passo 1: Criar o repositório no GitHub

1. Acesse: https://github.com/new
2. **Nome do repositório**: `teste-backend` (ou outro nome de sua escolha)
3. **Descrição**: "Teste Back-end - Plataforma de pagamentos simplificada"
4. Deixe como **PRIVADO** (conforme instruções do desafio)
5. **NÃO** marque nenhuma opção (README, .gitignore, license)
6. Clique em **"Create repository"**

## Passo 2: Configurar o remote e fazer push

Após criar o repositório, execute os comandos abaixo substituindo `SEU-USUARIO` pelo seu username do GitHub:

```bash
# Adicionar o remote (substitua SEU-USUARIO pelo seu username)
git remote add origin https://github.com/SEU-USUARIO/teste-backend.git

# Fazer push do código
git push -u origin main
```

## Alternativa: Usar o script automatizado

Execute o script que criamos:

```bash
./scripts/deploy-to-github.sh teste-backend
```

O script irá guiá-lo através do processo.

## Autenticação

Se for solicitado usuário e senha:
- **Usuário**: Seu username do GitHub
- **Senha**: Use um **Personal Access Token** (não sua senha)

Para criar um token:
1. Acesse: https://github.com/settings/tokens
2. Clique em "Generate new token (classic)"
3. Dê um nome (ex: "teste-backend")
4. Selecione o escopo `repo`
5. Clique em "Generate token"
6. Copie o token e use como senha

## Verificar se funcionou

Após o push, acesse seu repositório:
```
https://github.com/SEU-USUARIO/teste-backend
```

Você deve ver todos os arquivos do projeto lá! 🎉

