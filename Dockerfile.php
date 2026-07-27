FROM php:8.2-apache

# Habilitar mod_rewrite por si usas URLs limpias
RUN a2enmod rewrite

# Copiar solo el frontend PHP
COPY public/ /var/www/html/

# Railway asigna el puerto dinámicamente en $PORT
# Este script ajusta Apache para escuchar en ese puerto
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]