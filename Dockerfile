# Utilise une image officielle PHP avec Apache
FROM php:8.2-apache



# Installe les extensions PHP nécessaires (sans MySQL)
# Si besoin d'autres extensions, ajoutez-les ici
# RUN docker-php-ext-install <autre_extension>

# Active tous les modules Apache requis (.htaccess)
RUN a2enmod rewrite deflate expires headers

# Copie le code de l'application dans le conteneur
COPY . /var/www/html/

# Donne les bons droits sur les fichiers
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copie un fichier de configuration Apache personnalisé si besoin
# COPY ./config/apache.conf /etc/apache2/sites-available/000-default.conf

# Expose le port 80
EXPOSE 80

# Commande de démarrage
CMD ["apache2-foreground"]
