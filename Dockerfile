FROM wodby/drupal-php:7.2

ADD . /var/www/html

#USER root

#RUN  chmod -R 770 /var/www/html; chown -R wodby:www-data /var/www/html
