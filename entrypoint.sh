#!/bin/bash
set -e

PORT="${PORT:-8080}"

sed -i "s/80/${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

sed -i "s/index.php/login.php/g" /etc/apache2/mods-enabled/dir.conf 2>/dev/null || true

exec apache2-foreground