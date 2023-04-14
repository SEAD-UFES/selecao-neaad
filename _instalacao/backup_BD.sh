#!/bin/sh
sistema='selecaoneaad';
banco='selecaoneaad';
usuario='root';
senha='Ne@ad9372BD';
data=`date +%d-%m-%Y`;

echo "############ Criando Backup do Sistema : $sistema em $data ##############"

echo "Criando diretorio temporario\n"
mkdir $data
chmod 777 $data
cd $data

echo "Criando dump do banco\n"
mysqldump -u$usuario -p$senha $banco > $sistema-$data.sql
tar -czf $sistema-$data.sql.tar.gz $sistema-$data.sql
rm $sistema-$data.sql

echo "Removendo e movendo o backup do BD"
rm /var/backups/$sistema-*.sql.tar.gz
mv $sistema-$data.sql.tar.gz /var/backups/


echo "Compactando Codigo"
tar -czf codigo-$sistema-$data.tar.gz /var/www/html/$sistema

echo "Removendo e movendo o backup do Codigo"
rm /var/backups/codigo-$sistema-*.tar.gz
mv codigo-$sistema-$data.tar.gz /var/backups/

echo "Eliminando estrutura temporaria\n"
cd ..
rm -rf $data
echo "############### Backup Concluido ########################\n"

