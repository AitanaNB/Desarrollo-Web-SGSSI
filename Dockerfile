FROM php:7.2.2-apache

#Para instalar PDO: solo he remplazado la linea donde ponia 'RUN docker-php-ext-install mysqli', por la que esta aquí debajo que instala PDO

RUN docker-php-ext-install pdo pdo_mysql
