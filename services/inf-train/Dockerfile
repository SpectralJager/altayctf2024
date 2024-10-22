FROM php:7.4-apache

RUN apt-get update -y && apt-get upgrade -y
RUN set -eux ; \
    apt-get -y install \
        build-essential \
        default-mysql-client \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libxml2-dev \
        libzip-dev \
        libbz2-dev \
        zip \
        jpegoptim optipng pngquant gifsicle ; \
    docker-php-ext-configure gd --with-freetype --with-jpeg=/usr ; \
    docker-php-ext-install -j "$(nproc)" gd bcmath pdo_mysql mysqli zip ; \
    \
    savedAptMark="$(apt-mark showmanual)"; \
    apt-mark auto '.*' > /dev/null ; \
    apt-mark manual $savedAptMark ; \
    ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
        | awk '/=>/ { print $3 }' \
        | sort -u \
        | xargs -r dpkg-query -S \
        | cut -d: -f1 \
        | sort -u \
        | xargs -rt apt-mark manual ; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false ; \
    apt-get -y autoclean && apt-get -y clean && apt-get -y autoremove && \
    rm -rf /var/lib/apt/lists/*

RUN ln -sf /usr/share/zoneinfo/Asia/Barnaul /etc/localtime && \
    a2enmod rewrite && \
    curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    sed -i -e 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/www!g'  /etc/apache2/sites-available/000-default.conf && \
    sed -i -e '13a\<Directory /var/www/html/www/* >'  /etc/apache2/sites-available/000-default.conf && \ 
    sed -i -e '14a\Options -MultiViews +Indexes -FollowSymLinks +SymLinksIfOwnerMatch +IncludesNoExec +Includes'  /etc/apache2/sites-available/000-default.conf && \
    sed -i -e '15a\AllowOverride All'  /etc/apache2/sites-available/000-default.conf && \
    sed -i -e '16a\Require all granted'  /etc/apache2/sites-available/000-default.conf && \
    sed -i -e '17a\Order allow,deny'  /etc/apache2/sites-available/000-default.conf && \
    sed -i -e '18a\allow from all'  /etc/apache2/sites-available/000-default.conf && \
    sed -i -e '19a\</Directory>'  /etc/apache2/sites-available/000-default.conf
    
COPY ./php.ini "$PHP_INI_DIR/php.ini"
WORKDIR /var/www/html

CMD composer install -o && apache2-foreground