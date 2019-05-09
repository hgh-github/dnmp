FROM registry.cn-beijing.aliyuncs.com/flyhope/centos
MAINTAINER huanggenghuai
#安装依赖
RUN yum install -y gcc libxml2-devel  libjpeg-*  bzip2 bzip2-devel libpng-devel freetype-devel gd-devel libmcrypt-devel openssl-devel openssl curl curl-devel
COPY php-5.4.36.tar.bz2 /usr/local/src
COPY redis-3.1.1.tgz /usr/local/src
COPY mongo-1.6.12.tgz /usr/local/src
RUN groupadd www && useradd -g www www
RUN cd /usr/local/src && tar -xvf php-5.4.36.tar.bz2 && cd php-5.4.36  \
    && export PHP_OPENSSL_DIR=yes \
    && yum install -y epel-release \
    && yum install -y m4 autoconf \
#    && yum install -y gcc libxml2-devel  libjpeg-*  bzip2 bzip2-devel libpng-devel freetype-devel gd-devel libmcrypt-devel openssl-devel openssl curl curl-devel  \
    && ./configure  \
        --prefix=/usr/local/php54 \
        --with-config=/usr/local/php54/etc \
        --with-config-file-path=/usr/local/php54/etc \
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
        --enable-zip \
        --with-mysqli=mysqlnd \
        --with-pdo-mysql=mysqlnd \
    && make && make install
    #编译安装phpredis扩展
RUN cd /usr/local/src && tar -zxvf redis-3.1.1.tgz && cd redis-3.1.1 \
   && /usr/local/php54/bin/phpize \
   && ./configure --with-php-config=/usr/local/php54/bin/php-config \
   && make -j && make install
       #编译安装mongodb扩展
RUN cd /usr/local/src && tar -zxvf mongo-1.6.12.tgz && cd mongo-1.6.12 \
    && /usr/local/php54/bin/phpize \
    && ./configure --with-php-config=/usr/local/php54/bin/php-config \
    && make -j && make install
   #php-fpm 添加到service
RUN cd /usr/local/src/php-5.4.36/sapi/fpm && cp init.d.php-fpm /etc/init.d/php-fpm \
    && cd /etc/init.d/ && chmod +x /etc/init.d/php-fpm && chkconfig --add php-fpm && chkconfig php-fpm on
CMD /etc/init.d/php-fpm start \
&& tail -f /dev/null
EXPOSE 9000


