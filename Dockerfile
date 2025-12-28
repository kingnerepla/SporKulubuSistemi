FROM php:8.1-apache-bullseye

# 1. Gerekli araçlar
RUN apt-get update && apt-get install -y gnupg2 curl ca-certificates unixodbc-dev

# 2. Microsoft Anahtarını Ekle
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /etc/apt/trusted.gpg.d/microsoft.gpg

# 3. Repo Ekle (Debian 11)
RUN curl https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list

# 4. Sürücüyü Kur (Versiyon 18 - Apple Silicon Destekli)
RUN apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18

# 5. PHP Eklentilerini Kur
RUN pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# 6. Apache Ayarları
RUN a2enmod rewrite
WORKDIR /var/www/html
