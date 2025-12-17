#!/bin/bash

# Script para criar repositório no GitHub e fazer push
# Uso: ./scripts/deploy-to-github.sh [nome-do-repositorio]

REPO_NAME="${1:-teste-backend}"
GITHUB_USER=$(git config user.name | tr ' ' '-' | tr '[:upper:]' '[:lower:]' 2>/dev/null || echo "seu-usuario")

echo "🚀 Criando repositório no GitHub: $REPO_NAME"
echo ""
echo "Opção 1: Criar manualmente no GitHub"
echo "1. Acesse: https://github.com/new"
echo "2. Nome do repositório: $REPO_NAME"
echo "3. Deixe PRIVADO (conforme instruções do desafio)"
echo "4. NÃO inicialize com README, .gitignore ou license"
echo "5. Clique em 'Create repository'"
echo ""
echo "Depois execute os comandos abaixo:"
echo ""
echo "git remote add origin https://github.com/$GITHUB_USER/$REPO_NAME.git"
echo "git push -u origin main"
echo ""
read -p "Já criou o repositório no GitHub? (s/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Ss]$ ]]; then
    echo "📦 Configurando remote..."
    git remote remove origin 2>/dev/null
    git remote add origin "https://github.com/$GITHUB_USER/$REPO_NAME.git"

    echo "📤 Fazendo push para o GitHub..."
    git push -u origin main

    if [ $? -eq 0 ]; then
        echo "✅ Código enviado com sucesso!"
        echo "🔗 Repositório: https://github.com/$GITHUB_USER/$REPO_NAME"
    else
        echo "❌ Erro ao fazer push. Verifique:"
        echo "   1. Se o repositório foi criado no GitHub"
        echo "   2. Se você tem permissão para fazer push"
        echo "   3. Se precisa fazer autenticação (git config credential.helper store)"
    fi
else
    echo "Execute este script novamente após criar o repositório no GitHub."
fi

