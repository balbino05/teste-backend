#!/usr/bin/env python3
"""
Script para criar repositório no GitHub via API
Requer: pip install PyGithub
Uso: python3 scripts/create-github-repo.py [nome-do-repo]
"""

import sys
import os
from getpass import getpass

try:
    from github import Github
except ImportError:
    print("❌ PyGithub não instalado. Instale com: pip install PyGithub")
    print("   Ou use o método manual descrito em GITHUB_SETUP.md")
    sys.exit(1)

def main():
    repo_name = sys.argv[1] if len(sys.argv) > 1 else "teste-backend"

    # Tentar obter token de variável de ambiente ou pedir ao usuário
    token = os.getenv("GITHUB_TOKEN")
    if not token:
        print("🔑 Para criar o repositório, você precisa de um Personal Access Token")
        print("   Crie um em: https://github.com/settings/tokens")
        print("   Escopo necessário: 'repo'")
        token = getpass("Token do GitHub: ")

    if not token:
        print("❌ Token não fornecido. Use o método manual em GITHUB_SETUP.md")
        sys.exit(1)

    try:
        g = Github(token)
        user = g.get_user()

        print(f"👤 Logado como: {user.login}")
        print(f"📦 Criando repositório: {repo_name}")

        # Criar repositório privado
        repo = user.create_repo(
            repo_name,
            description="Teste Back-end - Plataforma de pagamentos simplificada",
            private=True,
            auto_init=False
        )

        print(f"✅ Repositório criado com sucesso!")
        print(f"🔗 URL: {repo.html_url}")
        print(f"\n📤 Agora execute:")
        print(f"   git remote add origin {repo.clone_url}")
        print(f"   git push -u origin main")

    except Exception as e:
        print(f"❌ Erro: {e}")
        print("\n💡 Use o método manual descrito em GITHUB_SETUP.md")
        sys.exit(1)

if __name__ == "__main__":
    main()

