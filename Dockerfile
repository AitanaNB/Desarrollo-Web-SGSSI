FROM php:7.2.2-apache

#Para instalar PDO: solo he remplazado la linea donde ponia 'RUN docker-php-ext-install mysqli', por la que esta aquí debajo que instala PDO

RUN docker-php-ext-install pdo pdo_mysql

#asegurar config segura de Apache
COPY ./.docker/apache/adicional.conf /tmp/adicional.conf
#eliminar líneas contradictorias con "sed", para no borrar todo el security
#RUN cat /tmp/adicional.conf > /etc/apache2/conf-enabled/security.conf
RUN sed -i '/ServerTokens/d' /etc/apache2/conf-enabled/security.conf && \
    sed -i '/ServerSignature/d' /etc/apache2/conf-enabled/security.conf && \
    cat /tmp/adicional.conf >> /etc/apache2/conf-enabled/security.conf