FROM registry.cn-beijing.aliyuncs.com/flyhope/centos
MAINTAINER huanggenghuai
COPY php-7.1.8.tar.gz /usr/local/src
#COPY Centos-6.repo /etc/yum.repos.d/
COPY redis-3.1.1.tgz /usr/local/src
COPY composer.phar /usr/local/bin/composer
#RUN yum makecache
RUN groupadd www && useradd -g www www
RUN cd /usr/local/src && tar -xvf php-7.1.8.tar.gz && cd php-7.1.8  \
    && export PHP_OPENSSL_DIR=yes \
#    && yum install -y epel-release \
    && yum install -y m4 autoconf \
    && yum install -y pcre-devel gcc libxml2-devel  libjpeg-*  bzip2 bzip2-devel libpng-devel freetype-devel gd-devel libmcrypt-devel openssl-devel openssl curl curl-devel  \
    && ./configure  \
        --prefix=/usr/local/php71 \
        --with-config=/usr/local/php71/etc \
        --with-config-file-path=/usr/local/php71/etc \
        --enable-fpm \
        --with-fpm-user=www \
        --with-fpm-group=www \
        --with-libxml-dir \
        --with-gd \
        --with-jpeg-dir \
        --with-png-dir   \
        --with-freetype-dir   \
        --with-iconv-dir   \
        --with-zlib-dir   \
        --with-bz2   \
        --with-openssl   \
        --with-mcrypt   \
        --with-curl \
        --enable-soap   \
        --enable-gd-native-ttf   \
        --enable-mbstring   \
        --enable-sockets   \
        --enable-exif \
        --enable-mysqlnd \
         --with-mysqli=mysqlnd \
         --with-pdo-mysql=mysqlnd \
    && make && make install
    #软连接
RUN   ln -sf /usr/local/php71/bin/php /usr/local/bin/php && ln -sf /usr/local/php71/bin/pecl /usr/local/bin/pecl
    #更新pecl
RUN pecl channel-update pecl.php.net
    #安装phpredis扩展
RUN pecl install redis
   #安装mongodb扩展
RUN pecl mongodb
RUN yum install wget -y
#安装swoole
RUN cd /usr/local/src  \
    && wget https://github.com/redis/hiredis/archive/v0.13.3.tar.gz \
    && wget https://github.com/swoole/swoole-src/archive/v1.9.19.tar.gz \
    && tar -xzvf v0.13.3.tar.gz && tar -xzvf v1.9.19.tar.gz \
    && cd hiredis-0.13.3 && make -j && make install && ldconfig \
    && cd ../swoole-src-1.9.19 && /usr/local/php71/bin/phpize \
    && ./configure --with-php-config=/usr/local/php71/bin/php-config --enable-async-redis --enable-openssl && make \
    && make install

    #莫名其妙的问题
RUN echo '/usr/local/lib' >>/etc/ld.so.conf &&  ldconfig
   #php-fpm 添加到service
RUN cd /usr/local/src/php-7.1.8/sapi/fpm && cp init.d.php-fpm /etc/init.d/php-fpm \
    && cd /etc/init.d/ && chmod +x /etc/init.d/php-fpm && chkconfig --add php-fpm && chkconfig php-fpm on

CMD /etc/init.d/php-fpm start && tail -f /dev/null
EXPOSE 9000
#启动服务

