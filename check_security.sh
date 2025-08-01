#!/bin/bash

echo "🔒 Verificando permissões e arquivos sensíveis..."

# Verifica se .env está acessível publicamente
if [ -f public/.env ]; then
  echo "⚠️  ALERTA: .env está acessível em /public"
else
  echo "✅ .env não está em public/"
fi

# Verifica permissões do .env
PERM=$(stat -c "%a" .env)
if [[ "$PERM" -gt 644 ]]; then
  echo "⚠️  ALERTA: Permissões inseguras no .env ($PERM)"
else
  echo "✅ Permissões seguras no .env ($PERM)"
fi

# Verifica debug mode
if grep -q "APP_DEBUG=true" .env; then
  echo "⚠️  ALERTA: APP_DEBUG está ativado!"
else
  echo "✅ APP_DEBUG desativado"
fi

# Verifica HTTPS forçado no Laravel
if grep -q "FORCE_HTTPS=true" .env; then
  echo "✅ HTTPS forçado via .env"
else
  echo "⚠️  HTTPS não está forçado (FORCE_HTTPS)"
fi

# Verifica permissões de storage e bootstrap
echo "📂 Verificando permissões de storage/ e bootstrap/"
chmod -R 755 storage bootstrap/cache
echo "✅ Permissões ajustadas"
