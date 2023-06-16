FROM mysql:latest

#ENV MYSQL_ROOT_PASSWORD=root 
# 初始化建立database
COPY create.sql /docker-entrypoint-initdb.d 
# 要匯入的sql
COPY house.sql /var/lib/
# 匯入指令
COPY import.sh /docker-entrypoint-initdb.d